<?php
/**
 * Shared helpers for programmatic Primary Nav menu builds.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Log a line from menu setup (WP-CLI when present, otherwise no-op).
 *
 * @param string $message Log line.
 */
function happywood_menu_setup_log( string $message ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
		WP_CLI::log( $message );
	}
}

/**
 * Abort menu setup.
 *
 * @param string $message Error text.
 */
function happywood_menu_setup_fail( string $message ): void {
	if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
		WP_CLI::error( $message );
	}

	throw new RuntimeException( esc_html( $message ) );
}

/**
 * Theme location slugs for the two nav menus.
 *
 * Resolved through defined() so these scripts still work when run against a
 * bootstrap that has not loaded includes/constants.php.
 *
 * @return array{primary: string, utility: string}
 */
function happywood_menu_setup_locations(): array {
	return array(
		'primary' => defined( 'HAPPYWOOD_MENU_PRIMARY' ) ? HAPPYWOOD_MENU_PRIMARY : 'happywood-primary-nav',
		'utility' => defined( 'HAPPYWOOD_MENU_UTILITY' ) ? HAPPYWOOD_MENU_UTILITY : 'happywood-utility-nav',
	);
}

/**
 * Resolve the menu assigned to a theme location, creating nothing.
 *
 * The read-only counterpart to happywood_menu_setup_get_or_create_for_location().
 * Reporting tools need this instead: a dump or a backup that creates a menu as
 * a side effect reports on the empty thing it just made while the real menu
 * sits untouched, which reads as "no items" rather than as a failure.
 *
 * @param string $location Theme location slug (e.g. happywood-primary-nav).
 * @return int Nav menu term ID, or 0 when the location has no menu assigned.
 */
function happywood_menu_setup_find_for_location( string $location ): int {
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;

	if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
		return 0;
	}

	return $menu_id;
}

/**
 * Find or create a classic menu and assign it to a theme location.
 *
 * Location assignment is the source of truth — never a baked-in term ID.
 *
 * @param string $location Theme location slug (e.g. happywood-primary-nav).
 * @param string $name     Menu name used when creating or finding an unassigned menu.
 * @return int Nav menu term ID.
 */
function happywood_menu_setup_get_or_create_for_location( string $location, string $name ): int {
	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;

	if ( $menu_id ) {
		$assigned = wp_get_nav_menu_object( $menu_id );

		if ( $assigned ) {
			happywood_menu_setup_log(
				sprintf( 'Using %s menu "%s" (ID %d) from location %s.', $name, $assigned->name, $menu_id, $location )
			);

			return $menu_id;
		}
	}

	$existing = wp_get_nav_menu_object( $name );

	if ( $existing ) {
		$menu_id = (int) $existing->term_id;
		happywood_menu_setup_log( sprintf( 'Found existing menu "%s" (ID %d); assigning to %s.', $name, $menu_id, $location ) );
	} else {
		$created = wp_create_nav_menu( $name );

		if ( is_wp_error( $created ) ) {
			happywood_menu_setup_fail( $created->get_error_message() );
		}

		$menu_id = (int) $created;
		happywood_menu_setup_log( sprintf( 'Created menu "%s" (ID %d); assigning to %s.', $name, $menu_id, $location ) );
	}

	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	return $menu_id;
}

/**
 * Ensure a nav menu item belongs to the target menu term.
 *
 * @param int $item_id Menu item post ID.
 * @param int $menu_id Nav menu term ID.
 */
function happywood_menu_setup_assign_to_menu( int $item_id, int $menu_id ): void {
	wp_set_object_terms( $item_id, $menu_id, 'nav_menu' );
}

/**
 * Set ACF fields on a nav menu item.
 *
 * @param int                  $item_id Menu item post ID.
 * @param array<string, mixed> $fields  Field name => value.
 */
function happywood_menu_setup_set_fields( int $item_id, array $fields ): void {
	if ( ! function_exists( 'update_field' ) ) {
		return;
	}

	foreach ( $fields as $name => $value ) {
		update_field( $name, $value, $item_id );
	}
}

/**
 * Add a product_cat term to the menu.
 *
 * @param int                  $menu_id   Nav menu term ID.
 * @param int                  $parent_id Parent menu item ID.
 * @param int                  $term_id   Taxonomy term ID.
 * @param string               $title     Menu item title.
 * @param array<string, mixed> $fields    Extra ACF fields.
 * @param string               $taxonomy  Taxonomy slug (default product_cat).
 */
