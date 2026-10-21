<?php
/**
 * Theme include loader.
 *
 * Timeless root files load here. Each capability directory loads through its
 * own `bootstrap.php` — delete that directory and its one require below.
 * Header and menu bootstraps are loaded from their `lib/` classes, not here.
 *
 * @package Happywood
 */

/**
 * Composer Auto Loader
 */
if ( ! file_exists( get_stylesheet_directory() . '/vendor/autoload.php' ) ) {
	wp_die( 'vendor directory missing. Please execute `composer install` to create vendor directory.' );
}
require_once get_stylesheet_directory() . '/vendor/autoload.php';

/**
 * Core — constants and generic helpers.
 */
require_once get_stylesheet_directory() . '/includes/constants.php';
require_once get_stylesheet_directory() . '/includes/helper-functions.php';

/**
 * Shared machinery with no capability of its own.
 */
require_once get_stylesheet_directory() . '/includes/accordion-functions.php';
require_once get_stylesheet_directory() . '/includes/taxonomy-terms-functions.php';
require_once get_stylesheet_directory() . '/includes/category-swiper-functions.php';
require_once get_stylesheet_directory() . '/includes/load-more-functions.php';
require_once get_stylesheet_directory() . '/includes/archive-shell-functions.php';

/**
 * Capabilities. One require per directory.
 */
require_once get_stylesheet_directory() . '/includes/woocommerce/bootstrap.php';
require_once get_stylesheet_directory() . '/includes/post/bootstrap.php';
require_once get_stylesheet_directory() . '/includes/faq/bootstrap.php';
require_once get_stylesheet_directory() . '/includes/integrations/bootstrap.php';

/**
 * Remove Comment System
 */
// require_once get_stylesheet_directory() . '/includes/disable-comments.php';
