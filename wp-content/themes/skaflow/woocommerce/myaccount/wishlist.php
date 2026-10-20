<?php
/**
 * My Account wishlist — product-card grid.
 *
 * Theme-owned endpoint. WooCommerce has no stock wishlist template.
 *
 * @package Skaflow
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'skaflow_render_account_wishlist' ) ) {
	skaflow_render_account_wishlist();
}
