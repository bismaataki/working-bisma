<?php
/**
 * Product reviews — theme options, display mode, and tab gates.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a boolean theme option with a default when unset.
 *
 * @param string $field_name ACF field name.
 * @param bool   $default    Default when ACF missing or value empty.
 * @return bool
 */
function skaflow_get_theme_option_bool( string $field_name, bool $default = true ): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return $default;
	}

	$value = get_field( $field_name, 'option' );

	if ( null === $value || '' === $value ) {
		return $default;
	}

	return (bool) $value;
}

/**
 * Whether product reviews are enabled globally (WooCommerce setting).
 */
function skaflow_is_product_reviews_enabled(): bool {
	return get_option( 'woocommerce_enable_reviews' ) === 'yes';
}

/**
 * Product reviews display mode: section or tab.
 */
function skaflow_get_product_reviews_display_mode(): string {
	if ( ! function_exists( 'get_field' ) ) {
		return 'section';
	}

	$value = get_field( 'product_reviews_display', 'option' );

	if ( ! in_array( $value, array( 'tab', 'section' ), true ) ) {
		return 'section';
	}

	return $value;
}

/**
 * Whether reviews render as the Figma standalone section (blocks).
 */
function skaflow_is_product_reviews_section_mode(): bool {
	return skaflow_is_product_reviews_enabled()
		&& 'section' === skaflow_get_product_reviews_display_mode();
}

/**
 * Whether reviews render in the WooCommerce Reviews tab only.
 */
function skaflow_is_product_reviews_tab_mode(): bool {
	return skaflow_is_product_reviews_enabled()
		&& 'tab' === skaflow_get_product_reviews_display_mode();
}

/**
 * Whether reviews UI should render for a product (global + per-product).
 *
 * @param int $product_id Product post ID.
 * @return bool
 */
function skaflow_should_show_product_reviews( int $product_id ): bool {
	if ( ! skaflow_is_product_reviews_enabled() ) {
		return false;
	}

	if ( $product_id <= 0 || ! function_exists( 'wc_get_product' ) ) {
		return false;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return false;
	}

	return $product->get_reviews_allowed();
}

/**
 * Whether a custom single-product tab is enabled in theme options.
 *
 * @param string $tab_key WooCommerce tab key.
 * @return bool
 */
function skaflow_is_product_tab_enabled( string $tab_key ): bool {
	$map = array(
		'description'               => 'product_tab_overview_enabled',
		'sk_delivery_information'   => 'product_tab_delivery_enabled',
		'sk_dimensions'             => 'product_tab_dimensions_enabled',
		'sk_care'                   => 'product_tab_care_enabled',
		'sk_parts'                  => 'product_tab_parts_enabled',
		'sk_additional_information' => 'product_tab_additional_info_enabled',
	);

	if ( ! isset( $map[ $tab_key ] ) ) {
		return true;
	}

	return skaflow_get_theme_option_bool( $map[ $tab_key ], true );
}
