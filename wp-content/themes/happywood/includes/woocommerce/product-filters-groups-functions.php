<?php
/**
 * Product archive layered-nav — filter groups, price bounds, and chips data.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lucide icon slug for a catalog filter group (attribute slug or `price`).
 *
 * Maps WooCommerce attribute slugs to the theme's curated Lucide set — no ACF.
 * Unknown slugs fall back to `filter`.
 *
 * @param string $slug Attribute slug e.g. `material`, `colour`, or `price`.
 * @return string Lucide slug registered in happywood_get_lucide_icon_choices().
 */
function happywood_get_filter_group_icon_slug( string $slug ): string {

	$slug = sanitize_title( $slug );

	$map = array(
		'price'          => 'tag',
		'material'       => 'package',
		'colour'         => 'eye',
		'color'          => 'eye',
		'arms'           => 'sliders-horizontal',
		'stackable'      => 'package',
		'assembly'       => 'check-circle',
		'indoor-outdoor' => 'map-pin',
		'folding'        => 'menu',
		'height'         => 'sliders-horizontal',
		'shape'          => 'grid-2x2',
		'size'           => 'sliders-horizontal',
		'seat'           => 'user',
		'type'           => 'file-text',
		'features'       => 'star',
		'backrest'       => 'shield-check',
		'base-type'      => 'truck',
		'brand'          => 'tag',
		'mounting'       => 'map-pin',
		'feet'           => 'truck',
		'castors'        => 'truck',
		'feet-castors'   => 'truck',
	);

	$icon = $map[ $slug ] ?? 'filter';

	if ( function_exists( 'happywood_sanitize_lucide_icon_slug' ) ) {
		$icon = happywood_sanitize_lucide_icon_slug( $icon );
	}

	return '' !== $icon ? $icon : 'filter';
}

/**
 * Number of active selections within one filter group (bar button context).
 *
 * Price counts as 1 when min/max is set; attributes count chosen terms.
 *
 * @param array<string, mixed> $group Filter group from happywood_get_archive_filter_groups().
 * @return int
 */
function happywood_get_filter_group_applied_count( array $group ): int {

	if ( 'price' === ( $group['type'] ?? '' ) ) {
		$params = happywood_parse_catalog_filter_params();

		return ( ! empty( $params['min_price'] ) || ! empty( $params['max_price'] ) ) ? 1 : 0;
	}

	$count = 0;

	foreach ( (array) ( $group['terms'] ?? array() ) as $term ) {
		if ( ! empty( $term['chosen'] ) ) {
			++$count;
		}
	}

	return $count;
}

/**
 * Visible attribute filter groups for the current product archive.
 *
 * @return array<int, array<string, mixed>>
 */
function happywood_get_archive_filter_groups(): array {
	if ( ! happywood_is_product_filter_archive() || ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}

	if ( ! class_exists( 'WC_Query' ) ) {
		return array();
	}

	$groups     = array();
	$chosen     = \WC_Query::get_layered_nav_chosen_attributes();
	$attributes = wc_get_attribute_taxonomies();

	if ( empty( $attributes ) ) {
		return happywood_maybe_prepend_price_filter_group( $groups );
	}

	foreach ( $attributes as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );

		if ( ! taxonomy_exists( $taxonomy ) ) {
			continue;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'orderby'    => 'name',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			continue;
		}

		$term_ids = wp_list_pluck( $terms, 'term_id' );
		$counts   = happywood_get_filtered_term_product_counts( $term_ids, $taxonomy );
		$options  = array();

		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}

			$count = isset( $counts[ $term->term_id ] ) ? (int) $counts[ $term->term_id ] : 0;

			if ( $count <= 0 ) {
				continue;
			}

			$chosen_terms = $chosen[ $taxonomy ]['terms'] ?? array();
			$is_chosen    = in_array( $term->slug, $chosen_terms, true );

			$options[] = array(
				'term_id'    => (int) $term->term_id,
				'slug'       => $term->slug,
				'label'      => $term->name,
				'count'      => $count,
				'chosen'     => $is_chosen,
				'toggle_url' => happywood_build_layered_nav_toggle_url( $taxonomy, $term->slug ),
			);
		}

		if ( empty( $options ) ) {
			continue;
		}

		$attribute_slug = wc_attribute_taxonomy_slug( $taxonomy );

		$groups[] = array(
			'slug'       => $attribute_slug,
			'label'      => wc_attribute_label( $taxonomy ),
			'taxonomy'   => $taxonomy,
			'type'       => 'attribute',
			'query_type' => happywood_get_layered_nav_query_type( $taxonomy ),
			'terms'      => $options,
		);
	}

	return happywood_maybe_prepend_price_filter_group( $groups );
}

