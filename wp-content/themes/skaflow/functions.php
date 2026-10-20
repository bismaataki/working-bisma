<?php
/**
 * Theme bootstrap — load includes and start Skaflow\Init.
 *
 * @package Skaflow
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
function Skaflow() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid -- theme bootstrap entry.

	return Skaflow\Init::get_instance();
}

Skaflow();
