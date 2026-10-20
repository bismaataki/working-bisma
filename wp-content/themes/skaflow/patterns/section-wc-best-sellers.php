<?php
/**
 * Title: WC Best Sellers
 * Slug: skaflow/section-wc-best-sellers
 * Description: Marketing section heading plus WooCommerce best-sellers product collection grid.
 * Categories: section, skaflow
 * Keywords: best sellers, products, woocommerce, collection, shop
 * Inserter: Yes
 */
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Best Sellers"},"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:woocommerce/product-collection {"queryId":0,"query":{"perPage":10,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"popularity","search":"","exclude":[],"inherit":false,"taxQuery":{},"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"carousel","columns":5,"shrinkColumns":true},"dimensions":{"widthType":"fill","fixedWidth":"20%"},"collection":"woocommerce/product-collection/best-sellers","hideControls":["inherit","order","filterable"],"queryContextIncludes":["collection"],"forcePageReload":true,"__privatePreviewState":{"isPreview":false,"previewMessage":"Actual products will vary depending on the page being viewed."},"align":"wide","layout":{"type":"default"}} -->
    <div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
        <div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small","padding":{"bottom":"var:preset|spacing|medium"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group" style="padding-bottom:var(--wp--preset--spacing--medium)"><!-- wp:paragraph {"className":"is-style-text-eyebrow"} -->
                <p class="is-style-text-eyebrow">Most loved</p>
                <!-- /wp:paragraph -->

                <!-- wp:heading {"fontSize":"xxx-large"} -->
                <h2 class="wp-block-heading has-xxx-large-font-size">Best sellers</h2>
                <!-- /wp:heading -->

                <!-- wp:paragraph -->
                <p>Our most popular pieces, in stock and ready to ship.</p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:group -->

            <!-- wp:woocommerce/product-gallery-large-image-next-previous {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
            <div class="wp-block-woocommerce-product-gallery-large-image-next-previous"></div>
            <!-- /wp:woocommerce/product-gallery-large-image-next-previous --></div>
        <!-- /wp:group -->

        <!-- wp:woocommerce/product-template {"layout":{"type":"flex","justifyContent":"left","verticalAlignment":"top","flexWrap":"nowrap","orientation":"horizontal"}} -->
        <!-- wp:skaflow/product-card {"titleTag":"h3"} /-->
        <!-- /wp:woocommerce/product-template --></div>
    <!-- /wp:woocommerce/product-collection --></section>
<!-- /wp:group -->
