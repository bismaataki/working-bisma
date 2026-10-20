<?php
/**
 * Post card block — server render via shared block markup.
 *
 * Renders the canonical post card inner blocks inside
 * `.wp-block-skaflow-post-card`. Markup lives in
 * skaflow_get_post_card_blocks_markup().
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = is_array( $attributes ) ? $attributes : array();

$wrapper_classes = 'sk-post-card';

if ( ! empty( $attributes['hasBorder'] ) ) {
	$wrapper_classes .= ' sk-post-card--has-border';
}

$block_props = get_block_wrapper_attributes(
	array(
		'class' => $wrapper_classes,
	)
);

$post_id = 0;

if ( $block instanceof WP_Block && ! empty( $block->context['postId'] ) ) {
	$post_id = absint( $block->context['postId'] );
} elseif ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$post_id = absint( wp_unslash( $_GET['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

$needs_teardown = false;
$previous_post  = null;

if ( $post_id > 0 ) {
	$current_post = isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post ? $GLOBALS['post'] : null;

	if ( ! $current_post instanceof WP_Post || (int) $current_post->ID !== $post_id ) {
		$post = get_post( $post_id );

		if ( $post instanceof WP_Post ) {
			$previous_post  = $current_post;
			$needs_teardown = true;

			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['post'] = $post;
			setup_postdata( $post );
		}
	}
}

$blocks_rendered = do_blocks(
	skaflow_get_post_card_blocks_markup( $attributes )
);

if ( $needs_teardown ) {
	wp_reset_postdata();

	if ( $previous_post instanceof WP_Post ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	} else {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = null;
	}
}

// TODO: Do not remove the Block Wrapper class, its going to be Very Helpful to identify this ourt block no matter where its inserted.
printf(
	'
	<div %1$s>
		%2$s
	</div>',
	$block_props, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes().
	$blocks_rendered // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_blocks().
);
