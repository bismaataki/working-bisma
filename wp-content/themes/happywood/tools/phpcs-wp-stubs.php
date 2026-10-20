<?php
/**
 * Minimal WP stubs so TGMPA's Composer files-autoload can load outside WordPress.
 * Used only as auto_prepend_file for `composer phpcs` / `composer phpcbf`.
 */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', '/tmp/' );
}

if ( ! function_exists( 'did_action' ) ) {
	function did_action( $hook_name = '' ) {
		return 0;
	}
}

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! class_exists( 'WP_List_Table', false ) ) {
	class WP_List_Table {}
}
