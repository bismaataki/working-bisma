<?php
/**
 * Title: Footer
 * Slug: happywood/footer
 * Description: Site footer with brand column, nav columns, location map, copyright, and payment icons.
 * Categories: footer, happywood
 * Keywords: footer, navigation, copyright, social, map
 * Inserter: no
 *
 * The three nav columns are `core/navigation` blocks with inline
 * `navigation-link` children rather than a saved WordPress menu. That is
 * deliberate: inline links travel with the theme, so the footer renders on a
 * fresh clone with no setup. A menu assigned in wp-admin would leave it empty.
 *
 * **Only link to URLs that exist on a bare install.** Every link below resolves
 * against WooCommerce pages, the posts page, or a theme CPT archive. The
 * previous version pointed at product categories and marketing pages from one
 * project, and seven of its seventeen links 404'd on this install alone.
 *
 * Pages a project usually adds here once it has created them: Contact,
 * About, Delivery & Returns, Warranty, Trade Accounts, Careers. Add them when
 * the page exists, not in anticipation.
 */
?>
<!-- wp:group {"metadata":{"name":"Footer","patternName":"happywood/footer","categories":["footer"]},"className":"site-footer","style":{"spacing":{"margin":{"top":"0"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group site-footer has-white-background-color has-background" style="margin-top:0"><!-- wp:group {"metadata":{"name":"Footer Main"},"align":"wide","className":"site-footer__main","layout":{"type":"constrained"}} -->
    <div class="wp-block-group alignwide site-footer__main"><!-- wp:columns {"align":"wide","className":"site-footer__columns"} -->
        <div class="wp-block-columns alignwide site-footer__columns"><!-- wp:column {"width":"24%","className":"site-footer__brand"} -->
            <div class="wp-block-column site-footer__brand" style="flex-basis:24%"><!-- wp:group {"layout":{"type":"constrained"}} -->
                <div class="wp-block-group"><!-- wp:site-logo {"width":180,"shouldSyncIcon":false} /-->

                    <!-- wp:paragraph {"className":"site-footer__tagline is-style-text-lead","fontSize":"small"} -->
                    <p class="site-footer__tagline is-style-text-lead has-small-font-size">Commercial-grade products, supplied quickly and without sky-high prices.</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:social-links {"iconColor":"body","iconColorValue":"#16181B","openInNewTab":true,"size":"has-normal-icon-size","className":"site-footer__social is-style-logos-only","style":{"spacing":{"blockGap":{"left":"var:preset|spacing|small"}}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
                    <ul class="wp-block-social-links has-normal-icon-size has-icon-color site-footer__social is-style-logos-only"><!-- wp:social-link {"url":"https://www.facebook.com/","service":"facebook"} /-->

                        <!-- wp:social-link {"url":"https://www.instagram.com/","service":"instagram"} /-->

                        <!-- wp:social-link {"url":"https://www.linkedin.com/","service":"linkedin"} /-->

                        <!-- wp:social-link {"url":"https://www.youtube.com/","service":"youtube"} /--></ul>
                    <!-- /wp:social-links --></div>
                <!-- /wp:group --></div>
            <!-- /wp:column -->

            <!-- wp:column {"width":"","className":"site-footer__nav-column footer-nav__shop","style":{"spacing":{"blockGap":"var:preset|spacing|0"}}} -->
            <div class="wp-block-column site-footer__nav-column footer-nav__shop"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
                <p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color">Shop</p>
                <!-- /wp:paragraph -->

                <!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left","flexWrap":"wrap"}} -->
                <!-- wp:navigation-link {"label":"Shop All","url":"/shop/","kind":"custom","isTopLevelLink":true} /-->

                <!-- wp:navigation-link {"label":"Cart","url":"/cart/","kind":"custom","isTopLevelLink":true} /-->

                <!-- wp:navigation-link {"label":"Checkout","url":"/checkout/","kind":"custom","isTopLevelLink":true} /-->
                <!-- /wp:navigation --></div>
            <!-- /wp:column -->

            <!-- wp:column {"width":"","className":"site-footer__nav-column footer-nav__care","style":{"spacing":{"blockGap":"var:preset|spacing|0"}}} -->
            <div class="wp-block-column site-footer__nav-column footer-nav__care"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
                <p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color">Customer Care</p>
                <!-- /wp:paragraph -->

                <!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
                <!-- wp:navigation-link {"label":"FAQs","url":"/faq/","kind":"custom","isTopLevelLink":true} /-->

                <!-- wp:navigation-link {"label":"Showrooms","url":"/showrooms/","kind":"custom","isTopLevelLink":true} /-->

                <!-- wp:navigation-link {"label":"Track Order","url":"/my-account/orders/","kind":"custom","isTopLevelLink":true} /-->
                <!-- /wp:navigation --></div>
            <!-- /wp:column -->

            <!-- wp:column {"width":"","className":"site-footer__nav-column footer-nav__company","style":{"spacing":{"blockGap":"var:preset|spacing|0"}}} -->
            <div class="wp-block-column site-footer__nav-column footer-nav__company"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
                <p class="site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color">Company</p>
                <!-- /wp:paragraph -->

                <!-- wp:navigation {"overlayMenu":"never","className":"site-footer__nav","layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
                <!-- wp:navigation-link {"label":"Blog","url":"/blog/","kind":"custom","isTopLevelLink":true} /-->

                <!-- wp:navigation-link {"label":"My Account","url":"/my-account/","kind":"custom","isTopLevelLink":true} /-->
                <!-- /wp:navigation --></div>
            <!-- /wp:column -->

            <!-- wp:column {"width":"24%","className":"site-footer__locations"} -->
            <div class="wp-block-column site-footer__locations" style="flex-basis:24%"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained","justifyContent":"center","contentSize":"300px"}} -->
                <div class="wp-block-group"><!-- wp:paragraph {"className":"site-footer__nav-heading is-style-text-eyebrow","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}},"spacing":{"margin":{"bottom":"var:preset|spacing|x-small"}},"typography":{"textAlign":"center"}},"textColor":"body"} -->
                    <p class="has-text-align-center site-footer__nav-heading is-style-text-eyebrow has-body-color has-text-color has-link-color" style="margin-bottom:var(--wp--preset--spacing--x-small)">Multiple Locations Nationwide</p>
                    <!-- /wp:paragraph -->

                    <!-- wp:image {"width":"300px","sizeSlug":"full","linkDestination":"none","className":"site-footer__map","style":{"spacing":{"margin":{"top":"var:preset|spacing|small","bottom":"var:preset|spacing|small"}}}} -->
                    <figure class="wp-block-image size-full is-resized site-footer__map" style="margin-top:var(--wp--preset--spacing--small);margin-bottom:var(--wp--preset--spacing--small)"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/footer/footer-map.svg' ) ); ?>" alt="<?php esc_attr_e( 'Map of our showroom locations', 'happywood' ); ?>" style="width:300px"/></figure>
                    <!-- /wp:image --></div>
                <!-- /wp:group --></div>
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
                <p>Happy Wood. All rights reserved.</p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"site-footer__legal","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"center"}} -->
            <div class="wp-block-group site-footer__legal"><!-- wp:paragraph -->
                <p><a href="/privacy-policy/">Privacy Policy</a></p>
                <!-- /wp:paragraph -->

                <!-- wp:paragraph -->
                <p><a href="/terms-of-service/">Terms of Service</a></p>
                <!-- /wp:paragraph -->

                <!-- wp:paragraph -->
                <p><a href="/accessibility/">Accessibility</a></p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:group -->

            <!-- wp:group {"className":"site-footer__payments","layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"right"}} -->
            <div class="wp-block-group site-footer__payments"><!-- wp:image {"sizeSlug":"large","linkDestination":"none"} -->
                <figure class="wp-block-image size-large"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/footer/payment-cards.svg' ) ); ?>" alt="<?php esc_attr_e( 'Accepted payment methods', 'happywood' ); ?>"/></figure>
                <!-- /wp:image --></div>
            <!-- /wp:group --></div>
        <!-- /wp:group --></div>
    <!-- /wp:group --></div>
<!-- /wp:group -->
