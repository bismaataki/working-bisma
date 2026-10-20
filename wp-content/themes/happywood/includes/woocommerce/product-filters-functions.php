<?php
/**
 * Product archive layered-nav — filter chrome, panel, chips, and swatch markup.
 *
 * @package Happywood
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
function happywood_render_product_filters_html( ?array $filter_groups = null ): string {
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
function happywood_render_product_filters_chrome_html( ?array $filter_groups = null ): string {
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
function happywood_render_product_filters_sidebar_html( ?array $filter_groups = null ): string {
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
function happywood_render_product_view_switcher_html(): string {

	$buttons = array(
		array(
			'label'           => __( 'Grid view', 'happywood' ),
			'tag'             => 'button',
			'style'           => 'is-style-light',
			'icon'            => 'grid-2x2',
			'class'           => 'hw-product-view-switcher__button',
			'element_class'   => 'hw-product-view-switcher__control',
			'html_attributes' => array(
				'data-hw-products-view' => 'grid',
				'aria-pressed'          => 'true',
			),
		),
		array(
			'label'           => __( 'List view', 'happywood' ),
			'tag'             => 'button',
			'style'           => 'is-style-light',
			'icon'            => 'list',
			'class'           => 'hw-product-view-switcher__button',
			'element_class'   => 'hw-product-view-switcher__control',
			'html_attributes' => array(
				'data-hw-products-view' => 'list',
				'aria-pressed'          => 'false',
			),
		),
	);

	$inner = happywood_get_buttons_markup(
		$buttons,
		array(
			'wrapper_class' => 'hw-product-view-switcher__buttons',
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
function happywood_filter_shop_archive_layout_block( string $block_content, array $block ): string {
	if ( 'core/group' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$class_name = $block['attrs']['className'] ?? '';

	if ( false === strpos( $class_name, 'hw-shop-archive-layout' ) ) {
		return $block_content;
	}

	if ( ! happywood_is_product_filter_archive() ) {
		return $block_content;
	}

	$data_attributes = sprintf(
		' data-panel-desktop="%1$s" data-panel-mobile="%2$s" data-clear-url="%3$s"',
		esc_attr( happywood_get_filters_panel_desktop() ),
		esc_attr( happywood_get_filters_panel_mobile() ),
		esc_url( happywood_get_clear_catalog_filters_url() )
	);

	$updated = preg_replace(
		'/(<div[^>]*class="[^"]*\bhw-shop-archive-layout\b[^"]*")/i',
		'$1' . $data_attributes,
		$block_content,
		1
	);

	return is_string( $updated ) ? $updated : $block_content;
}

add_filter( 'render_block', 'happywood_filter_shop_archive_layout_block', 10, 2 );

/**
 * Render active filter chips markup only.
 *
 * @return string
 */
function happywood_render_product_filters_chips_html(): string {
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
function happywood_render_product_filters_panel_html( ?array $filter_groups = null ): string {
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
function happywood_render_product_results_count_html( int $total, int $viewing ): string {
	if ( $total <= 0 ) {
		return '';
	}

	$viewing = max( 1, min( $viewing, $total ) );

	if ( 1 === $total ) {
		$text = __( 'Showing the single result', 'happywood' );
	} elseif ( $viewing >= $total ) {
		$text = sprintf(
			/* translators: %s: total number of products */
			__( 'Showing all %s results', 'happywood' ),
			number_format_i18n( $total )
		);
	} else {
		$text = sprintf(
			/* translators: 1: first visible product number, 2: last visible product number, 3: total products */
			__( 'Showing %1$s&ndash;%2$s of %3$s results', 'happywood' ),
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
function happywood_render_filter_swatch_term( string $taxonomy, array $term, string $filter_name ): string {
	$term_slug  = (string) ( $term['slug'] ?? '' );
	$term_label = (string) ( $term['label'] ?? $term_slug );
	$is_chosen  = ! empty( $term['chosen'] );
	$swatch     = class_exists( '\Happywood\Product_Swatches' )
		? \Happywood\Product_Swatches::has_swatch( 0, $taxonomy, $term_slug )
		: array();

	$classes = array(
		'hw-swatch',
		'hw-filter-term',
		'hw-swatch--style-3',
	);

	if ( $is_chosen ) {
		$classes[] = 'is-active';
		$classes[] = 'hw-swatch--active';
	}

	$style = '';
	$image = '';

	if ( ! empty( $swatch['color'] ) ) {
		$style     = 'background-color:' . (string) $swatch['color'];
		$classes[] = 'hw-swatch--bg';
	} elseif ( ! empty( $swatch['image'] ) && class_exists( '\Happywood\Product_Swatches' ) ) {
		$image     = \Happywood\Product_Swatches::get_term_swatch_image_html( $swatch['image'] );
		$classes[] = 'hw-swatch--bg';
	} else {
		$classes[] = 'hw-swatch--text';
	}

	$inner = '';

	if ( $style || $image ) {
		$inner = sprintf(
			'<span class="hw-swatch__bg" style="%s">%s</span>',
			esc_attr( $style ),
			$image // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attachment HTML from WP.
		);
	} else {
		$inner = sprintf(
			'<span class="hw-swatch__text">%s</span>',
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
