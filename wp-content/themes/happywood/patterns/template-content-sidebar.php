<?php
/**
 * Title: Template Content Sidebar
 * Slug: happywood/template-content-sidebar
 * Description: Single post main column with bordered content area and sticky sidebar widgets.
 * Categories: page, happywood
 * Keywords: single, post, sidebar, content, template
 * Inserter: no
 */
?>
<!-- wp:group {"metadata":{"categories":["section","happywood"],"name":"Section with Right Sidebar"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-right"} -->
	<div class="wp-block-columns alignwide is-style-has-sidebar-right">

		<!-- wp:column {"className":"hw-sidebar-main is-style-bordered","style":{"spacing":{"padding":{"top":"var:preset|spacing|xxx-large","bottom":"var:preset|spacing|xxx-large","left":"var:preset|spacing|small","right":"var:preset|spacing|small"}}}} -->
		<div class="wp-block-column hw-sidebar-main is-style-bordered" style="padding-top:var(--wp--preset--spacing--xxx-large);padding-right:var(--wp--preset--spacing--small);padding-bottom:var(--wp--preset--spacing--xxx-large);padding-left:var(--wp--preset--spacing--small)"><!-- wp:post-content {"lock":{"move":false,"remove":true},"layout":{"type":"constrained"}} /--></div>
		<!-- /wp:column -->

		<!-- wp:column {"className":"hw-sidebar-aside"} -->
		<div class="wp-block-column hw-sidebar-aside"><!-- wp:pattern {"slug":"happywood/sidebar"} /--></div>
		<!-- /wp:column --></div>
	<!-- /wp:columns --></div>
<!-- /wp:group -->
