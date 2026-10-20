<?php
/**
 * Delivery and collection answers: the contract.
 *
 * Which meta keys an order records, what values they may hold, and what those
 * read as in English. Everything that displays the answers depends on this file
 * and on nothing else.
 *
 * Deliberately separate from whatever collects them. Today that is the block
 * checkout in `checkout-questions-functions.php` and `src/checkout.js`; if that
 * is replaced by a classic-checkout form, or by WP-CLI backfilling old orders,
 * the surfaces below keep working because the keys did not move. See
 * `context/kb/plugin-wc-checkout-questions.md`.
 *
 * This file also hangs the three customer-facing surfaces: the admin order
 * screen, the confirmation page, and the emails. Collection can go away;
 * display cannot.
 *
 * That is also why nothing here calls into the collection file — it can be
 * deleted without leaving a fatal behind.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Answer key => order meta key, grouped by the shipping choice that asks it.
 *
 * The grouping matters to the collector, which stores only the group matching
 * the method the order actually used. Display walks both groups and shows what
 * is there, so an order carrying one group prints one group.
 *
 * Meta keys are underscore-prefixed to stay out of the HPOS Custom Fields
 * metabox, where they would appear as raw editable keys. Nothing else is hidden
 * by the prefix: order meta is not auto-rendered on any customer surface
 * whatever it is called.
 *
 * The name still says CHECKOUT because that is where the questions are asked.
 * It does not bind them to the block checkout.
 */
const HAPPYWOOD_CHECKOUT_QUESTION_META = array(
	'delivery'   => array(
		'forklift'              => '_happywood_forklift',
		'premises'              => '_happywood_premises',
		'delivery_instructions' => '_happywood_delivery_instructions',
	),
	'collection' => array(
		'collected_by'    => '_happywood_collected_by',
		'carrier_name'    => '_happywood_carrier_name',
		'carrier_account' => '_happywood_carrier_account',
		'carrier_phone'   => '_happywood_carrier_phone',
		'carrier_note'    => '_happywood_carrier_note',
	),
);

/**
 * Accepted values for the fixed-choice answers, so an arbitrary Store API caller
 * cannot write anything it likes into order meta. Free-text answers are absent
 * here and are sanitised rather than matched.
 */
const HAPPYWOOD_CHECKOUT_QUESTION_VALUES = array(
	'forklift'     => array( 'yes', 'no' ),
	'premises'     => array( 'commercial', 'residential' ),
	'collected_by' => array( 'myself', 'carrier' ),
);

/**
 * Human labels for the answer keys.
 *
 * Same wording the shopper saw on the checkout. Rewriting them for the order
 * ("Collected by" / "Their own carrier") looked tidy and then read as a
 * different question. Warehouse, support and the customer should see the same
 * words that were asked.
 *
 * A function rather than a constant so the strings translate on use. Building
 * them at include time would run `__()` before the text domain is loaded.
 *
 * @return array<string, string> Answer key => label.
 */
function happywood_get_order_question_labels() {
	return array(
		'forklift'              => __( 'Do you have a forklift on site?', 'happywood' ),
		'premises'              => __( 'Is this a commercial or residential address?', 'happywood' ),
		'delivery_instructions' => __( 'Delivery instructions', 'happywood' ),
		'collected_by'          => __( 'Who is picking up the items', 'happywood' ),
		'carrier_name'          => __( 'Carrier name', 'happywood' ),
		'carrier_account'       => __( 'Account Number', 'happywood' ),
		'carrier_phone'         => __( 'Phone', 'happywood' ),
		'carrier_note'          => __( 'Pickup notes', 'happywood' ),
	);
}

/**
 * Human labels for the fixed-choice values.
 *
 * Keyed the same as HAPPYWOOD_CHECKOUT_QUESTION_VALUES. Free-text answers are
 * absent and print as stored.
 *
 * @return array<string, array<string, string>> Answer key => value => label.
 */
