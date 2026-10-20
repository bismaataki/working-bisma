<?php
/**
 * Product archive layered-nav — filter chrome, panel, chips, and swatch markup.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the full product filters shell markup.
 *
 * @param array<int, array<string, mixed>>|null $filter_groups Optional precomputed groups.
 * @return string
 */
function skaflow_render_product_filters_html( ?array $filter_groups = null ): string {
	if ( null !== $filter_groups && empty( $filter_groups ) ) {
		return '';
	}

	ob_start();
	get_template_part(
		'partials/product',
		'filters',
		null !== $filter_groups
			? array(
				'filter_groups' => $filter_groups,
			)
			: array()
	);

	return (string) ob_get_clean();
}

/**
 * Render filter chrome only (bar + chips) for the white filters band.
 *
 * @param array<int, array<string, mixed>>|null $filter_groups Optional precomputed groups.
 * @return string
 */
function skaflow_render_product_filters_chrome_html( ?array $filter_groups = null ): string {
	if ( null !== $filter_groups && empty( $filter_groups ) ) {
		return '';
	}

	ob_start();
	get_template_part(
		'partials/product-filters',
		'chrome',
		null !== $filter_groups
			? array(
				'filter_groups' => $filter_groups,
			)
			: array()
	);

	return (string) ob_get_clean();
}

/**
 * Render filter sidebar only (panel host) for the shop grid band.
 *
 * @param array<int, array<string, mixed>>|null $filter_groups Optional precomputed groups.
 * @return string
 */
function skaflow_render_product_filters_sidebar_html( ?array $filter_groups = null ): string {
	if ( null !== $filter_groups && empty( $filter_groups ) ) {
		return '';
	}

	ob_start();
	get_template_part(
		'partials/product-filters',
		'sidebar',
		null !== $filter_groups
			? array(
				'filter_groups' => $filter_groups,
			)
			: array()
	);

	return (string) ob_get_clean();
}

/**
 * Render grid/list view toggle buttons for product archives.
 *
 * @return string
 */
function skaflow_render_product_view_switcher_html(): string {

	$buttons = array(
		array(
			'label'           => __( 'Grid view', 'skaflow' ),
			'tag'             => 'button',
			'style'           => 'is-style-light',
			'icon'            => 'grid-2x2',
			'class'           => 'sk-product-view-switcher__button',
			'element_class'   => 'sk-product-view-switcher__control',
			'html_attributes' => array(
				'data-sk-products-view' => 'grid',
				'aria-pressed'          => 'true',
			),
		),
		array(
			'label'           => __( 'List view', 'skaflow' ),
			'tag'             => 'button',
			'style'           => 'is-style-light',
			'icon'            => 'list',
			'class'           => 'sk-product-view-switcher__button',
			'element_class'   => 'sk-product-view-switcher__control',
			'html_attributes' => array(
				'data-sk-products-view' => 'list',
				'aria-pressed'          => 'false',
			),
		),
	);

	$inner = skaflow_get_buttons_markup(
		$buttons,
		array(
			'wrapper_class' => 'sk-product-view-switcher__buttons',
			'default_style' => 'is-style-light',
			'layout'        => array(
				'type'           => 'flex',
				'flexWrap'       => 'nowrap',
				'justifyContent' => 'flex-end',
			),
		)
	);

	if ( '' === $inner ) {
		return '';
	}

	return $inner;
}

/**
 * Inject product-filters data attributes onto the shop archive layout group.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block.
 * @return string
 */
function skaflow_filter_shop_archive_layout_block( string $block_content, array $block ): string {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class_name = $block['attrs']['className'] ?? '';

	if ( false === strpos( $class_name, 'sk-shop-archive-layout' ) ) {
		return $block_content;
	}

	if ( ! skaflow_is_product_filter_archive() ) {
		return $block_content;
	}

	$data_attributes = sprintf(
		' data-panel-desktop="%1$s" data-panel-mobile="%2$s" data-clear-url="%3$s"',
		esc_attr( skaflow_get_filters_panel_desktop() ),
		esc_attr( skaflow_get_filters_panel_mobile() ),
		esc_url( skaflow_get_clear_catalog_filters_url() )
	);

	$updated = preg_replace(
		'/(<div[^>]*class="[^"]*\bsk-shop-archive-layout\b[^"]*")/i',
		'$1' . $data_attributes,
		$block_content,
		1
	);

	return is_string( $updated ) ? $updated : $block_content;
}

