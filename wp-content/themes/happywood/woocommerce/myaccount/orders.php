<?php
/**
 * My Account orders — card list.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $has_orders ) ) {
	$has_orders = false;
}

if ( ! isset( $current_page ) ) {
	$current_page = 1;
}

do_action( 'woocommerce_before_account_orders', $has_orders );

if ( function_exists( 'happywood_render_account_orders' ) ) {
	happywood_render_account_orders(
		isset( $customer_orders ) ? $customer_orders : null,
		(bool) $has_orders,
		(int) $current_page
	);
}

do_action( 'woocommerce_after_account_orders', $has_orders );
