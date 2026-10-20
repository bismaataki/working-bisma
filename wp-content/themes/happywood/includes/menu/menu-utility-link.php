<?php
/**
 * Utility cluster menu item renderer (Showrooms, Account, Quotes).
 *
 * @package Happywood
 */

if ( empty( $args['item'] ) || ! $args['item'] instanceof WP_Post ) {
	return;
}

$item      = $args['item'];
$url       = ! empty( $item->url ) ? $item->url : '#';
$icon_slug = happywood_menu_get_utility_icon_slug( $item );

printf(
	'<a href="%s" class="site-header__utility-link site-header__utility-link--icon-%s">',
	esc_url( $url ),
	esc_attr( $icon_slug )
);

echo '<span class="site-header__utility-icon hw-icon-preview hw-icon-' . esc_attr( $icon_slug ) . '" aria-hidden="true"></span>';
echo '<span class="site-header__utility-label">';
echo happywood_menu_render_labels( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper escapes labels.
echo '</span>';
echo '</a>';
