<?php
/**
 * Order confirmation fulfillment card — server render.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'happywood_get_confirmation_order' ) ) {
	return;
}

$order = happywood_get_confirmation_order();

if ( ! $order ) {
	if ( is_admin() ) {
		echo happywood_get_confirmation_editor_placeholder_html( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			__( 'Delivery / Pickup Information', 'happywood' ),
			__( 'Shows the WooCommerce address or warehouse, payment, method, and the order answers.', 'happywood' )
		);
	}
	return;
}

$wrapper_attributes = get_block_wrapper_attributes();

printf(
	'<div %1$s>%2$s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	happywood_get_confirmation_fulfillment_html( $order ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
);
