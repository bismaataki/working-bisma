<?php
/**
 * Add an Articles section to Primary Nav, derived from posts already present.
 *
 * A bootstrap for the blog side of the mega menu: it builds a starting point
 * once and then hands the menu to the editor. Re-running it will not overwrite
 * hand edits, so it is safe on a new environment.
 *
 * Back up first. This adds to the menu at the assigned location and will sit
 * alongside anything already there:
 *
 *   ddev wp eval-file wp-content/themes/happywood/bin/backup-primary-nav-menu.php
 *   ddev wp eval-file wp-content/themes/happywood/bin/setup-primary-nav-articles.php
 *
 * Procedure and the choice between sources: .cursor/skills/sf-setup-menus/
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/lib/menu-setup-helpers.php';
require_once __DIR__ . '/lib/menu-setup-articles.php';

$survey = happywood_menu_setup_survey_articles();

happywood_menu_setup_log(
	sprintf(
		'Content survey: %d eligible post categories, %d posts in scope, %d published posts total.',
		$survey['categories'],
		$survey['posts_in_scope'],
		$survey['posts_published']
	)
);

if ( 0 === $survey['categories'] ) {
	happywood_menu_setup_log(
		'Nothing to derive from. A fresh install has only the default category, '
		. 'which is deliberately skipped. Publish posts into a real category first.'
	);

	return;
}

$result = happywood_menu_setup_run_articles();

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI' ) ) {
	WP_CLI::success(
		sprintf(
			'Primary Nav (ID %d): %d new article sections added. '
			. 'Reorder and rename them in Appearance > Menus.',
			$result['primary_menu_id'],
			$result['created']
		)
	);
}