function happywood_menu_setup_add_term(
	int $menu_id,
	int $parent_id,
	int $term_id,
	string $title,
	array $fields = array(),
	string $taxonomy = 'product_cat'
): int {

	$item_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-object-id' => $term_id,
			'menu-item-object'    => $taxonomy,
			'menu-item-type'      => 'taxonomy',
			'menu-item-status'    => 'publish',
			'menu-item-title'     => $title,
			'menu-item-parent-id' => $parent_id,
		)
	);

	if ( is_wp_error( $item_id ) ) {
		happywood_menu_setup_fail( $item_id->get_error_message() );
	}

	$item_id = (int) $item_id;
	wp_update_post(
		array(
			'ID'         => $item_id,
			'post_title' => $title,
		)
	);
	update_post_meta( $item_id, '_menu_item_title', $title );
	happywood_menu_setup_assign_to_menu( $item_id, $menu_id );

	happywood_menu_setup_set_fields(
		$item_id,
		array_merge(
			array(
				'link_type' => 'thumbnail-link',
			),
			$fields
		)
	);

	return $item_id;
}

/**
 * Add a post object to the menu.
 *
 * A real post_type item, not a custom link. A custom link would bake today's
 * permalink into the menu, so the link would break silently the next time
 * somebody edited the slug, and the item would carry no post association for
 * happywood_menu_get_thumbnail_id() to resolve a featured image from.
 *
 * @param int                  $menu_id   Nav menu term ID.
 * @param int                  $parent_id Parent menu item ID.
 * @param int                  $post_id   Post ID to link to.
 * @param string               $title     Menu item title.
 * @param array<string, mixed> $fields    Extra ACF fields.
 * @param string               $post_type Post type slug (default post).
 */
function happywood_menu_setup_add_post(
	int $menu_id,
	int $parent_id,
	int $post_id,
	string $title,
	array $fields = array(),
	string $post_type = 'post'
): int {

	$item_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-object-id' => $post_id,
			'menu-item-object'    => $post_type,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-title'     => $title,
			'menu-item-parent-id' => $parent_id,
		)
	);

	if ( is_wp_error( $item_id ) ) {
		happywood_menu_setup_fail( $item_id->get_error_message() );
	}

	$item_id = (int) $item_id;
	wp_update_post(
		array(
			'ID'         => $item_id,
			'post_title' => $title,
		)
	);
	update_post_meta( $item_id, '_menu_item_title', $title );
	happywood_menu_setup_assign_to_menu( $item_id, $menu_id );

	happywood_menu_setup_set_fields(
		$item_id,
		array_merge(
			array(
				'link_type' => 'thumbnail-link',
			),
			$fields
		)
	);

	return $item_id;
}

/**
 * Add a custom URL menu item.
 *
 * @param int                  $menu_id   Nav menu term ID.
 * @param int                  $parent_id Parent menu item ID.
 * @param string               $title     Menu item title.
 * @param string               $url       Item URL.
 * @param array<string, mixed> $fields    Extra ACF fields.
 */
function happywood_menu_setup_add_custom(
	int $menu_id,
	int $parent_id,
	string $title,
	string $url,
	array $fields = array()
): int {

	$item_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-type'      => 'custom',
			'menu-item-url'       => $url,
			'menu-item-title'     => $title,
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent_id,
		)
	);

	if ( is_wp_error( $item_id ) ) {
		happywood_menu_setup_fail( $item_id->get_error_message() );
	}

	$item_id = (int) $item_id;
	happywood_menu_setup_assign_to_menu( $item_id, $menu_id );
	happywood_menu_setup_set_fields( $item_id, $fields );

	return $item_id;
}

/**
 * Add a section heading to the menu.
 *
 * @param int                  $menu_id   Nav menu term ID.
 * @param int                  $parent_id Parent menu item ID.
 * @param string               $title     Heading label.
 * @param array<string, mixed> $fields    Extra ACF fields.
 */
function happywood_menu_setup_add_heading(
	int $menu_id,
	int $parent_id,
	string $title,
	array $fields = array()
): int {

	$item_id = wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-type'      => 'custom',
			'menu-item-url'       => '#',
			'menu-item-title'     => $title,
			'menu-item-status'    => 'publish',
			'menu-item-parent-id' => $parent_id,
		)
	);

	if ( is_wp_error( $item_id ) ) {
		happywood_menu_setup_fail( $item_id->get_error_message() );
	}

	$item_id = (int) $item_id;
	happywood_menu_setup_assign_to_menu( $item_id, $menu_id );

	happywood_menu_setup_set_fields(
		$item_id,
		array_merge(
			array(
				'link_type' => 'heading',
			),
			$fields
		)
	);

	return $item_id;
}

