<?php
/**
 * Query Monitor — stop Booster's deprecation notices from killing cart requests.
 *
 * Booster's `WC_Shipping_WCJ_Custom_Template` assigns one dynamic property per
 * weight-table row, which PHP 8.4 deprecates. `booster_custom_shipping_1` is
 * configured with 98 rows, so constructing it raises 196 notices, and it is
 * constructed on every shipping calculation.
 *
 * On REST and AJAX requests Query Monitor cannot render its HTML panel, so it
 * reports through response headers instead — one header per collected error,
 * each carrying a JSON-encoded stack trace. 196 of those overflow nginx's
 * `fastcgi_buffer_size`, which rejects the response before a body is ever sent.
 * The request surfaces in the browser as a bare 502, and because the response
 * never completes the calculated rates are never stored, so every retry
 * recalculates and fails again. Observed 2026-08-19 taking out
 * `/wp-json/wc/store/v1/cart` and `?wc-ajax=add_to_cart` for logged-in users.
 *
 * Query Monitor collects through its own error handler, so `display_errors`,
 * `error_reporting` and `WP_DEBUG` have no bearing on this — the level has to be
 * set through Query Monitor itself.
 *
 * Deliberately scoped to Booster: every other component still reports its
 * deprecations. This file goes when Booster does.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'qm/collect/php_error_levels', 'happywood_query_monitor_booster_error_level' );

/**
 * Silences deprecations from Booster only, leaving its warnings and errors alone.
 *
 * @param array<string,array<string,int>> $levels Error levels, keyed by component type then component slug.
 * @return array<string,array<string,int>>
 */
function happywood_query_monitor_booster_error_level( $levels ) {
	$levels['plugin']['woocommerce-jetpack'] = E_ALL & ~E_DEPRECATED;

	return $levels;
}