/**
 * Min/max catalog price bounds for the current archive context.
 *
 * @return array{min: float, max: float}
 */
function happywood_get_catalog_price_bounds(): array {
	$min = 0.0;
	$max = (float) apply_filters( 'happywood_catalog_price_filter_max', 10000 );

	if ( class_exists( '\WC_Widget_Price_Filter' ) && is_callable( array( '\WC_Widget_Price_Filter', 'get_filtered_price' ) ) ) {
		$prices = \WC_Widget_Price_Filter::get_filtered_price();

		if ( is_object( $prices ) ) {
			if ( isset( $prices->min_price ) ) {
				$min = floor( (float) $prices->min_price );
			}

			if ( isset( $prices->max_price ) && (float) $prices->max_price > 0 ) {
				$max = ceil( (float) $prices->max_price );
			}
		}
	}

	if ( $max <= $min ) {
		$max = max( $min + 1, (float) apply_filters( 'happywood_catalog_price_filter_max', 10000 ) );
	}

	return array(
		'min' => $min,
		'max' => $max,
	);
}

/**
 * @param array<int, array<string, mixed>> $groups Filter groups.
 * @return array<int, array<string, mixed>>
 */
function happywood_maybe_prepend_price_filter_group( array $groups ): array {
	if ( ! happywood_is_product_filter_archive() ) {
		return $groups;
	}

	$params = happywood_parse_catalog_filter_params();
	$min    = isset( $params['min_price'] ) ? (float) $params['min_price'] : 0.0;
	$max    = isset( $params['max_price'] ) ? (float) $params['max_price'] : 0.0;
	$bounds = happywood_get_catalog_price_bounds();

	array_unshift(
		$groups,
		array(
			'slug'      => 'price',
			'label'     => __( 'Filter by price', 'happywood' ),
			'taxonomy'  => '',
			'type'      => 'price',
			'min'       => $min,
			'max'       => $max,
			'min_bound' => $bounds['min'],
			'max_bound' => $bounds['max'],
		)
	);

	return $groups;
}

/**
 * Active filter chips for the current catalog query (attributes + price).
 *
 * @return array<int, array<string, mixed>>
 */
function happywood_get_active_filter_chips(): array {
	if ( ! function_exists( 'WC' ) || ! class_exists( 'WC_Query' ) ) {
		return array();
	}

	$chips  = array();
	$chosen = \WC_Query::get_layered_nav_chosen_attributes();
	$params = happywood_parse_catalog_filter_params();

	foreach ( $chosen as $taxonomy => $data ) {
		if ( empty( $data['terms'] ) || ! is_array( $data['terms'] ) ) {
			continue;
		}

		$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );

		foreach ( $data['terms'] as $term_slug ) {
			$term = get_term_by( 'slug', $term_slug, $taxonomy );

			if ( ! $term instanceof \WP_Term ) {
				continue;
			}

			$remaining = array_values(
				array_diff(
					$data['terms'],
					array( $term_slug )
				)
			);

			$chips[] = array(
				'key'         => $filter_name . ':' . $term_slug,
				'label'       => $term->name,
				'remove_url'  => happywood_build_catalog_filter_url(
					array(
						$filter_name => empty( $remaining ) ? null : implode( ',', $remaining ),
					)
				),
				'filter_name' => $filter_name,
				'term_slug'   => $term_slug,
			);
		}
	}

	if ( ! empty( $params['min_price'] ) || ! empty( $params['max_price'] ) ) {
		$min_label = ! empty( $params['min_price'] ) ? wc_price( (float) $params['min_price'] ) : '';
		$max_label = ! empty( $params['max_price'] ) ? wc_price( (float) $params['max_price'] ) : '';

		$label = trim(
			wp_strip_all_tags(
				sprintf(
					/* translators: 1: min price, 2: max price */
					__( '%1$s – %2$s', 'happywood' ),
					$min_label ? $min_label : '…',
					$max_label ? $max_label : '…'
				)
			)
		);

		$chips[] = array(
			'key'         => 'price',
			'label'       => $label,
			'remove_url'  => happywood_build_catalog_filter_url(
				array(
					'min_price' => null,
					'max_price' => null,
				)
			),
			'filter_name' => 'price',
		);
	}

	return $chips;
}

/**
 * Whether an attribute group should render as a colour swatch grid.
 *
 * @param string $attribute_slug Attribute slug e.g. `colour`.
 * @return bool
 */
function happywood_is_colour_filter_attribute( string $attribute_slug ): bool {
	return in_array( $attribute_slug, array( 'colour', 'color' ), true );
}