/**
 * Find a top-level primary nav item linked to a taxonomy term.
 *
 * Matching is on the term, not the title, so renaming the item in wp-admin
 * does not make the next seeder run create a duplicate alongside it.
 *
 * @param int    $menu_id  Nav menu term ID.
 * @param int    $term_id  Taxonomy term ID.
 * @param string $taxonomy Taxonomy slug (default product_cat).
 */
function happywood_menu_setup_find_top_level_term( int $menu_id, int $term_id, string $taxonomy = 'product_cat' ): int {

	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) ) {
		return 0;
	}

	foreach ( $items as $item ) {
		if (
			0 === (int) $item->menu_item_parent
			&& 'taxonomy' === $item->type
			&& $taxonomy === $item->object
			&& $term_id === (int) $item->object_id
		) {
			return (int) $item->ID;
		}
	}

	return 0;
}

/**
 * Find a top-level primary nav item by exact title.
 *
 * @param int    $menu_id Nav menu term ID.
 * @param string $title   Menu item title.
 */
function happywood_menu_setup_find_top_level_title( int $menu_id, string $title ): int {

	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) ) {
		return 0;
	}

	foreach ( $items as $item ) {
		if ( 0 === (int) $item->menu_item_parent && $title === $item->title ) {
			return (int) $item->ID;
		}
	}

	return 0;
}

/**
 * Whether a menu item already has at least one child.
 *
 * @param int $menu_id Nav menu term ID.
 * @param int $item_id Parent menu item ID.
 */
function happywood_menu_setup_has_children( int $menu_id, int $item_id ): bool {

	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) ) {
		return false;
	}

	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === $item_id ) {
			return true;
		}
	}

	return false;
}

/**
 * Delete a menu item and all of its descendants.
 *
 * @param int $menu_id Nav menu term ID.
 * @param int $item_id Root menu item post ID.
 */
function happywood_menu_setup_delete_item_subtree( int $menu_id, int $item_id ): void {

	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) ) {
		return;
	}

	$children_by_parent = array();

	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;

		if ( ! isset( $children_by_parent[ $parent ] ) ) {
			$children_by_parent[ $parent ] = array();
		}

		$children_by_parent[ $parent ][] = (int) $item->ID;
	}

	$delete_recursive = static function ( int $id ) use ( &$delete_recursive, $children_by_parent ): void {
		foreach ( $children_by_parent[ $id ] ?? array() as $child_id ) {
			$delete_recursive( $child_id );
		}

		wp_delete_post( $id, true );
	};

	$delete_recursive( $item_id );
}

/**
 * Delete direct children of a menu item (keep the parent).
 *
 * @param int $menu_id   Nav menu term ID.
 * @param int $parent_id Parent menu item post ID.
 */
function happywood_menu_setup_delete_children( int $menu_id, int $parent_id ): void {

	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) ) {
		return;
	}

	foreach ( $items as $item ) {
		if ( (int) $item->menu_item_parent === $parent_id ) {
			happywood_menu_setup_delete_item_subtree( $menu_id, (int) $item->ID );
		}
	}
}

/**
 * Create or reuse a top-level taxonomy menu item and run a child builder.
 *
 * @param int                  $menu_id        Nav menu term ID.
 * @param int                  $term_id        Taxonomy term ID.
 * @param string               $title          Menu item title.
 * @param array<string, mixed> $fields         ACF fields for the top-level item.
 * @param callable             $build_children Callback receiving ( menu_id, parent_id ).
 * @param string               $taxonomy       Taxonomy slug (default product_cat).
 */
