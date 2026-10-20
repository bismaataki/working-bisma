<?php
/**
 * WooCommerce feature class wiring.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\WooCommerce' ) ) {
	return;
}

/**
 * WooCommerce integration entry point.
 */
class WooCommerce {

	/**
	 * WooCommerce constructor.
	 */
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		new WooCommerce_Admin();
		new WooCommerce_Archive();
		new Classic_WC_Compatibility();
		new WooCommerce_Breadcrumbs();
		new WooCommerce_Single_Product();
		new WooCommerce_Quantity();
		new Product_Swatches();
		new Wishlist();
	}
}
