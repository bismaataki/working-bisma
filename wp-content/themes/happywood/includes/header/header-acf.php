<?php
/**
 * ACF field hooks for header theme options.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_theme_file_path( 'includes/menu/happywood-lucide-icon-choices.php' );

add_filter(
	'acf/load_field/name=announcement_icon',
	function ( $field ) {

		$field['choices'] = happywood_get_lucide_icon_choices();

		return $field;
	}
);

add_filter(
	'acf/load_field/key=field_happywood_header_announcement_icon',
	function ( $field ) {

		$field['choices'] = happywood_get_lucide_icon_choices();

		return $field;
	}
);
