<?php
/**
 * Blog post includes.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = get_stylesheet_directory() . '/includes/post';

require_once $dir . '/post-card-functions.php';
require_once $dir . '/post-filters-functions.php';
require_once $dir . '/post-archive-hooks.php';
