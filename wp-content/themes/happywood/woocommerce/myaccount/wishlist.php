<?php
/**
 * My Account wishlist — product-card grid.
 *
 * Theme-owned endpoint. WooCommerce has no stock wishlist template.
 *
 * @package Happywood
 */

defined( 'ABSPATH' ) || exit;

if ( function_exists( 'happywood_render_account_wishlist' ) ) {
	happywood_render_account_wishlist();
}
