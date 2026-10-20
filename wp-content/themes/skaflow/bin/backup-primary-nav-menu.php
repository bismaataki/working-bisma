<?php
/**
 * Export Primary Nav menu to JSON for restore/reference.
 *
 * Usage:
 *   ddev wp eval-file wp-content/themes/skaflow/bin/backup-primary-nav-menu.php
 *
 * Writes: bin/backups/primary-nav-{Y-m-d-His}.json
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/menu-setup-helpers.php';

$locations = skaflow_menu_setup_locations();
$menu_id   = skaflow_menu_setup_find_for_location( $locations['primary'] );

// Erroring out matters more here than anywhere else in bin/. This is the
// backup the seeders tell you to take first, so a snapshot of the wrong menu,
// or of nothing at all, is worse than no snapshot: you would seed on top of a
// live nav believing it was recoverable.
if ( ! $menu_id ) {
	WP_CLI::error(
		sprintf( 'No menu assigned to location %s. Nothing to back up.', $locations['primary'] )
	);
}

$items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );

if ( ! is_array( $items ) ) {
	WP_CLI::error( 'Primary Nav menu items could not be loaded.' );
}

$menu = wp_get_nav_menu_object( $menu_id );

$export = array(
	'exported_at' => gmdate( 'c' ),
	'menu_id'     => $menu_id,
	'menu_name'   => $menu->name ?? 'Primary Nav',
	'items'       => array(),
);

foreach ( $items as $item ) {
	$acf = function_exists( 'get_fields' ) ? ( get_fields( $item->ID ) ?: array() ) : array();

	$export['items'][] = array(
		'id'        => (int) $item->ID,
		'parent'    => (int) $item->menu_item_parent,
		'order'     => (int) $item->menu_order,
		'title'     => $item->title,
		'type'      => $item->type,
		'object'    => $item->object,
		'object_id' => (int) $item->object_id,
		'url'       => $item->url,
		'acf'       => $acf,
	);
}

$backup_dir = __DIR__ . '/backups';

if ( ! is_dir( $backup_dir ) ) {
	wp_mkdir_p( $backup_dir );
}

$filename = sprintf( 'primary-nav-%s.json', gmdate( 'Y-m-d-His' ) );
$path     = $backup_dir . '/' . $filename;

$written = file_put_contents(
	$path,
	wp_json_encode( $export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
);

if ( false === $written ) {
	WP_CLI::error( "Failed to write backup to {$path}" );
}

WP_CLI::success( sprintf( 'Primary Nav backup saved (%d items): %s', count( $export['items'] ), $path ) );
