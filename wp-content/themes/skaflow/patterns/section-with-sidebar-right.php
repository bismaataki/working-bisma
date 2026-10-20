<?php
/**
 * Title: Section with Right Sidebar
 * Slug: skaflow/section-with-sidebar-right
 * Description: Two-column page section with main content and a right sidebar column.
 * Categories: section, skaflow
 * Keywords: sidebar, layout, columns, right, content
 * Block Types: core/columns
 */
?>
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large)"><!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-right"} -->
<div class="wp-block-columns alignwide is-style-has-sidebar-right"><!-- wp:column {"className":"sk-sidebar-main"} -->
<div class="wp-block-column sk-sidebar-main"><!-- wp:paragraph -->
<p><?php esc_html_e( 'Main content goes here.', 'skaflow' ); ?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"className":"sk-sidebar-aside"} -->
<div class="wp-block-column sk-sidebar-aside"><!-- wp:group {"tagName":"aside","layout":{"type":"default"}} -->
<aside class="wp-block-group"><!-- wp:heading {"level":6,"className":"is-style-text-eyebrow","style":{"typography":{"textTransform":"uppercase"}},"fontSize":"small"} -->
<h6 class="wp-block-heading is-style-text-eyebrow has-small-font-size" style="text-transform:uppercase"><?php esc_html_e( 'Sidebar', 'skaflow' ); ?></h6>
<!-- /wp:heading -->

<!-- wp:paragraph {"fontSize":"small"} -->
<p class="has-small-font-size"><?php esc_html_e( 'Sidebar content goes here.', 'skaflow' ); ?></p>
<!-- /wp:paragraph --></aside>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->
