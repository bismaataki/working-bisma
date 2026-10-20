<?php
/**
 * Title: Title Post
 * Slug: happywood/title-post
 * Description: Centred post title section with category label and post meta.
 * Categories: banner, happywood
 * Keywords: title, post, heading, category, meta, blog
 */
?>
<!-- wp:group {"metadata":{"name":"Post Title Section"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|section-m","bottom":"var:preset|spacing|0"}}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--section-m);padding-bottom:var(--wp--preset--spacing--0)"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
	<div class="wp-block-group"><!-- wp:happywood/taxonomy-terms {"limit":1} /-->

		<!-- wp:post-title {"textAlign":"center","level":1} /-->
		<!-- wp:pattern {"slug":"happywood/component-post-meta"} /-->
</div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
