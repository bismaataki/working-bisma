<?php
/**
 * Example Primary + Utility nav trees.
 *
 * This file is the **project-specific half** of the menu seeder. The helpers in
 * `menu-setup-helpers.php` are generic and need no editing; this file is meant to
 * be rewritten per project.
 *
 * The tree below is a deliberately small worked example that exercises every
 * mega-menu shape the theme supports. It will not produce a usable menu as-is:
 * `SKAFLOW_MENU_SETUP_TERMS` holds placeholder term IDs. Replace them with real
 * `product_cat` IDs for the project, or the seeder logs a skip for each one.
 *
 * Find the real IDs with:
 *   ddev wp term list product_cat --fields=term_id,name,slug --format=table
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Placeholder term IDs, keyed by the label they carry in the example tree.
 *
 * Swap the integers for real `product_cat` term IDs. Keys are arbitrary labels
 * used only by this file, so rename them freely to match the project's catalogue.
 */
const SKAFLOW_MENU_SETUP_TERMS = array(
	// Top-level mega menu parents.
	'category_a'    => 0,
	'category_b'    => 0,
	'category_c'    => 0,

	// Children of category_a, grouped under headings.
	'a_child_one'   => 0,
	'a_child_two'   => 0,
	'a_child_three' => 0,
	'a_child_four'  => 0,

	// Children of category_b, flat grid with no headings.
	'b_child_one'   => 0,
	'b_child_two'   => 0,
	'b_child_three' => 0,

	// Single child of category_c, plain text link.
	'c_child_one'   => 0,

	// Right-aligned direct links.
	'new_arrivals'  => 0,
	'sale'          => 0,
);

/**
 * Resolve a placeholder key to a term ID, or 0 when it has not been filled in.
 *
 * @param string $key Key in SKAFLOW_MENU_SETUP_TERMS.
 *
 * @return int Term ID, or 0 when unset.
 */
function skaflow_menu_setup_term( string $key ): int {
	return (int) ( SKAFLOW_MENU_SETUP_TERMS[ $key ] ?? 0 );
}

/**
 * Filter a [ key, label ] list down to entries with a real term ID.
 *
 * @param array<int, array{0: string, 1: string}> $items Key/label pairs.
 *
 * @return array<int, array{0: int, 1: string}> Term ID/label pairs.
 */
function skaflow_menu_setup_resolve_items( array $items ): array {
	$resolved = array();

	foreach ( $items as $item ) {
		$term_id = skaflow_menu_setup_term( $item[0] );

		if ( $term_id > 0 ) {
			$resolved[] = array( $term_id, $item[1] );
			continue;
		}

		skaflow_menu_setup_log( sprintf( 'Skipped "%s" — no term ID set for %s.', $item[1], $item[0] ) );
	}

	return $resolved;
}

/**
 * Populate Primary Nav and Utility Nav.
 *
 * @param int $primary_menu_id Nav menu term ID for the primary location.
 * @param int $utility_menu_id Nav menu term ID for the utility location.
 */
