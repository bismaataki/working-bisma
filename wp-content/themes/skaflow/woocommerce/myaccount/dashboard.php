<?php
/**
 * My Account dashboard — Overview.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $current_user ) || ! $current_user instanceof WP_User ) {
	$current_user = wp_get_current_user();
}

if ( $current_user instanceof WP_User && $current_user->exists() && function_exists( 'skaflow_render_account_dashboard' ) ) {
	skaflow_render_account_dashboard( $current_user );
}

/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
