<?php
/**
 * Commerce includes — WooCommerce.
 *
 * Loaded unconditionally from `includes/init.php`. Every file below only
 * registers hooks, so nothing here executes on a site without WooCommerce.
 * The plugin guard lives in `Skaflow\WooCommerce` (`lib/class-woocommerce.php`),
 * which is what gates the commerce classes. Keep it that way: a top-level call
 * to `wc_*()` or `WC()` in any file below turns a missing plugin into a fatal.
 *
 * Delete this directory and that one require on a project without commerce.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = get_stylesheet_directory() . '/includes/woocommerce';

require_once $dir . '/woocommerce-functions.php';
require_once $dir . '/woocommerce-breadcrumb-functions.php';
require_once $dir . '/product-filters-query-functions.php';
require_once $dir . '/product-filters-groups-functions.php';
require_once $dir . '/product-filters-functions.php';
require_once $dir . '/load-more-functions.php';
require_once $dir . '/search-hooks.php';
require_once $dir . '/product-card-functions.php';
require_once $dir . '/product-card-hooks.php';
require_once $dir . '/product-tabs-functions.php';
require_once $dir . '/product-reviews-settings-functions.php';
require_once $dir . '/product-reviews-query-functions.php';
require_once $dir . '/product-reviews-functions.php';
require_once $dir . '/product-reviews-slider-functions.php';
require_once $dir . '/product-cat-swiper-functions.php';
require_once $dir . '/product-cat-banner-functions.php';
require_once $dir . '/product-faqs-functions.php';
require_once $dir . '/quick-view-functions.php';
require_once $dir . '/delivery-estimate-functions.php';
require_once $dir . '/blocks-cart-guard-functions.php';
require_once $dir . '/order-questions-functions.php';
require_once $dir . '/checkout-questions-functions.php';
require_once $dir . '/cart-questions-functions.php';
require_once $dir . '/order-confirmation-functions.php';
require_once $dir . '/my-account/my-account-functions.php';
require_once $dir . '/my-account/my-account-order-card-functions.php';
require_once $dir . '/my-account/my-account-dashboard-functions.php';
require_once $dir . '/my-account/my-account-orders-functions.php';
require_once $dir . '/my-account/my-account-wishlist-functions.php';
require_once $dir . '/review-card-functions.php';
require_once $dir . '/shop-the-look-functions.php';
require_once $dir . '/request-quote-functions.php';