function happywood_get_order_question_value_labels() {
	return array(
		'forklift'     => array(
			'yes' => __( 'Yes', 'happywood' ),
			'no'  => __( 'No', 'happywood' ),
		),
		'premises'     => array(
			'commercial'  => __( 'Commercial', 'happywood' ),
			'residential' => __( 'Residential', 'happywood' ),
		),
		'collected_by' => array(
			'myself'  => __( 'Myself', 'happywood' ),
			'carrier' => __( 'My Custom Carrier', 'happywood' ),
		),
	);
}

/**
 * The answers an order carries, labelled and ready to print.
 *
 * Returns only keys with a stored value, in the order they were asked, so a
 * delivered order yields the delivery answers and a collected one the collection
 * answers without the caller testing which it is.
 *
 * The shape matches what `woocommerce_email_order_meta_fields` expects, since
 * that is the fussiest of the consumers; the other surfaces read the same array
 * and lay it out themselves.
 *
 * Values are raw. Escaping belongs to whoever renders, because the surfaces do
 * not agree on the context — `esc_html()` for the admin screen, plain text for
 * the plain-text email.
 *
 * @param \WC_Order|int $order Order or order id.
 *
 * @return array<string, array{label: string, value: string}> Empty if the order
 *                                                            has no answers.
 */
function happywood_get_order_answers( $order ) {
	$order = is_a( $order, 'WC_Order' ) ? $order : wc_get_order( $order );

	if ( ! $order ) {
		return array();
	}

	$labels       = happywood_get_order_question_labels();
	$value_labels = happywood_get_order_question_value_labels();
	$answers      = array();

	foreach ( HAPPYWOOD_CHECKOUT_QUESTION_META as $group ) {
		foreach ( $group as $key => $meta_key ) {
			$value = $order->get_meta( $meta_key );

			if ( ! is_scalar( $value ) || '' === trim( (string) $value ) ) {
				continue;
			}

			$value = (string) $value;

			$answers[ $key ] = array(
				'label' => $labels[ $key ] ?? $key,
				'value' => $value_labels[ $key ][ $value ] ?? $value,
			);
		}
	}

	return $answers;
}

/**
 * Which group of questions an order answered.
 *
 * Decided from the answers themselves rather than from the shipping method,
 * because this file must not depend on the collector — see the header. The two
 * agree in practice: the collector stores one group per order.
 *
 * @param array $answers Output of happywood_get_order_answers().
 *
 * @return string 'collection', 'delivery', or '' when there is nothing to show.
 */
function happywood_get_order_answers_group( array $answers ) {
	if ( ! $answers ) {
		return '';
	}

	$collection = array_keys( HAPPYWOOD_CHECKOUT_QUESTION_META['collection'] );

	return array_intersect_key( $answers, array_flip( $collection ) ) ? 'collection' : 'delivery';
}

/**
 * Heading for a block of answers, named after the checkout path that asked them.
 *
 * "Pickup" is the word on the Ship / Pickup toggle, so the pickup answers sit
 * under Pickup details rather than a synonym.
 *
 * @param array $answers Output of happywood_get_order_answers().
 *
 * @return string Empty when there is nothing to head.
 */
function happywood_get_order_answers_heading( array $answers ) {
	switch ( happywood_get_order_answers_group( $answers ) ) {
		case 'collection':
			return __( 'Pickup details', 'happywood' );
		case 'delivery':
			return __( 'Delivery details', 'happywood' );
		default:
			return '';
	}
}

add_action( 'woocommerce_admin_order_data_after_shipping_address', 'happywood_render_admin_order_answers' );
add_action( 'woocommerce_order_details_after_order_table', 'happywood_render_confirmation_order_answers' );
add_action( 'woocommerce_thankyou', 'happywood_render_confirmation_order_answers' );
add_filter( 'woocommerce_email_order_meta_fields', 'happywood_email_order_answers', 10, 3 );
add_filter( 'woocommerce_get_order_item_totals', 'happywood_add_order_answers_to_totals', 10, 2 );

