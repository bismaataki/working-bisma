<?php
/**
 * Quantity input markup on shop and product.
 *
 * @package Happywood
 */

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\WooCommerce_Quantity' ) ) {
	return;
}

/**
 * Segmented +/- quantity control for WooCommerce forms.
 */
class WooCommerce_Quantity {

	/**
	 * WooCommerce_Quantity constructor.
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register quantity hooks.
	 */
	private function register_hooks(): void {
		add_action( 'woocommerce_before_quantity_input_field', array( $this, 'render_minus_button' ) );
		add_action( 'woocommerce_after_quantity_input_field', array( $this, 'render_plus_button' ) );
		add_filter( 'woocommerce_quantity_input_classes', array( $this, 'add_quantity_input_class' ) );
	}

	/**
	 * Add theme class to the quantity input.
	 *
	 * @param array<int, string> $classes Input classes.
	 * @return array<int, string>
	 */
	public function add_quantity_input_class( array $classes ): array {
		$classes[] = 'hw-qty-input';

		return $classes;
	}

	/**
	 * Output the decrement button before the quantity field.
	 */
	public function render_minus_button(): void {
		printf(
			'<button type="button" class="hw-qty-button hw-qty-button--minus" aria-label="%1$s"><span aria-hidden="true">&minus;</span></button>',
			esc_attr__( 'Decrease quantity', 'happywood' )
		);
	}

	/**
	 * Output the increment button after the quantity field.
	 */
	public function render_plus_button(): void {
		printf(
			'<button type="button" class="hw-qty-button hw-qty-button--plus" aria-label="%1$s"><span aria-hidden="true">+</span></button>',
			esc_attr__( 'Increase quantity', 'happywood' )
		);
	}
}
