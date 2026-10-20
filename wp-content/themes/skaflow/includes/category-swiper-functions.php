<?php
/**
 * Reusable category swiper markup helpers.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize category swiper display options.
 *
 * @param array<string, mixed> $args Raw options.
 * @return array{
 *     show_arrows_desktop: bool,
 *     show_arrows_mobile: bool,
 *     show_progress_bar: bool,
 *     show_labels: bool,
 *     card_style: bool,
 *     instance_id: string,
 * }
 */
function skaflow_normalize_category_swiper_args( array $args = array() ): array {
	$instance_id = isset( $args['instance_id'] )
		? sanitize_html_class( (string) $args['instance_id'] )
		: 'sk-category-swiper';

	if ( '' === $instance_id ) {
		$instance_id = 'sk-category-swiper';
	}

	return array(
		'show_arrows_desktop' => ! isset( $args['showArrowsDesktop'] ) || (bool) $args['showArrowsDesktop'],
		'show_arrows_mobile'  => isset( $args['showArrowsMobile'] ) && (bool) $args['showArrowsMobile'],
		'show_progress_bar'   => ! isset( $args['showProgressBar'] ) || (bool) $args['showProgressBar'],
		'show_labels'         => ! isset( $args['showLabels'] ) || (bool) $args['showLabels'],
		'card_style'          => ! empty( $args['card_style'] ),
		'instance_id'         => $instance_id,
	);
}

/**
 * Whether a category swiper block is using the Category cards style variation.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param WP_Block|null        $block      Block instance.
 * @return bool
 */
