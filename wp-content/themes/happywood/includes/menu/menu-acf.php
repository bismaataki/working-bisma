<?php
/**
 * ACF field hooks for menu item options.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'acf/load_field/name=utility_icon',
	function ( $field ) {

		$field['choices'] = happywood_get_lucide_icon_choices();

		return $field;
	}
);

add_filter(
	'acf/load_field/key=field_happywood_menu_utility_icon',
	function ( $field ) {

		$field['choices'] = happywood_get_lucide_icon_choices();

		return $field;
	}
);
