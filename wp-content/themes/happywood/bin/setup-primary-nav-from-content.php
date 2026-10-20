<?php
/**
 * Build Primary Nav from product categories that already exist.
 *
 * The derived alternative to `setup-primary-nav-menu.php`, which builds the
 * hand-authored tree in `lib/menu-setup-tree.php`. Use this when the catalogue
 * exists but the navigation design does not yet.
 *
 * Back up first. This adds to the menu at the assigned location and will sit
 * alongside anything already there:
 *
 *   ddev wp eval-file wp-content/themes/happywood/bin/backup-primary-nav-menu.php
 *   ddev wp eval-file wp-content/themes/happywood/bin/setup-primary-nav-from-content.php
 *
 * Procedure and the choice between sources: .cursor/skills/sf-setup-menus/
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/menu-setup-helpers.php';
require_once __DIR__ . '/lib/menu-setup-from-content.php';

$survey = happywood_menu_setup_survey_content();

happywood_menu_setup_log(
	sprintf(
		'Content survey: %d top-level product categories, %d children, %d products.',
		$survey['product_cats_top'],
		$survey['product_cats_kids'],
		$survey['products']
	)
);

if ( 0 === $survey['product_cats_top'] ) {
	happywood_menu_setup_log(
		'Nothing to derive from. Use bin/setup-primary-nav-menu.php with the '
		. 'hand-authored tree in bin/lib/menu-setup-tree.php instead.'
	);

	return;
}

$result = happywood_menu_setup_run_from_content();

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
	WP_CLI::success(
		sprintf(
			'Primary Nav (ID %d): %d new top-level items derived from product categories. '
			. 'Refine grouping and headings in Appearance > Menus.',
			$result['primary_menu_id'],
			$result['created']
		)
	);
}
