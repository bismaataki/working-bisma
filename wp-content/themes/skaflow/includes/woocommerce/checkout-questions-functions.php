<?php
/**
 * Checkout questions: server side.
 *
 * Loads the React that renders our questions into WooCommerce's checkout
 * SlotFills, and writes the answers to order meta.
 *
 * The answers arrive as Store API extension data rather than as POST fields,
 * because the block checkout submits one JSON request. WooCommerce hands that
 * request to `woocommerce_store_api_checkout_update_order_from_request` with the
 * order already built, which is where extension data becomes order meta.
 *
 * Why not `woocommerce_register_additional_checkout_field()`: it renders every
 * order-location field in one section at the foot of the checkout, supports no
 * textarea, and cannot place one question beside the pickup choice and another
 * beside the delivery choice. See
 * `context/kb/plugin-wc-checkout-questions.md`.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Store API extension namespace. Must match NAMESPACE in src/checkout.js.
 */
const SKAFLOW_CHECKOUT_QUESTIONS_NAMESPACE = 'skaflow';

/*
 * SKAFLOW_CHECKOUT_QUESTION_META and SKAFLOW_CHECKOUT_QUESTION_VALUES are
 * defined in order-questions-functions.php, which init.php loads first. They are
 * the contract every surface reads, and they outlive this file — see the header
 * there.
 *
 * The grouping in the key map is not decoration to this file in particular:
 * extension data merges rather than replaces, so a shopper who answers the
 * delivery questions and then switches to pickup sends both groups. Only the
 * group matching the method the order actually used is stored — see
 * skaflow_save_checkout_questions().
 */

add_action( 'wp_enqueue_scripts', 'skaflow_enqueue_checkout_questions' );
add_action( 'init', 'skaflow_register_checkout_questions_schema' );
add_action( 'woocommerce_store_api_checkout_update_order_from_request', 'skaflow_save_checkout_questions', 10, 2 );

/**
 * Declares our namespace on the checkout endpoint's `extensions` property.
 *
 * Not optional, and its absence fails silently: the checkout schema builds
 * `extensions` from the registered callbacks only, so an unregistered namespace
 * is dropped from the request before any hook sees it. The order still places,
 * the answer simply never arrives.
 *
 * Runs on `init`, not on `woocommerce_blocks_loaded` as WooCommerce's own
 * documentation shows. That action fires during `plugins_loaded`, before the
 * theme is loaded at all, so a theme registering there is always too late and
 * silently registers nothing. `init` is early enough — `rest_api_init` and the
 * schema build both follow it.
 *
 * @return void
 */
function skaflow_register_checkout_questions_schema() {
	if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		return;
	}

	woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema::IDENTIFIER,
			'namespace'       => SKAFLOW_CHECKOUT_QUESTIONS_NAMESPACE,
			'schema_callback' => 'skaflow_get_checkout_questions_schema',
			'schema_type'     => ARRAY_A,
		)
	);
}

/**
 * Describes the answers we accept on the checkout endpoint.
 *
 * Deliberately loose: no `enum`, because a value outside it makes the whole
 * checkout request fail validation, and a shopper blocked from ordering is a
 * worse outcome than an answer we discard. Narrowing happens in
 * skaflow_clean_checkout_answer() instead.
 *
 * @return array
 */
function skaflow_get_checkout_questions_schema() {
	$schema = array();

	foreach ( SKAFLOW_CHECKOUT_QUESTION_META as $group ) {
		foreach ( array_keys( $group ) as $key ) {
			$schema[ $key ] = array(
				/* translators: %s: answer key, e.g. carrier_name. */
				'description' => sprintf( __( 'Checkout question answer: %s.', 'skaflow' ), $key ),
				'type'        => array( 'string', 'null' ),
				'context'     => array( 'view', 'edit' ),
			);
		}
	}

	return $schema;
}

/**
 * Enqueues the checkout questions script on the checkout.
 *
 * WooCommerce's handles are appended to the generated dependencies by hand. The
 * theme's build cannot see WooCommerce's packages, so nothing in
 * checkout.asset.php knows about them, but the script reads `wc.blocksCheckout`,
 * `wc.blocksComponents`, and `wc.wcBlocksData` at module scope and would throw
 * without them.
 *
 * @return void
 */
