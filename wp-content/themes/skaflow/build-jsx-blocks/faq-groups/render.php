<?php
/**
 * FAQ Groups — editor-selected FAQ category sections with accordions.
 *
 * @var array $attributes Block attributes.
 * @var string $content Block content.
 * @var WP_Block $block Block instance.
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
	$term_ids = get_terms(
		array(
			'taxonomy'   => 'skaflow_faq_category',
			'hide_empty' => true,
			'fields'     => 'ids',
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);
}

if ( empty( $term_ids ) || is_wp_error( $term_ids ) ) {
	return;
}

$order_by  = isset( $attributes['orderBy'] ) ? (string) $attributes['orderBy'] : 'manual';
$order     = isset( $attributes['order'] ) ? (string) $attributes['order'] : 'asc';
$title_tag = isset( $attributes['accordionTitleTag'] ) ? (string) $attributes['accordionTitleTag'] : 'h3';
$term_ids  = skaflow_sort_faq_groups_term_ids( $term_ids, $order_by, $order );

$queue_schema = empty( $attributes['previewMode'] );
$markup       = skaflow_get_faq_groups_html(
	$term_ids,
	array(
		'queue_schema' => $queue_schema,
		'title_tag'    => $title_tag,
	)
);

if ( '' === trim( $markup ) ) {
	return;
}

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'sk-faq-groups',
	)
);
?>
<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		data-sk-faq-groups>
	<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
</div>
