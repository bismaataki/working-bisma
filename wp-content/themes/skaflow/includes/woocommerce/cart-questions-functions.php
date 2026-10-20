<?php
/**
 * Cart tiles → two popups: server side.
 *
 * Loads the React that mounts the Shipping / Pickup tiles on the block cart
 * and localises the warehouse list those popups need. The tiles write a
 * delivery address or a pickup rate into the Store API cart, and the eight
 * answers into the cart session under the `skaflow` namespace so checkout
 * can show them and place-order can persist them. A guest shipping Confirm
 * also copies the address onto billing so checkout's "Use same address for
 * billing" starts checked.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WooCommerce session key for answers collected in a cart popup.
 */
const SKAFLOW_CART_ANSWERS_SESSION_KEY = 'skaflow_checkout_answers';

add_action( 'wp_enqueue_scripts', 'skaflow_enqueue_cart_questions' );
add_action( 'init', 'skaflow_register_cart_questions_schema' );

/**
 * Enqueues the cart-tile script on the block cart only.
 *
 * WooCommerce's handles are appended to the generated dependencies by hand.
 * The theme's build cannot see WooCommerce's packages, so nothing in
 * cart.asset.php knows about them, but the script reads `wc.blocksComponents`
 * and `wc.wcBlocksData` at module scope and would throw without them.
 *
 * Does not depend on `wp-editor`. That handle registers `core/editor`, which
 * makes the Cart block serve WooCommerce's demo cart instead of the shopper's
 * — see blocks-cart-guard-functions.php.
 *
 * @return void
 */
function skaflow_enqueue_cart_questions() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() || is_checkout() ) {
		return;
	}

	$asset_path = skaflow_get_build_dir( 'cart.asset.php' );

	if ( ! file_exists( $asset_path ) ) {
		return;
	}

	$asset  = require $asset_path;
	$handle = 'skaflow-cart-questions';

	wp_enqueue_script(
		$handle,
		skaflow_get_build_url( 'cart.js' ),
		array_merge(
			$asset['dependencies'],
			array( 'wc-blocks-components', 'wc-blocks-data-store' )
		),
		$asset['version'],
		true
	);

	wp_localize_script(
		$handle,
		'skaflowCartQuestions',
		array(
			'pickupLocations'     => skaflow_get_cart_pickup_locations(),
			'addressFinderActive' => defined( 'ADDRESSFINDER_WOOCOMMERCE_VERSION' ),
			'answers'             => skaflow_get_session_checkout_answers(),
			'estimate'            => skaflow_get_cart_estimate_config(),
			'countries'           => skaflow_get_cart_shipping_countries(),
			'states'              => skaflow_get_cart_shipping_states(),
			'isLoggedIn'          => is_user_logged_in(),
		)
	);

	if ( function_exists( 'wp_set_script_translations' ) ) {
		wp_set_script_translations( $handle, 'skaflow' );
	}
}

/**
 * Local pickup locations the warehouse dropdown can show before rates arrive.
 *
 * Rate ids match WooCommerce's `pickup_location:{index}` scheme so Confirm
 * can hand the same id to `selectShippingRate`. Disabled rows are omitted.
 *
 * @return array<int, array<string, mixed>>
 */
function skaflow_get_cart_pickup_locations() {
	$raw = get_option( 'pickup_location_pickup_locations', array() );

	if ( ! is_array( $raw ) ) {
		return array();
	}

	$locations = array();

	foreach ( $raw as $index => $location ) {
		if ( empty( $location['enabled'] ) ) {
			continue;
		}

		$address = is_array( $location['address'] ?? null ) ? $location['address'] : array();

		$locations[] = array(
			'rate_id' => 'pickup_location:' . (int) $index,
			'name'    => (string) ( $location['name'] ?? '' ),
			'address' => $address,
			'details' => (string) ( $location['details'] ?? '' ),
		);
	}

	return $locations;
}

/**
 * Shipping countries the address selects can offer.
 *
 * Codes match what the Store API accepts. A typed "Australia" or "Queens"
 * is rejected as an invalid shipping_address.
 *
 * @return array<string, string> Country code => name.
 */
function skaflow_get_cart_shipping_countries() {
	if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
		return array();
	}

	return WC()->countries->get_shipping_countries();
}

/**
 * States keyed by country, same source WooCommerce's checkout uses.
 *
 * @return array<string, array<string, string>> Country code => (state code => name).
 */
function skaflow_get_cart_shipping_states() {
	if ( ! function_exists( 'WC' ) || ! WC()->countries ) {
		return array();
	}

	return WC()->countries->get_states();
}