/**
 * Markup for a labelled list of answers, escaped for HTML.
 *
 * Shared by the admin screen and the confirmation page. Emails do not use it:
 * `woocommerce_email_order_meta_fields` wants an array, and the totals-row
 * fallback wants one cell per answer.
 *
 * `$variant` is `admin` or `customer`. Admin reuses the email grouping: who is
 * collecting, then name / account / phone under that label, notes on the next
 * line. Customer is a definition list using WooCommerce's additional-fields
 * markup.
 *
 * @param array  $answers Output of happywood_get_order_answers().
 * @param string $variant `admin` or `customer`.
 *
 * @return string Empty when there is nothing to print.
 */
function happywood_get_order_answers_html( array $answers, $variant = 'customer' ) {
	if ( ! $answers ) {
		return '';
	}

	$heading = happywood_get_order_answers_heading( $answers );

	if ( 'admin' === $variant ) {
		$items = '';

		foreach ( happywood_get_order_answers_totals_rows( $answers ) as $row ) {
			$items .= sprintf(
				'<p><strong>%s</strong><br>%s</p>',
				esc_html( $row['label'] ),
				wp_kses_post( $row['value'] )
			);
		}

		return sprintf(
			'<div class="address hw-order-answers">%s%s</div>',
			$heading ? '<h3>' . esc_html( $heading ) . '</h3>' : '',
			$items
		);
	}

	$items = '';

	foreach ( $answers as $answer ) {
		$items .= sprintf(
			'<dt>%s</dt><dd>%s</dd>',
			esc_html( $answer['label'] ),
			esc_html( $answer['value'] )
		);
	}

	return sprintf(
		'<section class="wc-block-order-confirmation-additional-fields hw-order-answers">%s<dl class="wc-block-components-additional-fields-list">%s</dl></section>',
		$heading ? '<h2 class="hw-order-answers__title">' . esc_html( $heading ) . '</h2>' : '',
		$items
	);
}

/**
 * Prints answers under the shipping address on the admin order screen.
 *
 * @param \WC_Order $order Order being viewed.
 *
 * @return void
 */
