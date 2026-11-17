<?php
/**
 * Title: single-product
 * Slug: happywood/single-product
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"metadata":{"name":"Breadcrumbs","patternName":"happywood/breadcrumbs","categories":["layout"]},"align":"full","className":"pattern-breadcrumbs-wrapper","style":{"spacing":{"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"},"padding":{"top":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull pattern-breadcrumbs-wrapper has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--0)"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:breadcrumbs {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|xx-small","bottom":"var:preset|spacing|xx-small"}}},"fontSize":"small"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"main","layout":{"inherit":true,"type":"constrained"}} -->
<main class="wp-block-group"><!-- wp:woocommerce/store-notices /-->

<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"65%"} -->
<div class="wp-block-column" style="flex-basis:65%"><!-- wp:woocommerce/product-gallery {"hoverZoom":false,"layout":{"type":"flex","flexWrap":"nowrap","orientation":"horizontal"}} -->
<div class="wp-block-woocommerce-product-gallery wc-block-product-gallery"><!-- wp:woocommerce/product-gallery-thumbnails {"thumbnailSize":"20%","activeThumbnailStyle":"outline"} /-->

<!-- wp:woocommerce/product-gallery-large-image -->
<div class="wp-block-woocommerce-product-gallery-large-image wc-block-product-gallery-large-image__inner-blocks"><!-- wp:woocommerce/product-image {"showProductLink":false,"showSaleBadge":false} -->
<div class="is-loading"></div>
<!-- /wp:woocommerce/product-image -->

<!-- wp:woocommerce/product-sale-badge {"align":"right"} /-->

<!-- wp:woocommerce/product-gallery-large-image-next-previous -->
<div class="wp-block-woocommerce-product-gallery-large-image-next-previous"></div>
<!-- /wp:woocommerce/product-gallery-large-image-next-previous --></div>
<!-- /wp:woocommerce/product-gallery-large-image --></div>
<!-- /wp:woocommerce/product-gallery --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"35%"} -->
<div class="wp-block-column" style="flex-basis:35%"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:post-title {"level":1,"__woocommerceNamespace":"woocommerce/product-query/product-title"} /-->

<!-- wp:happywood/wishlist-button /--></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-price {"isDescendentOfSingleProductTemplate":true,"fontSize":"large"} /-->

<!-- wp:woocommerce/product-meta {"metadata":{"ignoredHookedBlocks":["core/post-terms"]}} -->
<div class="wp-block-woocommerce-product-meta"><!-- wp:woocommerce/product-sku {"style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small"} /--></div>
<!-- /wp:woocommerce/product-meta -->

<!-- wp:woocommerce/add-to-cart-form /-->

<!-- wp:woocommerce/product-details {"className":"is-style-minimal"} -->
<div class="wp-block-woocommerce-product-details alignwide is-style-minimal"></div>
<!-- /wp:woocommerce/product-details -->

<!-- wp:paragraph -->
<p><?php esc_html_e('Note: Actual product color may vary slightly from the image.', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:happywood/share-this-look /--></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph -->
<p></p>
<!-- /wp:paragraph -->

<!-- wp:woocommerce/product-stock-indicator /-->

<!-- wp:happywood/delivery-estimate /-->

<!-- wp:woocommerce/product-rating {"isDescendentOfSingleProductTemplate":true} /-->

<!-- wp:happywood/product-reviews {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"}}}} /--></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Related Wrapper"},"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-m","bottom":"var:preset|spacing|section-m"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--section-m);padding-bottom:var(--wp--preset--spacing--section-m)"><!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":5,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false},"tagName":"div","displayLayout":{"type":"flex","columns":5,"shrinkColumns":true},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/upsells","hideControls":["filterable"],"queryContextIncludes":["collection"],"align":"wide"} -->
<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"fontSize":"xxx-large"} -->
<h2 class="wp-block-heading has-xxx-large-font-size" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--30)"><?php esc_html_e('You may also like…', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-template -->
<!-- wp:happywood/product-card {"titleTag":"h3"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection -->

<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":5,"pages":1,"offset":0,"postType":"product","order":"asc","orderBy":"title","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"flex","columns":5,"shrinkColumns":false},"dimensions":{"widthType":"fill"},"collection":"woocommerce/product-collection/related","hideControls":["inherit"],"queryContextIncludes":["collection"],"align":"wide"} -->
<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:heading {"style":{"spacing":{"margin":{"top":"var:preset|spacing|30","bottom":"var:preset|spacing|30"}}},"fontSize":"xxx-large"} -->
<h2 class="wp-block-heading has-xxx-large-font-size" style="margin-top:var(--wp--preset--spacing--30);margin-bottom:var(--wp--preset--spacing--30)"><?php esc_html_e('Related products', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:woocommerce/product-template -->
<!-- wp:happywood/product-card {"titleTag":"h3"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"FAQs Section"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('Frequently Asked Questions', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:group {"metadata":{"name":"FAQ Accordions"},"className":"is-style-narrow","layout":{"type":"constrained"}} -->
<div class="wp-block-group is-style-narrow"><!-- wp:happywood/product-faqs /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->