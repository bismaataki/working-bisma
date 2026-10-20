<?php
/**
 * Product Reviews Slider block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! skaflow_is_product_reviews_enabled() ) {
	return;
}

$attributes   = is_array( $attributes ) ? $attributes : array();
$parsed       = skaflow_parse_product_reviews_slider_block_args( $attributes );
$preview_mode = ! empty( $attributes['previewMode'] );
$scope        = array(
	'product_id'   => $parsed['product_id'],
	'category_ids' => $parsed['category_ids'],
);
$summary      = skaflow_get_verified_review_summary_for_scope( $scope );
$comments     = skaflow_get_verified_reviews_for_slider(
	array(
		'limit'         => $parsed['limit'],
		'order_by'      => $parsed['order_by'],
		'verified_only' => $parsed['verified_only'],
		'product_id'    => $parsed['product_id'],
		'category_ids'  => $parsed['category_ids'],
	)
);

if ( empty( $comments ) && $preview_mode ) {
	$comments = skaflow_get_product_reviews_slider_preview_comments( min( 3, $parsed['limit'] ) );
}

if ( empty( $comments ) && (int) $summary['total'] <= 0 && ! $preview_mode ) {
	return;
}

$instance_id = 'sk-product-reviews-slider-' . wp_unique_id();
$swiper_args = array(
	'showArrowsDesktop' => $parsed['show_arrows_desktop'],
	'showArrowsMobile'  => $parsed['show_arrows_mobile'],
	'showProgressBar'   => $parsed['show_progress_bar'],
	'instance_id'       => $instance_id,
	'card'              => array_merge(
		$parsed['card'],
		array(
			'verifiedOnly' => $parsed['verified_only'],
		)
	),
);

if ( $preview_mode ) {
	$track_markup = skaflow_get_product_reviews_slider_flex_list_html( $comments, $swiper_args );

	if ( '' === trim( $track_markup ) ) {
		$track_markup = skaflow_get_product_reviews_slider_placeholder_slides_html( min( 3, $parsed['limit'] ) );
	}
} else {
	$track_markup = skaflow_get_product_reviews_slider_html( $comments, $swiper_args );
}

if ( '' === trim( $track_markup ) && ! $preview_mode ) {
	return;
}

$summary_markup = '';

if ( $parsed['show_summary'] ) {
	$summary_markup = skaflow_render_verified_reviews_summary_markup( $summary );

	if ( $preview_mode && '' === trim( $summary_markup ) ) {
		$summary_markup = skaflow_render_verified_reviews_summary_markup(
			array(
				'average' => 4.8,
				'total'   => 2847,
			)
		);
	}
}

$show_nav   = $parsed['show_arrows_desktop'] || $parsed['show_arrows_mobile'];
$has_header = '' !== trim( $summary_markup ) || $show_nav;

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'sk-product-reviews-slider',
	)
);

$root_attributes = sprintf(
	'data-sk-product-reviews-slider data-show-arrows-desktop="%1$s" data-show-arrows-mobile="%2$s" data-show-progress-bar="%3$s" id="%4$s"',
	$parsed['show_arrows_desktop'] ? 'true' : 'false',
	$parsed['show_arrows_mobile'] ? 'true' : 'false',
	$parsed['show_progress_bar'] ? 'true' : 'false',
	esc_attr( $instance_id )
);
?>
<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> <?php echo $root_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php if ( $has_header ) : ?>
		<div class="sk-product-reviews-slider__header">
			<?php if ( '' !== trim( $summary_markup ) ) : ?>
				<div class="sk-product-reviews-slider__header-summary">
					<?php echo $summary_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			<?php endif; ?>

			<?php if ( $show_nav ) : ?>
				<?php echo skaflow_get_product_reviews_slider_nav_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== trim( $track_markup ) ) : ?>
		<div class="sk-product-reviews-slider__viewport">
			<?php echo $track_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	<?php endif; ?>
</section>
