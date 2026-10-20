<?php
/**
 * Thumbnail + label menu item renderer (Patterns B/C).
 *
 * @package Skaflow
 */

if ( empty( $args['item'] ) || ! $args['item'] instanceof WP_Post ) {
	return;
}

$item = $args['item'];
$url  = ! empty( $item->url ) ? $item->url : '#';

printf(
	'<a href="%s" class="site-header__menu-thumb-link">',
	esc_url( $url )
);

skaflow_menu_render_thumbnail( $item );

echo '<span class="site-header__menu-thumb-label">';
echo skaflow_menu_render_labels( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes labels.
echo '</span>';
echo '</a>';