function happywood_menu_setup_build_top_level_term(
	int $menu_id,
	int $term_id,
	string $title,
	array $fields,
	callable $build_children,
	string $taxonomy = 'product_cat'
): int {

	$parent_id = happywood_menu_setup_find_top_level_term( $menu_id, $term_id, $taxonomy );

	if ( $parent_id && happywood_menu_setup_has_children( $menu_id, $parent_id ) ) {
		happywood_menu_setup_log( sprintf( 'Skipping %s — already built (ID %d).', $title, $parent_id ) );
		return $parent_id;
	}

	if ( ! $parent_id ) {
		$parent_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-object-id' => $term_id,
				'menu-item-object'    => $taxonomy,
				'menu-item-type'      => 'taxonomy',
				'menu-item-status'    => 'publish',
				'menu-item-title'     => $title,
				'menu-item-parent-id' => 0,
			)
		);

		if ( is_wp_error( $parent_id ) ) {
			happywood_menu_setup_fail( $parent_id->get_error_message() );
		}

		$parent_id = (int) $parent_id;
		happywood_menu_setup_assign_to_menu( $parent_id, $menu_id );
	} else {
		happywood_menu_setup_log( sprintf( 'Rebuilding children for %s (ID %d).', $title, $parent_id ) );
	}

	wp_update_post(
		array(
			'ID'         => $parent_id,
			'post_title' => $title,
		)
	);
	update_post_meta( $parent_id, '_menu_item_title', $title );
	happywood_menu_setup_set_fields( $parent_id, $fields );

	$build_children( $menu_id, $parent_id );

	return $parent_id;
}

/**
 * Create or reuse a top-level custom menu item and run a child builder.
 *
 * @param int                  $menu_id        Nav menu term ID.
 * @param string               $title          Menu item title.
 * @param string               $url            Item URL.
 * @param array<string, mixed> $fields         ACF fields for the top-level item.
 * @param callable             $build_children Callback receiving ( menu_id, parent_id ).
 * @param bool                 $force_rebuild  When true, replace existing children.
 */
function happywood_menu_setup_build_top_level_custom(
	int $menu_id,
	string $title,
	string $url,
	array $fields,
	callable $build_children,
	bool $force_rebuild = false
): int {

	$parent_id = happywood_menu_setup_find_top_level_title( $menu_id, $title );

	if ( $parent_id && happywood_menu_setup_has_children( $menu_id, $parent_id ) && ! $force_rebuild ) {
		happywood_menu_setup_log( sprintf( 'Skipping %s — already built (ID %d).', $title, $parent_id ) );
		return $parent_id;
	}

	if ( ! $parent_id ) {
		$parent_id = happywood_menu_setup_add_custom( $menu_id, 0, $title, $url, $fields );
	} else {
		if ( $force_rebuild ) {
			happywood_menu_setup_log( sprintf( 'Force rebuilding children for %s (ID %d).', $title, $parent_id ) );
			happywood_menu_setup_delete_children( $menu_id, $parent_id );
		} else {
			happywood_menu_setup_log( sprintf( 'Rebuilding children for %s (ID %d).', $title, $parent_id ) );
		}

		happywood_menu_setup_set_fields( $parent_id, $fields );
	}

	$build_children( $menu_id, $parent_id );

	return $parent_id;
}

/**
 * Add thumbnail-link children in menu order (flat grid).
 *
 * @param int                                                     $menu_id Nav menu term ID.
 * @param int                                                     $parent_id Parent menu item ID.
 * @param array<int, array{0: int, 1: string}>|array<int, string> $items   term_id/title pairs or map.
 * @param string                                                  $taxonomy Taxonomy slug (default product_cat).
 */
function happywood_menu_setup_add_thumbnail_items(
	int $menu_id,
	int $parent_id,
	array $items,
	string $taxonomy = 'product_cat'
): void {

	foreach ( $items as $key => $value ) {
		if ( is_array( $value ) ) {
			[ $term_id, $title ] = $value;
		} else {
			$term_id = (int) $key;
			$title   = (string) $value;
		}

		happywood_menu_setup_add_term( $menu_id, $parent_id, (int) $term_id, $title, array(), $taxonomy );
	}
}

/**
 * Add thumbnail-link children for post objects, in the order given.
 *
 * The post counterpart to happywood_menu_setup_add_thumbnail_items().
 *
 * @param int                                  $menu_id   Nav menu term ID.
 * @param int                                  $parent_id Parent menu item ID.
 * @param array<int, array{0: int, 1: string}> $items     post_id/title pairs.
 * @param string                               $post_type Post type slug (default post).
 */
function happywood_menu_setup_add_post_thumbnail_items(
	int $menu_id,
	int $parent_id,
	array $items,
	string $post_type = 'post'
): void {

	foreach ( $items as $item ) {
		[ $post_id, $title ] = $item;

		happywood_menu_setup_add_post( $menu_id, $parent_id, (int) $post_id, (string) $title, array(), $post_type );
	}
}
