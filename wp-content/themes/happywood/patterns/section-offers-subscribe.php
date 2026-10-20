<?php
/**
 * Title: Offers Subscribe
 * Slug: happywood/section-offers-subscribe
 * Description: Newsletter subscribe section with offer copy and Gravity Forms embed.
 * Categories: section, happywood, call-to-action
 * Keywords: newsletter, subscribe, offers, form, email
 */
?>
<!-- wp:group {"metadata":{"name":"CTA Offers Subscribe"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|small"}},"layout":{"type":"constrained","contentSize":"40rem"}} -->
    <div class="wp-block-group alignwide"><!-- wp:outermost/icon-block {"iconName":"happywood-mail","itemsJustification":"center","hasNoIconFill":true,"width":"4rem","className":"is-style-style-3"} -->
        <div class="wp-block-outermost-icon-block items-justified-center is-style-style-3"><div class="icon-container has-no-icon-fill-color" style="width:4rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg></div></div>
        <!-- /wp:outermost/icon-block -->

        <!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
        <h2 class="wp-block-heading has-text-align-center">Get Exclusive Offers &amp; Updates</h2>
        <!-- /wp:heading -->

        <!-- wp:paragraph {"className":"is-style-text-lead","style":{"typography":{"textAlign":"center"}}} -->
        <p class="has-text-align-center is-style-text-lead">Subscribe to receive special promotions, new product launches, and styling tips delivered to your inbox.</p>
        <!-- /wp:paragraph -->

        <!-- wp:group {"className":"hw-form-subscribe","layout":{"type":"default"}} -->
        <div class="wp-block-group hw-form-subscribe"><!-- wp:gravityforms/form {"formId":"2","title":false,"description":false} /--></div>
        <!-- /wp:group -->

        <!-- wp:list {"className":"is-style-horizontal-center","style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small","happywoodIcon":"check","happywoodIconColor":"tertiary"} -->
        <ul class="wp-block-list is-style-horizontal-center has-neutral-color has-text-color has-link-color has-small-font-size hw-has-icon hw-icon-left hw-icon-check hw-icon-has-color-tertiary"><!-- wp:list-item -->
            <li>Early access to sales</li>
            <!-- /wp:list-item -->

            <!-- wp:list-item -->
            <li>Design inspiration</li>
            <!-- /wp:list-item -->

            <!-- wp:list-item -->
            <li>Unsubscribe anytime</li>
            <!-- /wp:list-item --></ul>
        <!-- /wp:list --></div>
    <!-- /wp:group --></div>
<!-- /wp:group -->
