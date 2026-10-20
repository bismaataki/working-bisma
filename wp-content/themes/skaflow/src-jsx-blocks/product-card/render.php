<?php
/**
 * Product card block — server render via shared block markup.
 *
 * Renders the canonical Product Collection card inner blocks inside
 * `.wp-block-skaflow-product-card`. Markup lives in
 * skaflow_get_product_card_blocks_markup().
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = $attributes ?? array();

$title_tag       = isset( $attributes['titleTag'] ) ? (string) $attributes['titleTag'] : 'h2';
$block_props     = get_block_wrapper_attributes();
$blocks_rendered = do_blocks(
	skaflow_get_product_card_blocks_markup(
		array(
			'title_tag' => $title_tag,
		)
	)
);

// TODO: Do not remove the Block Wrapper class, its going to be Very Helpful to identify this ourt block no matter where its inserted.
printf(
	'
	<div %s>
		%s
	</div>',
	$block_props, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes().
	$blocks_rendered // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_blocks().
);
