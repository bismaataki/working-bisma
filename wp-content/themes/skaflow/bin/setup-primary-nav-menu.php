<?php
/**
 * Build Primary Nav + Utility Nav menus per Figma mega menu specs.
 *
 * Resolves menus by theme location (creates + assigns if missing).
 * Same builder the BatchPress go-live job calls.
 *
 * Usage: ddev wp eval-file wp-content/themes/skaflow/bin/setup-primary-nav-menu.php
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/menu-setup-helpers.php';
require_once __DIR__ . '/lib/menu-setup-tree.php';

$result = skaflow_menu_setup_run();

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
	WP_CLI::success(
		sprintf(
			'Primary Nav (ID %d) and Utility Nav (ID %d) menus built.',
			$result['primary_menu_id'],
			$result['utility_menu_id']
		)
	);
}
