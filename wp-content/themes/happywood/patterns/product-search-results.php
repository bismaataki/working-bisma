<?php
/**
 * Title: product-search-results
 * Slug: happywood/product-search-results
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:pattern {"slug":"happywood/breadcrumbs"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"blockGap":"var:preset|spacing|0"}},"layout":{"inherit":true,"type":"constrained"}} -->
<main class="wp-block-group">
	<!-- wp:woocommerce/store-notices /-->

	<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|medium","bottom":"var:preset|spacing|medium"},"margin":{"bottom":"var:preset|spacing|medium"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignfull has-white-background-color has-background" style="margin-bottom:var(--wp--preset--spacing--medium);padding-top:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--medium)"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
		<div class="wp-block-group alignwide"><!-- wp:query-title {"type":"archive","showPrefix":false,"align":"wide"} /-->

			<!-- wp:term-description {"align":"wide"} /-->
			<!-- wp:search {"label":"<?php esc_attr_e('Search', 'happywood');?>","buttonText":"<?php esc_attr_e('Search', 'happywood');?>","buttonPosition":"button-inside","buttonUseIcon":true} /--></div>
		<!-- /wp:group --></div>
	<!-- /wp:group -->
	<!-- wp:woocommerce/product-collection {"queryId":0,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"taxQuery":[],"isProductCollectionBlock":true,"perPage":10,"pages":0,"offset":0,"postType":"product","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":true},"tagName":"div","displayLayout":{"type":"flex","columns":5,"shrinkColumns":true},"dimensions":{"widthType":"fill","fixedWidth":""},"queryContextIncludes":["collection"],"align":"wide"} -->
	<div class="wp-block-woocommerce-product-collection alignwide">
		<!-- wp:woocommerce/product-template -->
		<!-- wp:happywood/product-card /-->
		<!-- /wp:woocommerce/product-template -->

		<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
		<!-- wp:query-pagination-previous /-->

		<!-- wp:query-pagination-numbers /-->

		<!-- wp:query-pagination-next /-->
		<!-- /wp:query-pagination -->

		<!-- wp:woocommerce/product-collection-no-results {"align":"wide"} -->
		<!-- wp:paragraph -->
		<p><?php esc_html_e('No products were found matching your selection.', 'happywood');?></p>
		<!-- /wp:paragraph -->

		<!-- wp:search {"showLabel":false,"placeholder":"<?php esc_attr_e('Search products…', 'happywood');?>","buttonText":"<?php esc_attr_e('Search', 'happywood');?>","query":{"post_type":"product"}} /-->
		<!-- /wp:woocommerce/product-collection-no-results -->
	</div>
	<!-- /wp:woocommerce/product-collection -->



</main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