function happywood_render_admin_order_answers( $order ) {
	echo happywood_get_order_answers_html( happywood_get_order_answers( $order ), 'admin' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in happywood_get_order_answers_html().
}

/**
 * Prints answers after the totals table on the confirmation (and My Account view).
 *
 * Hung on both confirmation hooks the blocks still fire. Totals runs
 * `woocommerce_order_details_after_order_table`; Additional information runs
 * `woocommerce_thankyou` (and passes an id, not an order). A per-order guard
 * keeps the list from printing twice when both blocks are present, which is
 * the default template. Removing either block still leaves the other.
 *
 * On the theme order-confirmation template the fulfillment card owns the
 * answers, so this hook no-ops there. Remove that card and the list returns.
 *
 * @param \WC_Order|int $order Order being viewed, or its id.
 *
 * @return void
 */
function happywood_render_confirmation_order_answers( $order ) {
	$order = is_a( $order, 'WC_Order' ) ? $order : wc_get_order( $order );

	if ( ! $order ) {
		return;
	}

	if (
		function_exists( 'is_order_received_page' )
		&& is_order_received_page()
		&& function_exists( 'happywood_confirmation_template_has_fulfillment_block' )
		&& happywood_confirmation_template_has_fulfillment_block()
	) {
		return;
	}

	static $printed = array();

	$id = $order->get_id();

	if ( isset( $printed[ $id ] ) ) {
		return;
	}

	$html = happywood_get_order_answers_html( happywood_get_order_answers( $order ), 'customer' );

	if ( '' === $html ) {
		return;
	}

	$printed[ $id ] = true;

	echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in happywood_get_order_answers_html().
}

/**
 * Adds answers to the classic email order-meta list.
 *
 * WooCommerce renders this as `<p><strong>Label:</strong> value</p>` in both
 * HTML and plain-text mail. YayMail only reaches it when a template includes
 * the `[woocommerce_email_order_meta]` element — none of the live templates
 * here do. The totals-row filter below is what actually prints in those emails.
 *
 * @param array     $fields        Existing meta fields.
 * @param bool      $sent_to_admin Unused; both recipient types get the same answers.
 * @param \WC_Order $order        Order the email is about.
 *
 * @return array
 */
function happywood_email_order_answers( $fields, $sent_to_admin, $order ) {
	unset( $sent_to_admin );

	if ( ! is_array( $fields ) ) {
		$fields = array();
	}

	foreach ( happywood_get_order_answers( $order ) as $key => $answer ) {
		$fields[ 'happywood_' . $key ] = array(
			'label' => $answer['label'],
			'value' => $answer['value'],
		);
	}

	return $fields;
}

/**
 * Totals rows for an email: pickup collapses onto two rows, delivery stays one each.
 *
 * Carrier name, account and phone are details of "Who is picking up the items",
 * not questions of their own. They sit under that label as extra lines, the
 * choice itself in bold — the same shape WooCommerce uses for a pickup
 * location. Pickup notes stay a row, because they are a separate ask.
 * Myself has no extra lines, so the cell is just the bold choice.
 *
 * @param array $answers Output of happywood_get_order_answers().
 *
 * @return array<string, array{label: string, value: string}>
 */
function happywood_get_order_answers_totals_rows( array $answers ) {
	if ( isset( $answers['collected_by'] ) ) {
		$lines = array(
			'<strong>' . esc_html( $answers['collected_by']['value'] ) . '</strong>',
		);

		foreach ( array( 'carrier_name', 'carrier_account', 'carrier_phone' ) as $key ) {
			if ( ! empty( $answers[ $key ]['value'] ) ) {
				$lines[] = esc_html( $answers[ $key ]['value'] );
			}
		}

		$rows = array(
			'happywood_collected_by' => array(
				'label' => $answers['collected_by']['label'] . ':',
				'value' => implode( '<br>', $lines ),
			),
		);

		if ( ! empty( $answers['carrier_note']['value'] ) ) {
			$rows['happywood_carrier_note'] = array(
				'label' => $answers['carrier_note']['label'] . ':',
				'value' => esc_html( $answers['carrier_note']['value'] ),
			);
		}

		return $rows;
	}

	$rows = array();

	foreach ( $answers as $key => $answer ) {
		$rows[ 'happywood_' . $key ] = array(
			'label' => $answer['label'] . ':',
			'value' => esc_html( $answer['value'] ),
		);
	}

	return $rows;
}

/**
 * Adds answer rows to the order totals table, so YayMail emails show them
 * without a template edit.
 *
 * YayMail's Order Details element calls `get_order_item_totals()`, which is how
 * the pickup location already appears. Same door, our answers. Pickup fields
 * share one row; see happywood_get_order_answers_totals_rows().
 *
 * Skipped on the confirmation and My Account view-order pages, where the
 * after-table hook already prints a labelled list. During email send those
 * conditionals are false — the Store API request and an admin resend are not
 * those pages — so the rows land in the mail and nowhere else.
 *
 * @param array     $rows  Totals rows keyed by id.
 * @param \WC_Order $order Order being totalled.
 *
 * @return array
 */
function happywood_add_order_answers_to_totals( $rows, $order ) {
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return $rows;
	}

	if ( function_exists( 'is_view_order_page' ) && is_view_order_page() ) {
		return $rows;
	}

	$answers = happywood_get_order_answers( $order );

	if ( ! $answers || ! is_array( $rows ) ) {
		return $rows;
	}

	$insert = happywood_get_order_answers_totals_rows( $answers );

	if ( isset( $rows['shipping'] ) ) {
		$out = array();

		foreach ( $rows as $key => $row ) {
			$out[ $key ] = $row;

			if ( 'shipping' === $key ) {
				$out += $insert;
			}
		}

		return $out;
	}

	if ( isset( $rows['order_total'] ) ) {
		$out = array();

		foreach ( $rows as $key => $row ) {
			if ( 'order_total' === $key ) {
				$out += $insert;
			}

			$out[ $key ] = $row;
		}

		return $out;
	}

	return $rows + $insert;
}
