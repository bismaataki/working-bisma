<?php
/**
 * Theme bootstrap — load includes and start Happywood\Init.
 *
 * @package Happywood
 */

/**
 * Required functions parts
 */
require get_stylesheet_directory() . '/includes/init.php';
/**
 * Theme Bootstrap.
 *
 * @since    0.0.1
 */
function Happywood() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid -- theme bootstrap entry.

	return Happywood\Init::get_instance();
}

Happywood();
