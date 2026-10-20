<?php
/**
 * Product category swiper — editor-selected product_cat terms.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term_ids = array();

if ( ! empty( $attributes['terms'] ) && is_array( $attributes['terms'] ) ) {
	foreach ( $attributes['terms'] as $term_row ) {
		if ( ! is_array( $term_row ) ) {
			continue;
		}

		$term_id = isset( $term_row['id'] ) ? absint( $term_row['id'] ) : 0;

		if ( $term_id > 0 ) {
			$term_ids[] = $term_id;
		}
	}
}

if ( empty( $term_ids ) ) {
	// No selection means "the catalog", not "nothing". Patterns ship without
	// term IDs because IDs are per-database.
	$items = skaflow_get_product_cat_swiper_default_items();
} else {
	$order_by = isset( $attributes['orderBy'] ) ? (string) $attributes['orderBy'] : 'manual';
	$order    = isset( $attributes['order'] ) ? (string) $attributes['order'] : 'asc';
	$term_ids = skaflow_sort_product_cat_swiper_term_ids( $term_ids, $order_by, $order );
	$items    = skaflow_get_category_swiper_items_from_term_ids( $term_ids );
}

if ( empty( $items ) ) {
	return;
}

$swiper_args = array(
	'showArrowsDesktop' => ! isset( $attributes['showArrowsDesktop'] ) || (bool) $attributes['showArrowsDesktop'],
	'showArrowsMobile'  => isset( $attributes['showArrowsMobile'] ) && (bool) $attributes['showArrowsMobile'],
	'showProgressBar'   => ! isset( $attributes['showProgressBar'] ) || (bool) $attributes['showProgressBar'],
	'showLabels'        => ! isset( $attributes['showLabels'] ) || (bool) $attributes['showLabels'],
	'card_style'        => skaflow_category_swiper_uses_card_style( is_array( $attributes ) ? $attributes : array(), $block ?? null ),
	'instance_id'       => 'sk-product-cat-swiper-' . wp_unique_id(),
);

// Editor preview (ServerSideRender) — static flex list, no Swiper init.
if ( ! empty( $attributes['previewMode'] ) ) {
	$markup = skaflow_get_category_swiper_flex_preview_html( $items, $swiper_args );

	if ( '' === trim( $markup ) ) {
		return;
	}

	echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.

	return;
}

$markup = skaflow_get_category_swiper_html( $items, $swiper_args );

if ( '' === trim( $markup ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'sk-category-swiper',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
</div>
