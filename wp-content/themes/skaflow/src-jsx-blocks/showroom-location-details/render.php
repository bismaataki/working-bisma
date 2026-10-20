<?php
/**
 * showroom-location-details block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo skaflow_render_showroom_location_details( $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