function skaflow_category_swiper_uses_card_style( array $attributes = array(), ?WP_Block $block = null ): bool {
	$class_sources = array();

	if ( ! empty( $attributes['className'] ) && is_string( $attributes['className'] ) ) {
		$class_sources[] = $attributes['className'];
	}

	if ( $block instanceof WP_Block ) {
		if ( ! empty( $block->attributes['className'] ) && is_string( $block->attributes['className'] ) ) {
			$class_sources[] = $block->attributes['className'];
		}

		if ( ! empty( $block->parsed_block['attrs']['className'] ) && is_string( $block->parsed_block['attrs']['className'] ) ) {
			$class_sources[] = $block->parsed_block['attrs']['className'];
		}
	}

	foreach ( $class_sources as $class_name ) {
		if ( str_contains( $class_name, 'is-style-card' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Render category swiper prev/next controls.
 *
 * @return string
 */
function skaflow_get_category_swiper_nav_html(): string {
	ob_start();
	?>
	<div class="sk-category-swiper__nav" aria-hidden="false">
		<button
			type="button"
			class="sk-category-swiper__arrow sk-category-swiper__arrow--prev"
			aria-label="<?php esc_attr_e( 'Previous categories', 'skaflow' ); ?>"
		></button>
		<button
			type="button"
			class="sk-category-swiper__arrow sk-category-swiper__arrow--next"
			aria-label="<?php esc_attr_e( 'Next categories', 'skaflow' ); ?>"
		></button>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render category swiper markup from a prepared items config.
 *
 * @param array<int, array{
 *     id?: int,
 *     title?: string,
 *     label?: string,
 *     url?: string,
 *     image_id?: int,
 * }> $items Swiper slides.
 * @param array<string, mixed> $args Display options.
 * @return string
 */
function skaflow_get_category_swiper_html( array $items, array $args = array() ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$options  = skaflow_normalize_category_swiper_args( $args );
	$show_nav = $options['show_arrows_desktop'] || $options['show_arrows_mobile'];
	$nav_html = $show_nav ? skaflow_get_category_swiper_nav_html() : '';

	ob_start();
	?>
	<div
		class="sk-category-swiper__viewport"
		data-sk-category-swiper
		data-show-arrows-desktop="<?php echo $options['show_arrows_desktop'] ? 'true' : 'false'; ?>"
		data-show-arrows-mobile="<?php echo $options['show_arrows_mobile'] ? 'true' : 'false'; ?>"
		data-show-progress-bar="<?php echo $options['show_progress_bar'] ? 'true' : 'false'; ?>"
		data-show-labels="<?php echo $options['show_labels'] ? 'true' : 'false'; ?>"
		id="<?php echo esc_attr( $options['instance_id'] ); ?>"
	>
		<?php if ( $options['card_style'] && $show_nav ) : ?>
			<div class="sk-category-swiper__header">
				<?php echo $nav_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		<?php endif; ?>
		<div class="sk-category-swiper__track">
			<div class="swiper sk-category-swiper__swiper">
				<div class="swiper-wrapper">
					<?php
					foreach ( $items as $item ) {
						echo skaflow_get_category_swiper_slide_html( $item, $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					}
					?>
				</div>
				<?php if ( $options['show_progress_bar'] ) : ?>
					<div class="swiper-scrollbar sk-category-swiper__scrollbar" aria-hidden="true"></div>
				<?php endif; ?>
			</div>
			<?php if ( ! $options['card_style'] && $show_nav ) : ?>
				<?php echo $nav_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			<?php endif; ?>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render a static flex row of category cards (editor preview — no Swiper JS).
 *
 * @param array<int, array<string, mixed>> $items Swiper slides.
 * @param array<string, mixed>             $args  Display options.
 * @return string
 */
function skaflow_get_category_swiper_flex_list_html( array $items, array $args = array() ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$options                 = skaflow_normalize_category_swiper_args( $args );
	$options['static_slide'] = true;

	ob_start();
	?>
	<div class="sk-category-swiper__flex-list">
		<?php
		foreach ( $items as $item ) {
			echo skaflow_get_category_swiper_slide_html( $item, $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
		}
		?>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render editor preview markup (static flex list, optional card-style header nav).
 *
 * @param array<int, array<string, mixed>> $items Swiper slides.
 * @param array<string, mixed>             $args  Display options.
 * @return string
 */
function skaflow_get_category_swiper_flex_preview_html( array $items, array $args = array() ): string {
	$list_markup = skaflow_get_category_swiper_flex_list_html( $items, $args );

	if ( '' === trim( $list_markup ) ) {
		return '';
	}

	$options  = skaflow_normalize_category_swiper_args( $args );
	$show_nav = $options['show_arrows_desktop'] || $options['show_arrows_mobile'];
	$nav_html = $show_nav ? skaflow_get_category_swiper_nav_html() : '';

	if ( ! $options['card_style'] ) {
		return $list_markup;
	}

	ob_start();
	?>
	<div class="sk-category-swiper__viewport">
		<?php if ( $show_nav ) : ?>
			<div class="sk-category-swiper__header">
				<?php echo $nav_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
			</div>
		<?php endif; ?>
		<div class="sk-category-swiper__track">
			<?php echo $list_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render one swiper slide.
 *
 * @param array<string, mixed> $item Slide config.
 * @param array<string, mixed> $options Normalized display options.
 * @return string
 */
function skaflow_get_category_swiper_slide_html( array $item, array $options ): string {
	$url        = isset( $item['url'] ) ? (string) $item['url'] : '';
	$title      = isset( $item['title'] ) ? (string) $item['title'] : '';
	$label      = isset( $item['label'] ) ? (string) $item['label'] : $title;
	$image_id   = isset( $item['image_id'] ) ? absint( $item['image_id'] ) : 0;
	$item_id    = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
	$card_style = ! empty( $options['card_style'] );

	if ( '' === trim( $url ) ) {
		return '';
	}

	if ( ! $card_style && '' === trim( $label ) ) {
		return '';
	}

	$card_classes = 'sk-category-swiper__card';

	if ( $card_style ) {
		$card_classes .= ' sk-category-swiper__card--overlay';
	} elseif ( empty( $options['show_labels'] ) ) {
		$card_classes .= ' sk-category-swiper__card--no-label';
	}

	$slide_classes = ! empty( $options['static_slide'] )
		? 'sk-category-swiper__slide sk-category-swiper__slide--static'
		: 'swiper-slide sk-category-swiper__slide';

	$image_html = skaflow_get_category_swiper_slide_image_html(
		$image_id,
		$title,
		array(
			'card_style' => $card_style,
		)
	);

	ob_start();
	?>
	<div class="<?php echo esc_attr( $slide_classes ); ?>"<?php echo $item_id > 0 ? ' data-term-id="' . esc_attr( (string) $item_id ) . '"' : ''; ?>>
		<a class="<?php echo esc_attr( $card_classes ); ?>" href="<?php echo esc_url( $url ); ?>">
			<span class="sk-category-swiper__media">
				<?php echo $image_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
				<?php if ( $card_style ) : ?>
					<span class="sk-category-swiper__overlay">
						<?php if ( ! empty( $options['show_labels'] ) && '' !== trim( $label ) ) : ?>
							<span class="sk-category-swiper__title"><?php echo esc_html( $label ); ?></span>
						<?php endif; ?>
						<span class="sk-category-swiper__cta"><?php esc_html_e( 'Shop now', 'skaflow' ); ?></span>
					</span>
				<?php endif; ?>
			</span>
			<?php if ( ! $card_style && ! empty( $options['show_labels'] ) ) : ?>
				<span class="sk-category-swiper__label"><?php echo esc_html( $label ); ?></span>
			<?php endif; ?>
		</a>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render a slide image from an attachment ID or theme placeholder.
 *
 * @param int                  $image_id Attachment ID.
 * @param string               $alt      Accessible image label.
 * @param array<string, mixed> $args     Optional render args.
 * @return string
 */
function skaflow_get_category_swiper_slide_image_html( int $image_id, string $alt, array $args = array() ): string {
	$card_style = ! empty( $args['card_style'] );
	$image_size = 'thumbnail';

	if ( $card_style ) {
		$image_size = function_exists( 'wc_get_image_size' ) ? 'woocommerce_thumbnail' : 'medium';
	}
	$image_class = 'sk-category-swiper__image';

	if ( $card_style ) {
		$image_class .= ' sk-category-swiper__image--cover';
	}

	if ( $image_id > 0 ) {
		$image = wp_get_attachment_image(
			$image_id,
			$image_size,
			false,
			array(
				'class'    => $image_class,
				'alt'      => $alt,
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		if ( $image ) {
			return $image;
		}
	}

	$placeholder_id = skaflow_get_default_placeholder_image_id();

	if ( $placeholder_id > 0 ) {
		$placeholder = wp_get_attachment_image(
			$placeholder_id,
			$image_size,
			false,
			array(
				'class'    => $image_class . ' sk-category-swiper__image--placeholder',
				'alt'      => $alt,
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		if ( $placeholder ) {
			return $placeholder;
		}
	}

	if ( ! $card_style ) {
		return sprintf(
			'<img src="%s" class="%s sk-category-swiper__image--placeholder" alt="%s" loading="lazy" decoding="async" width="100" height="100" />',
			esc_url( get_theme_file_uri( SKAFLOW_MENU_THUMB_PLACEHOLDER ) ),
			esc_attr( $image_class ),
			esc_attr( $alt )
		);
	}

	return '';
}
