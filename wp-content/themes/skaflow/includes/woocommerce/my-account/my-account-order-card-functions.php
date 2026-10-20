<?php
/**
 * Shared My Account order card helpers.
 *
 * Markup lives in `partials/account-order-card.php`.
 *
 * @package Skaflow
 */

defined( 'ABSPATH' ) || exit;

/**
 * Render the shared order card partial.
 */
function skaflow_render_account_order_card( WC_Order $order ): void {
	get_template_part(
		'partials/account-order',
		'card',
		array(
			'order' => $order,
		)
	);
}

/**
 * Lucide slug for a shipping line — pickup vs delivery.
 */
function skaflow_get_account_order_shipping_icon_slug( WC_Order_Item_Shipping $item ): string {
	$method = strtolower( (string) $item->get_method_id() );

	return str_contains( $method, 'pickup' ) ? 'map-pin' : 'truck';
}

/**
 * Formatted shipping line amount, matching the order's tax display.
 */
function skaflow_get_account_order_shipping_price( WC_Order $order, WC_Order_Item_Shipping $item ): string {
	if ( ! function_exists( 'wc_price' ) ) {
		return '';
	}

	$inc_tax = function_exists( 'get_option' ) && 'incl' === get_option( 'woocommerce_tax_display_cart' );

	return wc_price(
		$order->get_line_total( $item, $inc_tax, true ),
		array(
			'currency' => $order->get_currency(),
		)
	);
}

/**
 * Visible variation / item attributes, stripped of markup.
 */
function skaflow_get_account_order_item_attrs( WC_Order_Item_Product $item ): string {
	$values = array();

	foreach ( $item->get_formatted_meta_data() as $meta ) {
		$value = isset( $meta->display_value ) ? wp_strip_all_tags( (string) $meta->display_value ) : '';
		$value = trim( $value );

		if ( '' !== $value ) {
			$values[] = $value;
		}
	}

	return implode( ', ', $values );
}

/**
 * WC account actions only — View / Pay / Cancel plus any filtered extras.
 */
function skaflow_get_account_order_actions_markup( WC_Order $order ): string {
	if ( ! function_exists( 'wc_get_account_orders_actions' ) || ! function_exists( 'skaflow_get_buttons_markup' ) ) {
		return '';
	}

	$actions = wc_get_account_orders_actions( $order );

	if ( empty( $actions ) || ! is_array( $actions ) ) {
		return '';
	}

	$buttons = array();

	foreach ( $actions as $key => $action ) {
		if ( empty( $action['name'] ) || empty( $action['url'] ) ) {
			continue;
		}

		$button = array(
			'label' => $action['name'],
			'url'   => $action['url'],
			'style' => 'is-style-light',
			'class' => 'has-small-font-size',
		);

		if ( 'view' === $key ) {
			$button['icon'] = 'eye';
		}

		if ( ! empty( $action['aria-label'] ) ) {
			$button['html_attributes'] = array(
				'aria-label' => $action['aria-label'],
			);
		}

		$buttons[] = $button;
	}

	if ( empty( $buttons ) ) {
		return '';
	}

	return '<footer class="sk-account-order-card__actions">' . skaflow_get_buttons_markup(
		$buttons,
		array(
			'wrapper_class' => 'sk-account-order-card__buttons',
		)
	) . '</footer>';
}
