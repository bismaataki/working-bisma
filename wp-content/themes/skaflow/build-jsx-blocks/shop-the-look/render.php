<?php
/**
 * Shop the Look sidebar widget — server render.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = 0;

if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
	$post_id = absint( $block->context['postId'] );
} elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$post_id = absint( wp_unslash( $_GET['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
} elseif ( is_singular( 'post' ) ) {
	$post_id = (int) get_queried_object_id();
}

if ( $post_id <= 0 ) {
	return;
}

$attributes = is_array( $attributes ) ? $attributes : array();

$markup = skaflow_get_shop_the_look_html( $post_id, $attributes );

if ( '' === trim( $markup ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'sk-shop-the-look is-style-bordered',
	)
);

printf(
	'<div %1$s>%2$s</div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	$markup // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
);
