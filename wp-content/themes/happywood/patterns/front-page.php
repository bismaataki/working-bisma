<?php
/**
 * Title: front-page
 * Slug: happywood/front-page
 * Inserter: no
 */
?>
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->

<!-- wp:group {"tagName":"main","metadata":{"name":"Main"},"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|block-gap"}}}} -->
<main class="wp-block-group" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--block-gap)"><!-- wp:cover {"url":"<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/6387ced1-a94c-469b-b259-3b81fcbfcd78.png","dimRatio":20,"overlayColor":"muted","isUserOverlayColor":true,"focalPoint":{"x":0.5,"y":0.5},"minHeight":70,"minHeightUnit":"vh","contentPosition":"center center","isDark":false,"sizeSlug":"full","metadata":{"patternName":"happywood/hero-banner","name":"hero banner"},"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull is-light" style="min-height:70vh"><img class="wp-block-cover__image-background size-full" alt="" src="<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/6387ced1-a94c-469b-b259-3b81fcbfcd78.png" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%"/><span aria-hidden="true" class="wp-block-cover__background has-muted-background-color has-background-dim-20 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"style":{"typography":{"textAlign":"center"},"elements":{"link":{"color":{"text":"var:preset|color|heading"}}}},"textColor":"heading"} -->
<h2 class="wp-block-heading has-text-align-center has-heading-color has-text-color has-link-color"><?php esc_html_e('Upgrade Your Home', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ghost","style":{"spacing":{"padding":{"top":"var:preset|spacing|xxx-small","bottom":"var:preset|spacing|xxx-small"}}}} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link wp-element-button" href="https://working-bisma.ddev.site/shop/" style="padding-top:var(--wp--preset--spacing--xxx-small);padding-bottom:var(--wp--preset--spacing--xxx-small)"><?php esc_html_e('Shop Now', 'happywood');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></div>
<!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"metadata":{"name":"category showcase"},"style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large)"><!-- wp:happywood/category-showcase {"categoryId":27,"align":"wide"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|medium","bottom":"var:preset|spacing|medium"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--medium)"><!-- wp:happywood/testimonials {"align":"wide"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","metadata":{"name":"Wc Top Sellers","description":"Marketing section heading plus WooCommerce best-sellers product collection grid.","categories":["section","happywood"]},"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:woocommerce/product-collection {"queryId":1,"query":{"perPage":10,"pages":1,"offset":0,"postType":"product","order":"desc","orderBy":"popularity","search":"","exclude":[],"inherit":false,"taxQuery":[],"isProductCollectionBlock":true,"featured":false,"woocommerceOnSale":false,"woocommerceStockStatus":["instock","outofstock","onbackorder"],"woocommerceAttributes":[],"woocommerceHandPickedProducts":[],"filterable":false,"relatedBy":{"categories":true,"tags":true}},"tagName":"div","displayLayout":{"type":"carousel","columns":5,"shrinkColumns":true},"dimensions":{"widthType":"fill","fixedWidth":"20%"},"collection":"woocommerce/product-collection/best-sellers","hideControls":["inherit","order","filterable"],"queryContextIncludes":["collection"],"forcePageReload":true,"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-woocommerce-product-collection alignwide"><!-- wp:group {"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"fontSize":"xxx-large"} -->
<h2 class="wp-block-heading has-xxx-large-font-size"><?php esc_html_e('Top Sellers', 'happywood');?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-gallery-large-image-next-previous {"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-woocommerce-product-gallery-large-image-next-previous"></div>
<!-- /wp:woocommerce/product-gallery-large-image-next-previous --></div>
<!-- /wp:group -->

<!-- wp:woocommerce/product-template {"layout":{"type":"flex","justifyContent":"left","verticalAlignment":"top","flexWrap":"nowrap","orientation":"horizontal"}} -->
<!-- wp:happywood/product-card {"titleTag":"h3"} /-->
<!-- /wp:woocommerce/product-template --></div>
<!-- /wp:woocommerce/product-collection -->

<!-- wp:buttons {"className":"is-style-full-on-small","style":{"spacing":{"padding":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"layout":{"type":"flex","verticalAlignment":"top","justifyContent":"center"}} -->
<div class="wp-block-buttons is-style-full-on-small" style="padding-top:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--0)"><!-- wp:button {"className":"is-style-ghost","style":{"typography":{"textAlign":"center"},"spacing":{"padding":{"top":"var:preset|spacing|xxx-small","bottom":"var:preset|spacing|xxx-small"}}}} -->
<div class="wp-block-button is-style-ghost"><a class="wp-block-button__link has-text-align-center wp-element-button" style="padding-top:var(--wp--preset--spacing--xxx-small);padding-bottom:var(--wp--preset--spacing--xxx-small)"><?php esc_html_e('View All', 'happywood');?></a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons --></section>
<!-- /wp:group -->

<!-- wp:group {"tagName":"section","metadata":{"name":"WC Shop By our collection","description":"Marketing section with category card swiper for browsing product categories.","categories":["section","happywood"]},"align":"full","style":{"spacing":{"blockGap":"var:preset|spacing|0","padding":{"top":"var:preset|spacing|section-m","bottom":"var:preset|spacing|section-m"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-m);padding-bottom:var(--wp--preset--spacing--section-m)"><!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading {"align":"wide","style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center alignwide"><?php esc_html_e('Our Collection', 'happywood');?></h2>
<!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:happywood/product-cat-swiper {"showArrowsMobile":true,"orderBy":"count","order":"desc","align":"wide","className":"is-style-card"} /--></section>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Offers Subscribe","description":"Newsletter subscribe section with offer copy and Gravity Forms embed.","categories":["section","happywood","call-to-action"]},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
<div class="wp-block-group alignwide"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
<h2 class="wp-block-heading has-text-align-center"><?php esc_html_e('Our Newsletter', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"is-style-text-lead","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center is-style-text-lead"><?php esc_html_e('Subscribe to receive special promotions, new product launches', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:group {"className":"hw-form-subscribe","layout":{"type":"default"}} -->
<div class="wp-block-group hw-form-subscribe"><!-- wp:gravityforms/form {"formId":"","title":false,"description":false,"inputPrimaryColor":"#204ce5"} /--></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></main>
<!-- /wp:group -->

<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->