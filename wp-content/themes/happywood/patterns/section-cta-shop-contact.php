<?php
/**
 * Title: CTA - Shop and Contact
 * Slug: happywood/section-cta-shop-contact
 * Description: Split call-to-action band with Shop Now and Contact Us buttons on a dark background.
 * Categories: call-to-action, section, happywood
 * Keywords: cta, shop, contact, buttons, banner
 */
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"CTA Shop and Contact"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"}},"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"backgroundColor":"heading","textColor":"white","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-white-color has-heading-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:columns {"align":"wide"} -->
    <div class="wp-block-columns alignwide"><!-- wp:column -->
        <div class="wp-block-column"><!-- wp:heading {"level":2,"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}}},"textColor":"white"} -->
            <h2 class="wp-block-heading has-white-color has-text-color has-link-color">Ready to Transform Your Space?</h2>
            <!-- /wp:heading -->

            <!-- wp:paragraph {"style":{"typography":{"textAlign":"left"}}} -->
            <p class="has-text-align-left">Browse our full collection and bring your design ideas to life with quality pieces that last.</p>
            <!-- /wp:paragraph -->

            <!-- wp:buttons -->
            <div class="wp-block-buttons"><!-- wp:button {"className":"is-style-light","happywoodIcon":"shopping-cart"} -->
                <div class="wp-block-button is-style-light hw-has-icon hw-icon-left hw-icon-shopping-cart"><a class="wp-block-button__link wp-element-button" href="/shop/">Shop All Products</a></div>
                <!-- /wp:button --></div>
            <!-- /wp:buttons --></div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"is-style-bordered","style":{"elements":{"link":{"color":{"text":"var:preset|color|body"}}}},"textColor":"body"} -->
        <div class="wp-block-column is-style-bordered has-body-color has-text-color has-link-color"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group"><!-- wp:heading {"level":3} -->
                <h3 class="wp-block-heading">Need Expert Advice?</h3>
                <!-- /wp:heading -->

                <!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
                <p class="is-style-text-lead has-small-font-size">Our team is here to help you choose the right products for your space and style.</p>
                <!-- /wp:paragraph -->

                <!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|block-gap"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"left"}} -->
                <div class="wp-block-group"><!-- wp:list {"style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small","happywoodIcon":"check"} -->
                    <ul class="wp-block-list has-neutral-color has-text-color has-link-color has-small-font-size hw-has-icon hw-icon-left hw-icon-check"><!-- wp:list-item -->
                        <li>Free design consultation</li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li>Personalised recommendations</li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list -->

                    <!-- wp:list {"style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small","happywoodIcon":"check"} -->
                    <ul class="wp-block-list has-neutral-color has-text-color has-link-color has-small-font-size hw-has-icon hw-icon-left hw-icon-check"><!-- wp:list-item -->
                        <li>Space planning assistance</li>
                        <!-- /wp:list-item -->

                        <!-- wp:list-item -->
                        <li>Custom order solutions</li>
                        <!-- /wp:list-item --></ul>
                    <!-- /wp:list --></div>
                <!-- /wp:group -->

                <!-- wp:buttons {"className":"is-style-full-on-small","layout":{"type":"flex","justifyContent":"center"}} -->
                <div class="wp-block-buttons is-style-full-on-small"><!-- wp:button {"width":100,"style":{"typography":{"textAlign":"center"}},"happywoodIcon":"phone"} -->
                    <div class="wp-block-button has-custom-width wp-block-button__width-100 hw-has-icon hw-icon-left hw-icon-phone"><a class="wp-block-button__link has-text-align-center wp-element-button" href="/contact/">Contact us</a></div>
                    <!-- /wp:button --></div>
                <!-- /wp:buttons --></div>
            <!-- /wp:group --></div>
        <!-- /wp:column --></div>
    <!-- /wp:columns --></section>
<!-- /wp:group -->
