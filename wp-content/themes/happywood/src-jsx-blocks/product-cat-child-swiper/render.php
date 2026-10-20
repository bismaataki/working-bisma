<?php
/**
 * Product category child swiper — immediate children of the queried product_cat term.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$items = happywood_get_queried_product_cat_child_swiper_items();

if ( empty( $items ) ) {
	return;
}

$swiper_args = array(
	'showArrowsDesktop' => ! isset( $attributes['showArrowsDesktop'] ) || (bool) $attributes['showArrowsDesktop'],
	'showArrowsMobile'  => isset( $attributes['showArrowsMobile'] ) && (bool) $attributes['showArrowsMobile'],
	'showProgressBar'   => ! isset( $attributes['showProgressBar'] ) || (bool) $attributes['showProgressBar'],
	'showLabels'        => ! isset( $attributes['showLabels'] ) || (bool) $attributes['showLabels'],
	'card_style'        => happywood_category_swiper_uses_card_style( is_array( $attributes ) ? $attributes : array(), $block ?? null ),
	'instance_id'       => 'hw-product-cat-child-swiper-' . wp_unique_id(),
);

$markup = happywood_get_category_swiper_html( $items, $swiper_args );

if ( '' === trim( $markup ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'hw-category-swiper',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
</div>
