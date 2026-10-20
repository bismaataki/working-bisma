<?php
/**
 * Title: FAQ Page CTA
 * Slug: happywood/faq-page-cta
 * Description: Dark “Still have questions?” band with contact buttons for the FAQ archive.
 * Categories: call-to-action, section, happywood
 * Keywords: faq, contact, support, cta
 */
?>
<!-- wp:group {"style":{"background":{"backgroundImage":{"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/happywood-art-1.png' ) ); ?>","id":0,"source":"file","title":"happywood-art-1"},"backgroundSize":"contain","backgroundRepeat":"no-repeat","backgroundPosition":"100% 0%"},"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"padding":{"top":"var:preset|spacing|section-xl","bottom":"var:preset|spacing|section-xl"}}},"backgroundColor":"body","textColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group has-white-color has-body-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--section-xl);padding-bottom:var(--wp--preset--spacing--section-xl)"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|medium"}},"layout":{"type":"constrained","contentSize":"30rem"}} -->
    <div class="wp-block-group"><!-- wp:heading {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"typography":{"textAlign":"center"}},"textColor":"white"} -->
        <h2 class="wp-block-heading has-text-align-center has-white-color has-text-color has-link-color">Still have questions?</h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"style":{"typography":{"textAlign":"center"}}} -->
        <p class="has-text-align-center">Our customer care team is ready to help \u2014 Monday to Friday, 9am to 5pm. We aim to respond to every enquiry within one business day.</p>
        <!-- /wp:paragraph --></div>
    <!-- /wp:group -->

    <!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
    <div class="wp-block-buttons"><!-- wp:button {"className":"is-style-ghost","happywoodIcon":"mail"} -->
        <div class="wp-block-button is-style-ghost hw-has-icon hw-icon-left hw-icon-mail"><a class="wp-block-button__link wp-element-button" href="/contact/">Contact Support</a></div>
        <!-- /wp:button -->

        <!-- wp:button {"className":"is-style-ghost","happywoodIcon":"phone"} -->
        <div class="wp-block-button is-style-ghost hw-has-icon hw-icon-left hw-icon-phone"><a class="wp-block-button__link wp-element-button" href="tel:REPLACE_ME">REPLACE_ME phone</a></div>
        <!-- /wp:button --></div>
    <!-- /wp:buttons --></div>
<!-- /wp:group -->