/**
 * Config for the delivery popup's rate / quote-only panel.
 *
 * Reuses the PDP card's currency and rate-card copy so the two surfaces do
 * not author the same price twice. Unpriced wording is not here: the plugin
 * sends `reason` and `message` on the lookup, and we print those as-is.
 *
 * @return array<string, mixed>
 */
function skaflow_get_cart_estimate_config() {
	if ( ! class_exists( '\Skaflow\PostcodePricing\Ajax' ) ) {
		return array(
			'enabled' => false,
		);
	}

	$strings  = function_exists( 'skaflow_get_delivery_estimate_strings' )
		? skaflow_get_delivery_estimate_strings()
		: array();
	$currency = function_exists( 'skaflow_get_delivery_estimate_currency_atts' )
		? skaflow_get_delivery_estimate_currency_atts()
		: array();

	return array(
		'enabled'       => true,
		'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
		'action'        => 'cf_postcode_estimate',
		'loading'       => $strings['loading'] ?? '',
		'failure'       => $strings['failure'] ?? '',
		'pricedTitle'   => $strings['priced_title'] ?? '',
		'pricedTransit' => $strings['priced_transit'] ?? '',
		'pricedNote'    => $strings['priced_note'] ?? '',
		'currency'      => array(
			'symbol'      => html_entity_decode(
				(string) ( $currency['data-price-symbol'] ?? '' ),
				ENT_QUOTES,
				'UTF-8'
			),
			'decimals'    => $currency['data-price-decimals'] ?? '2',
			'decimalSep'  => $currency['data-price-decimal-sep'] ?? '.',
			'thousandSep' => $currency['data-price-thousand-sep'] ?? ',',
			'format'      => $currency['data-price-format'] ?? '%1$s%2$s',
		),
	);
}

/**
 * Declares our namespace on the cart endpoint and the cart/extensions door.
 *
 * Checkout already registers `skaflow` on the checkout schema so place-order
 * can accept the answers. That does not make them persist on the cart: an
 * unregistered cart namespace is dropped, and `setExtensionData` is a checkout
 * dispatch. The session plus `POST /wc/store/v1/cart/extensions` is the cart
 * door — see `context/kb/plugin-wc-checkout-questions.md`.
 *
 * Same `init` timing as the checkout schema: `woocommerce_blocks_loaded` fires
 * before the theme is loaded.
 *
 * @return void
 */
function skaflow_register_cart_questions_schema() {
	if ( ! function_exists( 'woocommerce_store_api_register_endpoint_data' ) ) {
		return;
	}

	woocommerce_store_api_register_endpoint_data(
		array(
			'endpoint'        => \Automattic\WooCommerce\StoreApi\Schemas\V1\CartSchema::IDENTIFIER,
			'namespace'       => SKAFLOW_CHECKOUT_QUESTIONS_NAMESPACE,
			'schema_callback' => 'skaflow_get_checkout_questions_schema',
			'data_callback'   => 'skaflow_get_session_checkout_answers',
			'schema_type'     => ARRAY_A,
		)
	);

	if ( ! function_exists( 'woocommerce_store_api_register_update_callback' ) ) {
		return;
	}

	woocommerce_store_api_register_update_callback(
		array(
			'namespace' => SKAFLOW_CHECKOUT_QUESTIONS_NAMESPACE,
			'callback'  => 'skaflow_store_cart_checkout_answers',
		)
	);
}

/**
 * Answers currently sitting on the cart session.
 *
 * @return array<string, string>
 */
function skaflow_get_session_checkout_answers() {
	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return array();
	}

	$answers = WC()->session->get( SKAFLOW_CART_ANSWERS_SESSION_KEY, array() );

	return is_array( $answers ) ? $answers : array();
}

/**
 * Merges posted popup answers into the cart session.
 *
 * Called by `POST /wc/store/v1/cart/extensions`. Unknown keys are ignored;
 * known keys are cleaned with the same rules as place-order so a value that
 * would be dropped at checkout is never stored here either.
 *
 * @param array $data Posted `data` object from the extensions request.
 *
 * @return void
 */
function skaflow_store_cart_checkout_answers( $data ) {
	if ( ! is_array( $data ) || ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$current = skaflow_get_session_checkout_answers();

	foreach ( SKAFLOW_CHECKOUT_QUESTION_META as $group ) {
		foreach ( array_keys( $group ) as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$current[ $key ] = skaflow_clean_checkout_answer( $key, $data[ $key ] );
		}
	}

	WC()->session->set( SKAFLOW_CART_ANSWERS_SESSION_KEY, $current );
}