function skaflow_menu_setup_build_menus( int $primary_menu_id, int $utility_menu_id ): void {

	// -------------------------------------------------------------------------
	// Pattern A — grouped thumbnails under text headings.
	//
	// `column_span` and `child_columns` let one heading occupy two grid columns
	// and lay its own children out in two, which is how a long list stays
	// readable inside a four-column panel.
	// -------------------------------------------------------------------------
	$category_a = skaflow_menu_setup_term( 'category_a' );

	if ( $category_a > 0 ) {
		skaflow_menu_setup_build_top_level_term(
			$primary_menu_id,
			$category_a,
			'Category A',
			array(
				'grid_columns' => '4',
				'label_mobile' => 'Explore Category A',
			),
			function ( int $menu_id, int $parent_id ): void {
				$group_id = skaflow_menu_setup_add_heading(
					$menu_id,
					$parent_id,
					'BY TYPE',
					array(
						'column_span'   => '2',
						'child_columns' => '2',
					)
				);

				skaflow_menu_setup_add_thumbnail_items(
					$menu_id,
					$group_id,
					skaflow_menu_setup_resolve_items(
						array(
							array( 'a_child_one', 'Child One' ),
							array( 'a_child_two', 'Child Two' ),
						)
					)
				);

				// A heading with no field overrides takes one column.
				$second_id = skaflow_menu_setup_add_heading( $menu_id, $parent_id, 'BY MATERIAL' );

				skaflow_menu_setup_add_thumbnail_items(
					$menu_id,
					$second_id,
					skaflow_menu_setup_resolve_items(
						array(
							array( 'a_child_three', 'Child Three' ),
							array( 'a_child_four', 'Child Four' ),
						)
					)
				);
			}
		);
	}

	// -------------------------------------------------------------------------
	// Pattern B — flat grid, no headings. Use when the child list is short
	// enough not to need grouping.
	// -------------------------------------------------------------------------
	$category_b = skaflow_menu_setup_term( 'category_b' );

	if ( $category_b > 0 ) {
		skaflow_menu_setup_build_top_level_term(
			$primary_menu_id,
			$category_b,
			'Category B',
			array(
				'grid_columns' => '3',
				'label_mobile' => 'Explore Category B',
			),
			function ( int $menu_id, int $parent_id ): void {
				skaflow_menu_setup_add_thumbnail_items(
					$menu_id,
					$parent_id,
					skaflow_menu_setup_resolve_items(
						array(
							array( 'b_child_one', 'Child One' ),
							array( 'b_child_two', 'Child Two' ),
							array( 'b_child_three', 'Child Three' ),
						)
					)
				);
			}
		);
	}

	// -------------------------------------------------------------------------
	// Pattern C — heading with plain text children instead of thumbnails.
	// -------------------------------------------------------------------------
	$category_c = skaflow_menu_setup_term( 'category_c' );
	$c_child    = skaflow_menu_setup_term( 'c_child_one' );

	if ( $category_c > 0 ) {
		skaflow_menu_setup_build_top_level_term(
			$primary_menu_id,
			$category_c,
			'Category C',
			array(
				'grid_columns' => '4',
				'label_mobile' => 'Explore Category C',
			),
			function ( int $menu_id, int $parent_id ) use ( $c_child ): void {
				$group_id = skaflow_menu_setup_add_heading(
					$menu_id,
					$parent_id,
					'SUBCATEGORIES',
					array(
						'column_span'   => '2',
						'child_columns' => '2',
					)
				);

				if ( $c_child > 0 ) {
					skaflow_menu_setup_add_term( $menu_id, $group_id, $c_child, 'Child One' );
				}
			}
		);
	}

	// -------------------------------------------------------------------------
	// Right-aligned direct links. `link_type` picks the render treatment:
	// `default` is a plain link, `highlight-link` gets the accent style.
	// -------------------------------------------------------------------------
	$direct_links = array(
		array( 'new_arrivals', 'New Arrivals', 'default' ),
		array( 'sale', 'Sale', 'highlight-link' ),
	);

	foreach ( $direct_links as $link ) {
		[ $key, $label, $link_type ] = $link;

		$term_id = skaflow_menu_setup_term( $key );

		if ( 0 === $term_id ) {
			skaflow_menu_setup_log( sprintf( 'Skipped "%s" — no term ID set for %s.', $label, $key ) );
			continue;
		}

		$item_id = skaflow_menu_setup_find_top_level_term( $primary_menu_id, $term_id );

		if ( ! $item_id ) {
			$item_id = skaflow_menu_setup_add_term( $primary_menu_id, 0, $term_id, $label, array() );
		}

		skaflow_menu_setup_set_fields(
			$item_id,
			array(
				'link_type' => $link_type,
				'nav_align' => 'right',
			)
		);
	}

	// -------------------------------------------------------------------------
	// Utility Nav — rebuilt from scratch each run, so it is safe to re-seed.
	// `utility_icon` takes a Lucide slug from the curated set; see
	// .cursor/rules/project-lucide-icons.mdc for the list.
	// -------------------------------------------------------------------------
	$utility_items = wp_get_nav_menu_items( $utility_menu_id, array( 'post_status' => 'any' ) );

	if ( is_array( $utility_items ) ) {
		foreach ( $utility_items as $utility_item ) {
			wp_delete_post( (int) $utility_item->ID, true );
		}
	}

	$utility_links = array(
		array( 'Showrooms', home_url( '/showrooms/' ), 'map-pin' ),
		array( 'Account', home_url( '/my-account/' ), 'user' ),
	);

	foreach ( $utility_links as $utility_link ) {
		[ $label, $url, $icon ] = $utility_link;

		skaflow_menu_setup_add_custom(
			$utility_menu_id,
			0,
			$label,
			$url,
			array(
				'link_type'    => 'utility-link',
				'utility_icon' => $icon,
				'label_mobile' => $label,
			)
		);
	}
}

/**
 * Resolve menus by theme location, then build the trees.
 *
 * @return array{primary_menu_id: int, utility_menu_id: int}
 */
function skaflow_menu_setup_run(): array {
	$locations = skaflow_menu_setup_locations();

	$primary_menu_id = skaflow_menu_setup_get_or_create_for_location( $locations['primary'], 'Primary Nav' );
	$utility_menu_id = skaflow_menu_setup_get_or_create_for_location( $locations['utility'], 'Top Bar Utility Nav' );

	skaflow_menu_setup_build_menus( $primary_menu_id, $utility_menu_id );

	return array(
		'primary_menu_id' => $primary_menu_id,
		'utility_menu_id' => $utility_menu_id,
	);
}
