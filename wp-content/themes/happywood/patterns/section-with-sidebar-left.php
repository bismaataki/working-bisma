<?php
/**
 * Title: Section with Left Sidebar
 * Slug: happywood/section-with-sidebar-left
 * Description: Two-column page section with a left sidebar and main content column.
 * Categories: section, happywood
 * Keywords: sidebar, layout, columns, left, content
 * Block Types: core/columns
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large)"><!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-left"} -->
<div class="wp-block-columns alignwide is-style-has-sidebar-left"><!-- wp:column {"className":"hw-sidebar-aside"} -->
<div class="wp-block-column hw-sidebar-aside"><!-- wp:group {"tagName":"aside","layout":{"type":"default"}} -->
<aside class="wp-block-group"><!-- wp:heading {"level":6,"className":"is-style-text-eyebrow","style":{"typography":{"textTransform":"uppercase"}},"fontSize":"small"} -->
<h6 class="wp-block-heading is-style-text-eyebrow has-small-font-size" style="text-transform:uppercase"><?php esc_html_e( 'Sidebar', 'happywood' ); ?></h6>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Sidebar content goes here.', 'happywood' ); ?></p>
<!-- /wp:paragraph --></aside>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"hw-sidebar-main"} -->
<div class="wp-block-column hw-sidebar-main"><!-- wp:paragraph -->
<p><?php esc_html_e( 'Main content goes here.', 'happywood' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
