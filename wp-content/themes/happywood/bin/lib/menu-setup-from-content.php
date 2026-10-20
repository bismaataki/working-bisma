<?php
/**
 * Build a Primary Nav tree from content that already exists in the database.
 *
 * The alternative to `menu-setup-tree.php`, which is a tree you author by hand
 * from a design. This one reads the catalogue and produces a mega menu that is
 * correct on the day it runs, with no placeholder term IDs to fill in.
 *
 * Use it when the site has content but no agreed navigation design yet. Prefer
 * the hand-authored tree once a design exists: grouping, heading labels, and
 * column spans are editorial decisions this file can only approximate.
 *
 * What it produces:
 *
 * - One top-level item per top-level product category that has products
 * - Its child categories beneath it as thumbnail items, grouped under a single
 *   heading, because there is no way to infer an editorial grouping
 * - A flat item when a category has no children
 *
 * Everything it creates is an ordinary menu item, so refining the result in
 * Appearance > Menus is the expected next step, not a workaround.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report what could be derived, without changing anything.
 *
 * Run this before building so the choice between derived and hand-authored is
 * made against real numbers.
 *
 * @return array<string, mixed>
 */
function happywood_menu_setup_survey_content(): array {
	$survey = array(
		'product_cats_top'  => 0,
		'product_cats_kids' => 0,
		'products'          => 0,
		'pages'             => 0,
		'posts'             => 0,
	);

	if ( taxonomy_exists( 'product_cat' ) ) {
		$top = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => 0,
				'hide_empty' => true,
				'fields'     => 'ids',
				'exclude'    => happywood_menu_setup_excluded_product_cats(),
			)
		);

		if ( ! is_wp_error( $top ) ) {
			$survey['product_cats_top'] = count( $top );

			foreach ( $top as $term_id ) {
				$kids = get_terms(
					array(
						'taxonomy'   => 'product_cat',
						'parent'     => (int) $term_id,
						'hide_empty' => true,
						'fields'     => 'ids',
					)
				);

				if ( ! is_wp_error( $kids ) ) {
					$survey['product_cats_kids'] += count( $kids );
				}
			}
		}

		$survey['products'] = (int) wp_count_posts( 'product' )->publish;
	}

	$survey['pages'] = (int) wp_count_posts( 'page' )->publish;
	$survey['posts'] = (int) wp_count_posts( 'post' )->publish;

	return $survey;
}

/**
 * Product categories never worth putting in a nav.
 *
 * @return int[]
 */
function happywood_menu_setup_excluded_product_cats(): array {
	return array_values( array_filter( array( (int) get_option( 'default_product_cat', 0 ) ) ) );
}

/**
 * Build Primary Nav from existing product categories.
 *
 * @param int $primary_menu_id Nav menu term ID.
 * @param int $max_top_level   Maximum top-level items.
 *
 * @return int Number of top-level items created.
 */
function happywood_menu_setup_build_primary_from_content( int $primary_menu_id, int $max_top_level = 8 ): int {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		happywood_menu_setup_log( 'product_cat does not exist. Nothing to derive from.' );

		return 0;
	}

	$top_terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
			'order'      => 'ASC',
			'number'     => max( 1, $max_top_level ),
			'exclude'    => happywood_menu_setup_excluded_product_cats(),
		)
	);

	if ( is_wp_error( $top_terms ) || empty( $top_terms ) ) {
		happywood_menu_setup_log( 'No top-level product categories with products. Nothing to derive.' );

		return 0;
	}

	$created = 0;

	foreach ( $top_terms as $term ) {
		$children = happywood_get_product_cat_child_terms( (int) $term->term_id );

		// A category with no children is a plain link, not a mega menu panel.
		if ( empty( $children ) ) {
			$existing = happywood_menu_setup_find_top_level_term( $primary_menu_id, (int) $term->term_id );

			if ( ! $existing ) {
				$existing = happywood_menu_setup_add_term(
					$primary_menu_id,
					0,
					(int) $term->term_id,
					$term->name,
					array()
				);
			}

			happywood_menu_setup_set_fields( $existing, array( 'link_type' => 'default' ) );
			++$created;

			continue;
		}

		// Asked before building, because the builder returns the same term ID
		// whether it created the item or skipped one that was already there.
		// Counting unconditionally made a reseed report every existing item as
		// freshly derived.
		$already_present = (bool) happywood_menu_setup_find_top_level_term( $primary_menu_id, (int) $term->term_id );

		happywood_menu_setup_build_top_level_term(
			$primary_menu_id,
			(int) $term->term_id,
			$term->name,
			array(
				'grid_columns' => '4',
				/* translators: %s: product category name. */
				'label_mobile' => sprintf( __( 'Explore %s', 'happywood' ), $term->name ),
			),
			function ( int $menu_id, int $parent_id ) use ( $term, $children ): void {
				// One heading, because nothing in the taxonomy says how an editor
				// would group these. Split it by hand once the design exists.
				$heading_id = happywood_menu_setup_add_heading(
					$menu_id,
					$parent_id,
					strtoupper( $term->name ),
					array(
						'column_span'   => '2',
						'child_columns' => '2',
					)
				);

				$items = array();

				foreach ( $children as $child ) {
					$items[] = array( (int) $child->term_id, $child->name );
				}

				happywood_menu_setup_add_thumbnail_items( $menu_id, $heading_id, $items );
			}
		);

		if ( ! $already_present ) {
			++$created;
		}
	}

	return $created;
}

/**
 * Resolve menus by location, then derive Primary Nav from content.
 *
 * Utility Nav is still built from the hand-authored tree, because its items are
 * fixed destinations rather than catalogue entries.
 *
 * @param int $max_top_level Maximum top-level items.
 *
 * @return array{primary_menu_id: int, utility_menu_id: int, created: int}
 */
function happywood_menu_setup_run_from_content( int $max_top_level = 8 ): array {
	$locations = happywood_menu_setup_locations();

	$primary_menu_id = happywood_menu_setup_get_or_create_for_location( $locations['primary'], 'Primary Nav' );
	$utility_menu_id = happywood_menu_setup_get_or_create_for_location( $locations['utility'], 'Top Bar Utility Nav' );

	$created = happywood_menu_setup_build_primary_from_content( $primary_menu_id, $max_top_level );

	return array(
		'primary_menu_id' => $primary_menu_id,
		'utility_menu_id' => $utility_menu_id,
		'created'         => $created,
	);
}
