<?php
/**
 * Mega menu walker wiring and ACF menu fields.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\Mega_Menu' ) ) {
	return;
}

/**
 * Registers menu infrastructure: walker dependencies, hooks, and helpers.
 */
class Mega_Menu {

	/**
	 * Mega_Menu constructor.
	 */
	public function __construct() {

		$this->load_dependencies();
	}

	/**
	 * Load menu PHP dependencies.
	 */
	private function load_dependencies(): void {

		require_once get_theme_file_path( 'includes/menu/bootstrap.php' );
	}
}
