<?php
/**
 * Wishlist toggle button — server render.
 *
 * @var array $attributes Block attributes.
 * @var string $content Block content.
 * @var WP_Block $block Block instance.
 */

use Happywood\Wishlist;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$display_context = isset( $attributes['context'] ) ? (string) $attributes['context'] : 'card';
$display_context = in_array( $display_context, array( 'card', 'summary' ), true ) ? $display_context : 'card';

if ( 'card' === $display_context && ! happywood_is_wishlist_loop_enabled() ) {
	return;
}

if ( ! happywood_is_wishlist_enabled() ) {
	return;
}

$product_id = isset( $block->context['postId'] ) ? absint( $block->context['postId'] ) : 0;

if ( ! $product_id || ! Wishlist::is_valid_product( $product_id ) ) {
	return;
}

$in_wishlist = false;

if ( is_user_logged_in() ) {
	$in_wishlist = Wishlist::is_in_wishlist( get_current_user_id(), $product_id );
}

$wrapper_class = 'hw-wishlist-button';

if ( 'summary' === $display_context ) {
	$wrapper_class .= ' hw-wishlist-button--summary';
}

if ( $in_wishlist ) {
	$wrapper_class .= ' is-active';
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_class,
	)
);

$aria_pressed = $in_wishlist ? 'true' : 'false';
$label        = $in_wishlist
	? esc_attr__( 'Remove from wishlist', 'happywood' )
	: esc_attr__( 'Add to wishlist', 'happywood' );

if ( 'summary' === $display_context ) {
	printf(
		'<div %1$s><button type="button" class="hw-wishlist-trigger" data-product-id="%2$s" aria-pressed="%3$s" aria-label="%4$s"><span class="hw-wishlist-trigger__label" aria-hidden="true">%5$s</span></button></div>',
		$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes().
		esc_attr( (string) $product_id ),
		esc_attr( $aria_pressed ),
		esc_attr( $label ),
		esc_html( $in_wishlist ? __( 'Remove from wishlist', 'happywood' ) : __( 'Add to wishlist', 'happywood' ) )
	);

	return;
}

printf(
	'<div %1$s><button type="button" class="hw-wishlist-trigger" data-product-id="%2$s" aria-pressed="%3$s" aria-label="%4$s"><span class="screen-reader-text">%4$s</span></button></div>',
	$wrapper_attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes().
	esc_attr( (string) $product_id ),
	esc_attr( $aria_pressed ),
	esc_attr( $label )
);