add_filter( 'render_block', 'skaflow_filter_shop_archive_layout_block', 10, 2 );

/**
 * Render active filter chips markup only.
 *
 * @return string
 */
function skaflow_render_product_filters_chips_html(): string {
	ob_start();
	get_template_part( 'partials/product-filters', 'chips' );

	return (string) ob_get_clean();
}

/**
 * Render filter panel markup only.
 *
 * @param array<int, array<string, mixed>>|null $filter_groups Optional precomputed groups.
 * @return string
 */
function skaflow_render_product_filters_panel_html( ?array $filter_groups = null ): string {
	if ( null !== $filter_groups && empty( $filter_groups ) ) {
		return '';
	}

	ob_start();
	get_template_part(
		'partials/product-filters',
		'panel',
		null !== $filter_groups
			? array(
				'filter_groups' => $filter_groups,
			)
			: array()
	);

	return (string) ob_get_clean();
}

/**
 * Build WooCommerce-style archive results count HTML.
 *
 * @param int $total  Total matching products.
 * @param int $viewing Number of products currently visible on page 1.
 * @return string
 */
function skaflow_render_product_results_count_html( int $total, int $viewing ): string {
	if ( $total <= 0 ) {
		return '';
	}

	$viewing = max( 1, min( $viewing, $total ) );

	if ( 1 === $total ) {
		$text = __( 'Showing the single result', 'skaflow' );
	} elseif ( $viewing >= $total ) {
		$text = sprintf(
			/* translators: %s: total number of products */
			__( 'Showing all %s results', 'skaflow' ),
			number_format_i18n( $total )
		);
	} else {
		$text = sprintf(
			/* translators: 1: first visible product number, 2: last visible product number, 3: total products */
			__( 'Showing %1$s&ndash;%2$s of %3$s results', 'skaflow' ),
			number_format_i18n( 1 ),
			number_format_i18n( $viewing ),
			number_format_i18n( $total )
		);
	}

	return sprintf(
		'<p class="woocommerce-result-count" aria-live="polite">%s</p>',
		wp_kses_post( $text )
	);
}

/**
 * Render one filter swatch button for colour attributes.
 *
 * @param string               $taxonomy    Attribute taxonomy.
 * @param array<string, mixed> $term Term data from filter groups.
 * @param string               $filter_name Filter query param name.
 * @return string
 */
function skaflow_render_filter_swatch_term( string $taxonomy, array $term, string $filter_name ): string {
	$term_slug  = (string) ( $term['slug'] ?? '' );
	$term_label = (string) ( $term['label'] ?? $term_slug );
	$is_chosen  = ! empty( $term['chosen'] );
	$swatch     = class_exists( '\Skaflow\Product_Swatches' )
		? \Skaflow\Product_Swatches::has_swatch( 0, $taxonomy, $term_slug )
		: array();

	$classes = array(
		'sk-swatch',
		'sk-filter-term',
		'sk-swatch--style-3',
	);

	if ( $is_chosen ) {
		$classes[] = 'is-active';
		$classes[] = 'sk-swatch--active';
	}

	$style = '';
	$image = '';

	if ( ! empty( $swatch['color'] ) ) {
		$style     = 'background-color:' . (string) $swatch['color'];
		$classes[] = 'sk-swatch--bg';
	} elseif ( ! empty( $swatch['image'] ) && class_exists( '\Skaflow\Product_Swatches' ) ) {
		$image     = \Skaflow\Product_Swatches::get_term_swatch_image_html( $swatch['image'] );
		$classes[] = 'sk-swatch--bg';
	} else {
		$classes[] = 'sk-swatch--text';
	}

	$inner = '';

	if ( $style || $image ) {
		$inner = sprintf(
			'<span class="sk-swatch__bg" style="%s">%s</span>',
			esc_attr( $style ),
			$image // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attachment HTML from WP.
		);
	} else {
		$inner = sprintf(
			'<span class="sk-swatch__text">%s</span>',
			esc_html( $term_label )
		);
	}

	return sprintf(
		'<button type="button" class="%1$s" data-filter-name="%2$s" data-term-slug="%3$s" data-toggle-url="%4$s" aria-pressed="%5$s" title="%6$s">%7$s</button>',
		esc_attr( implode( ' ', $classes ) ),
		esc_attr( $filter_name ),
		esc_attr( $term_slug ),
		esc_url( (string) ( $term['toggle_url'] ?? '' ) ),
		$is_chosen ? 'true' : 'false',
		esc_attr( $term_label ),
		$inner // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
	);
}
