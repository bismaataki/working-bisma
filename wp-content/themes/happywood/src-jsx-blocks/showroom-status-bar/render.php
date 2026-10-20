<?php
/**
 * showroom-status-bar block render template.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo happywood_render_showroom_status_bar( $block ?? null ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