function skaflow_enqueue_checkout_questions() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	$asset_path = skaflow_get_build_dir( 'checkout.asset.php' );

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset  = require $asset_path;
	$handle = 'skaflow-checkout-questions';

	wp_enqueue_script(
		$handle,
		skaflow_get_build_url( 'checkout.js' ),
		array_merge(
			$asset['dependencies'],
			array( 'wc-blocks-checkout', 'wc-blocks-components', 'wc-blocks-data-store' )
		),
		$asset['version'],
		true
	);

	if ( function_exists( 'skaflow_get_session_checkout_answers' ) ) {
		wp_localize_script(
			$handle,
			'skaflowCheckoutQuestions',
			array(
				'answers' => skaflow_get_session_checkout_answers(),
			)
		);
	}

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( $handle, 'skaflow' );
	}
}

/**
 * Writes checkout question answers to order meta.
 *
 * Does not save the order — WooCommerce saves it after this hook when the
 * request is a real checkout rather than a draft.
 *
 * @param \WC_Order        $order   Order being created.
 * @param \WP_REST_Request $request The checkout request.
 *
 * @return void
 */
function skaflow_save_checkout_questions( $order, $request ) {
	$extensions = $request['extensions'] ?? array();
	$answers    = $extensions[ SKAFLOW_CHECKOUT_QUESTIONS_NAMESPACE ] ?? array();

	if ( ! is_array( $answers ) ) {
		return;
	}

	$group = skaflow_order_is_collection( $order ) ? 'collection' : 'delivery';

	foreach ( SKAFLOW_CHECKOUT_QUESTION_META[ $group ] as $key => $meta_key ) {
		if ( ! isset( $answers[ $key ] ) || ! skaflow_answer_was_asked( $key, $answers ) ) {
			continue;
		}

		$value = skaflow_clean_checkout_answer( $key, $answers[ $key ] );

		if ( '' === $value ) {
			continue;
		}

		$order->update_meta_data( $meta_key, $value );
	}
}

/**
 * Was this question on screen when the order was placed?
 *
 * Answers are kept in one client-side object and sent whole, so a question the
 * shopper backed out of still arrives: typing a carrier name and then switching
 * the collector back to Myself leaves the name in the request. Storing it would
 * put a carrier on an order somebody is collecting in person.
 *
 * The group filter in skaflow_save_checkout_questions() is the same rule one
 * level up, for the delivery and collection halves. Both live server side so
 * that what an order records depends on the order rather than on the client
 * having cleaned up after itself.
 *
 * @param string $key     Answer key.
 * @param array  $answers All submitted answers.
 *
 * @return bool
 */
function skaflow_answer_was_asked( $key, $answers ) {
	if ( ! str_starts_with( $key, 'carrier_' ) ) {
		return true;
	}

	return 'carrier' === ( $answers['collected_by'] ?? '' );
}

/**
 * Is this order being collected rather than delivered?
 *
 * Asks WooCommerce which method ids count as local pickup rather than comparing
 * against 'pickup_location' directly, so the answer stays right if a second
 * pickup method is ever enabled.
 *
 * @param \WC_Order $order Order to inspect.
 *
 * @return bool
 */
function skaflow_order_is_collection( $order ) {
	if ( ! class_exists( '\Automattic\WooCommerce\StoreApi\Utilities\LocalPickupUtils' ) ) {
		return false;
	}

	$pickup_methods = \Automattic\WooCommerce\StoreApi\Utilities\LocalPickupUtils::get_local_pickup_method_ids();

	foreach ( $order->get_items( 'shipping' ) as $shipping_item ) {
		if ( in_array( $shipping_item->get_method_id(), $pickup_methods, true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Reduces a submitted answer to something safe to store.
 *
 * Answers with a fixed option list must match it; anything else is treated as
 * free text. Returns an empty string for a value we will not store.
 *
 * @param string $key   Answer key.
 * @param mixed  $value Submitted value.
 *
 * @return string
 */
function skaflow_clean_checkout_answer( $key, $value ) {
	if ( ! is_scalar( $value ) ) {
		return '';
	}

	$value    = trim( (string) $value );
	$accepted = SKAFLOW_CHECKOUT_QUESTION_VALUES[ $key ] ?? null;

	if ( is_array( $accepted ) ) {
		return in_array( $value, $accepted, true ) ? $value : '';
	}

	return sanitize_textarea_field( $value );
}
