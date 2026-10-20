<?php
/**
 * Dump Primary Nav + Utility Nav structure from wp-admin (for syncing setup script).
 *
 * Usage: ddev wp eval-file wp-content/themes/happywood/bin/dump-primary-nav-menu.php
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/menu-setup-helpers.php';

/**
 * @param int $menu_id Nav menu term ID.
 */
function happywood_menu_dump_tree( int $menu_id ): void {
	$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

	if ( ! is_array( $items ) || empty( $items ) ) {
		WP_CLI::warning( "Menu {$menu_id} has no items." );
		return;
	}

	usort(
		$items,
		static fn( $a, $b ) => array( (int) $a->menu_order, (int) $a->ID ) <=> array( (int) $b->menu_order, (int) $b->ID )
	);

	$parent_map = array();
	foreach ( $items as $item ) {
		$parent_map[ (int) $item->ID ] = (int) $item->menu_item_parent;
	}

	foreach ( $items as $item ) {
		$depth = 0;
		$pid   = (int) $item->menu_item_parent;

		while ( $pid ) {
			++$depth;
			$pid = $parent_map[ $pid ] ?? 0;
		}

		$indent = str_repeat( '  ', $depth );
		$acf    = function_exists( 'get_fields' ) ? ( get_fields( $item->ID ) ?: array() ) : array();
		$bits   = array(
			"type={$item->type}",
			"object={$item->object}",
			"obj_id={$item->object_id}",
		);

		if ( 'custom' === $item->type && $item->url ) {
			$bits[] = 'url=' . $item->url;
		}

		foreach ( array( 'link_type', 'grid_columns', 'nav_align', 'column_span', 'child_columns', 'utility_icon' ) as $key ) {
			if ( ! empty( $acf[ $key ] ) ) {
				$bits[] = "{$key}={$acf[ $key ]}";
			}
		}

		if ( ! empty( $acf['label_mobile'] ) ) {
			$bits[] = 'label_mobile=' . wp_json_encode( $acf['label_mobile'] );
		}

		WP_CLI::log(
			sprintf(
				'%s[%d] %s %s',
				$indent,
				$item->ID,
				$item->title,
				implode( ' ', $bits )
			)
		);
	}
}

/**
 * Print one location's menu, or say plainly that nothing is assigned to it.
 *
 * @param string $label    Human label for the heading.
 * @param string $location Theme location slug.
 */
function happywood_menu_dump_location( string $label, string $location ): void {
	$menu_id = happywood_menu_setup_find_for_location( $location );

	if ( ! $menu_id ) {
		WP_CLI::log( sprintf( '=== %s ===', $label ) );
		WP_CLI::warning( sprintf( 'No menu assigned to location %s.', $location ) );
		return;
	}

	$menu = wp_get_nav_menu_object( $menu_id );

	WP_CLI::log( sprintf( '=== %s: %s (%d) ===', $label, $menu->name, $menu_id ) );
	happywood_menu_dump_tree( $menu_id );
}

$happywood_dump_locations = happywood_menu_setup_locations();

happywood_menu_dump_location( 'Primary Nav', $happywood_dump_locations['primary'] );

WP_CLI::log( '' );

happywood_menu_dump_location( 'Utility Nav', $happywood_dump_locations['utility'] );
