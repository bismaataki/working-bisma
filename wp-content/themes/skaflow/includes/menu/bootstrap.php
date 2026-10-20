<?php
/**
 * Menu includes — walker, hooks, and ACF fields.
 *
 * Loaded from `Skaflow\Mega_Menu`, not from `includes/init.php`.
 * Template-part walkers stay out of this list.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$dir = get_stylesheet_directory() . '/includes/menu';

require_once $dir . '/skaflow-lucide-icon-choices.php';
require_once $dir . '/menu-functions.php';
require_once $dir . '/walker/class-primary-walker.php';
require_once $dir . '/menu-hooks.php';
require_once $dir . '/menu-acf.php';
