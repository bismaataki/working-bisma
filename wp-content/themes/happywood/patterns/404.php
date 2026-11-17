<?php
/**
 * Title: 404
 * Slug: happywood/404
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","metadata":{"name":"Main"},"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|block-gap"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--block-gap)"><!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-xl","bottom":"var:preset|spacing|section-xl"},"blockGap":"var:preset|spacing|medium"}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
	<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--section-xl);padding-bottom:var(--wp--preset--spacing--section-xl)"><!-- wp:paragraph {"className":"is-style-text-eyebrow","style":{"typography":{"textAlign":"center"}}} -->
		<p class="has-text-align-center is-style-text-eyebrow"><?php esc_html_e('404', 'happywood');?></p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"textAlign":"center","level":1,"fontSize":"xxxx-large"} -->
		<h1 class="wp-block-heading has-text-align-center has-xxxx-large-font-size"><?php esc_html_e('Page not found', 'happywood');?></h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"align":"center","className":"is-style-text-lead"} -->
		<p class="has-text-align-center is-style-text-lead"><?php esc_html_e('Sorry, we couldn\'t find that page. Try searching or head back to the shop.', 'happywood');?></p>
		<!-- /wp:paragraph -->

		<!-- wp:search {"label":"<?php esc_attr_e('Search', 'happywood');?>","showLabel":false,"placeholder":"<?php esc_attr_e('Search products…', 'happywood');?>","width":100,"widthUnit":"%","buttonText":"<?php esc_attr_e('Search', 'happywood');?>","buttonUseIcon":true,"align":"center"} /-->

		<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
		<div class="wp-block-buttons"><!-- wp:button -->
			<div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/"><?php esc_html_e('Back to home', 'happywood');?></a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="/shop/"><?php esc_html_e('Browse shop', 'happywood');?></a></div>
			<!-- /wp:button --></div>
		<!-- /wp:buttons --></div>
	<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
