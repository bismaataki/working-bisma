<?php
/**
 * Title: Title Page
 * Slug: skaflow/title-page
 * Description: Centred page title section with page meta below the heading.
 * Categories: banner, skaflow
 * Keywords: title, page, heading, meta, banner
 */
?>
<!-- wp:group {"metadata":{"name":"Post Title Section"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|section-m","bottom":"var:preset|spacing|section-m"}}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--section-m);padding-bottom:var(--wp--preset--spacing--section-m)"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group"><!-- wp:post-title {"textAlign":"center","level":1} /-->
		<!-- wp:pattern {"slug":"skaflow/component-page-meta"} /-->
</div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
