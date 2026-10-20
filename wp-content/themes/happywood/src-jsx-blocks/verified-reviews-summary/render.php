<?php
/**
 * Verified Reviews Summary block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = is_array( $attributes ) ? $attributes : array();

echo happywood_render_verified_reviews_summary_block( $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
