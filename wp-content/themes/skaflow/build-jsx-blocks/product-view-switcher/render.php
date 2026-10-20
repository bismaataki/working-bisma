<?php
/**
 * Product view switcher — grid/list toggle (storefront).
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
	return;
}

if ( ! skaflow_is_product_filter_archive() ) {
	return;
}

$inner = skaflow_render_product_view_switcher_html();

if ( '' === $inner ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class'      => 'sk-product-view-switcher',
		'role'       => 'group',
		'aria-label' => __( 'Product view', 'skaflow' ),
	)
);

printf(
	'<div %1$s>%2$s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
);
