<?php
/**
 * Title: footer
 * Slug: happywood/footer
 * Inserter: no
 */
?>
<!-- wp:group {"metadata":{"name":"Footer","patternName":"happywood/footer","categories":["footer","happywood"],"description":"Site footer with brand column, nav columns, location map, copyright, and payment icons."},"className":"site-footer","style":{"spacing":{"margin":{"top":"0"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group site-footer has-white-background-color has-background" style="margin-top:0"><!-- wp:group {"metadata":{"name":"Footer Main"},"align":"wide","className":"site-footer__main","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide site-footer__main"><!-- wp:columns {"verticalAlignment":null,"align":"wide","className":"site-footer__columns"} -->
<div class="wp-block-columns alignwide site-footer__columns"><!-- wp:column {"verticalAlignment":"top","width":"35%","className":"site-footer__nav-column footer-nav__shop","style":{"spacing":{"blockGap":"var:preset|spacing|0"}},"layout":{"type":"default"}} -->
<div class="wp-block-column is-vertically-aligned-top site-footer__nav-column footer-nav__shop" style="flex-basis:35%"><!-- wp:group {"className":"is-style-default","style":{"spacing":{"padding":{"right":"var:preset|spacing|huge"},"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained","justifyContent":"center"}} -->
<div class="wp-block-group is-style-default" style="padding-right:var(--wp--preset--spacing--huge)"><!-- wp:heading -->
<h2 class="wp-block-heading"><?php esc_html_e('Happy Wood', 'happywood');?></h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"className":"site-footer__tagline is-style-text-lead","style":{"spacing":{"padding":{"top":"var:preset|spacing|small","right":"var:preset|spacing|huge"}}},"fontSize":"small"} -->
<p class="site-footer__tagline is-style-text-lead has-small-font-size" style="padding-top:var(--wp--preset--spacing--small);padding-right:var(--wp--preset--spacing--huge)"><?php esc_html_e('Commercial-grade products, supplied quickly and without sky-high prices.', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:social-links {"iconColor":"body","iconColorValue":"#16181B","openInNewTab":true,"size":"has-normal-icon-size","className":"site-footer__social is-style-logos-only","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|small"}}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<ul class="wp-block-social-links has-normal-icon-size has-icon-color site-footer__social is-style-logos-only"><!-- wp:social-link {"url":"https://www.facebook.com/","service":"facebook"} /-->

<!-- wp:social-link {"url":"https://www.instagram.com/","service":"instagram"} /-->

<!-- wp:social-link {"url":"https://www.linkedin.com/","service":"linkedin"} /-->

<!-- wp:social-link {"url":"https://www.youtube.com/","service":"youtube"} /--></ul>
<!-- /wp:social-links --></div>
<!-- /wp:group --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"20%","className":"site-footer__nav-column footer-nav__care","style":{"spacing":{"blockGap":"var:preset|spacing|0"}}} -->
<div class="wp-block-column site-footer__nav-column footer-nav__care" style="flex-basis:20%"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
<p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color"><?php esc_html_e('About', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e('FAQs', 'happywood');?>","url":"/faq/","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e('Track Order', 'happywood');?>","url":"/my-account/orders/","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"top","width":"20%","className":"site-footer__nav-column footer-nav__shop","style":{"spacing":{"blockGap":"var:preset|spacing|0"}},"layout":{"type":"default"}} -->
<div class="wp-block-column is-vertically-aligned-top site-footer__nav-column footer-nav__shop" style="flex-basis:20%"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
<p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color"><?php esc_html_e('Shop', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left","flexWrap":"wrap"}} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e('Shop All', 'happywood');?>","url":"/shop/","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e('Cart', 'happywood');?>","url":"/cart/","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e('Checkout', 'happywood');?>","url":"/checkout/","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"20%","className":"site-footer__nav-column footer-nav__company","style":{"spacing":{"blockGap":"var:preset|spacing|0"}}} -->
<div class="wp-block-column site-footer__nav-column footer-nav__company" style="flex-basis:20%"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
<p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color"><?php esc_html_e('Help', 'happywood');?></p>
<!-- /wp:paragraph -->

<!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
<!-- wp:navigation-link {"label":"<?php esc_attr_e('Blog', 'happywood');?>","url":"/blog/","kind":"custom","isTopLevelLink":true} /-->

<!-- wp:navigation-link {"label":"<?php esc_attr_e('My Account', 'happywood');?>","url":"/my-account/","kind":"custom","isTopLevelLink":true} /-->
<!-- /wp:navigation --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"metadata":{"name":"Footer Bottom"},"align":"wide","className":"site-footer__bottom","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignwide site-footer__bottom"><!-- wp:group {"align":"wide","className":"site-footer__bottom-inner","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
<div class="wp-block-group alignwide site-footer__bottom-inner"><!-- wp:group {"className":"site-footer__copyright","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group site-footer__copyright"><!-- wp:happywood/copyright-date-block {"startingYear":""} -->
<p class="wp-block-happywood-copyright-date-block">Copyright Year</p>
<!-- /wp:happywood/copyright-date-block -->

<!-- wp:paragraph -->
<p><?php esc_html_e('Happy Wood. All rights reserved.', 'happywood');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:group {"className":"site-footer__payments","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
<div class="wp-block-group site-footer__payments"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/footer/payment-cards.svg' ) ); ?>" alt="<?php esc_attr_e('Accepted payment methods', 'happywood');?>"/></figure>
<!-- /wp:image --></div>
<!-- /wp:group --></div>
<!-- /wp:group --></div>
<!-- /wp:group -->

<!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"45%","className":"site-footer__brand"} -->
<div class="wp-block-column site-footer__brand" style="flex-basis:45%"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"site-footer__tagline is-style-text-lead","style":{"spacing":{"padding":{"top":"var:preset|spacing|small"}}},"fontSize":"small"} -->
<p class="site-footer__tagline is-style-text-lead has-small-font-size" style="padding-top:var(--wp--preset--spacing--small)"><?php esc_html_e('Commercial-grade products, supplied quickly and without sky-high prices.', 'happywood');?></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div>
<!-- /wp:group -->