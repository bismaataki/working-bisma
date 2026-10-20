<?php
/**
 * Post Filters block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = is_array( $attributes ) ? $attributes : array();

echo skaflow_render_post_filters( $attributes, $block ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
