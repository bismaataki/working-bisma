<?php
/**
 * Title: Single Showrooms Default
 * Slug: happywood/single-showrooms-default
 * Categories: section, happywood
 * Description: Default layout inserted when a showroom single has no post content yet.
 * Post Types: showrooms
 * Keywords: showroom, single, default
 * Inserter: no
 */
?>
<!-- wp:cover {"useFeaturedImage":true,"isUserOverlayColor":true,"gradient":"azure-fade","contentPosition":"center center","align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim has-background-gradient has-azure-fade-gradient-background"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"align":"wide","layout":{"type":"flex","orientation":"vertical"}} -->
        <div class="wp-block-group alignwide"><!-- wp:group {"layout":{"type":"constrained","contentSize":"50rem"}} -->
            <div class="wp-block-group"><!-- wp:heading {"level":1} -->
                <h1 class="wp-block-heading">[happywood field="post_display_title"] Showroom</h1>
                <!-- /wp:heading -->

                <!-- wp:paragraph -->
                <p>Our largest and most comprehensive showroom in the heart of [happywood field="post_display_title"] . Experience over 5,000 square meters of premium product displays spanning every category we stock.</p>
                <!-- /wp:paragraph --></div>
            <!-- /wp:group -->

            <!-- wp:happywood/showroom-status-bar /--></div>
        <!-- /wp:group --></div></div>
<!-- /wp:cover -->

<!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
<div class="wp-block-group alignwide"><!-- wp:pattern {"slug":"happywood/showroom-gallery"} /--></div>
<!-- /wp:group -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:columns {"align":"wide","isStackedOnTablet":true} -->
    <div class="wp-block-columns alignwide is-stacked-on-tablet"><!-- wp:column {"className":"is-style-bordered"} -->
        <div class="wp-block-column is-style-bordered"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
            <div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"happywood-map-pin","iconColor":"secondary","iconColorValue":"#B3001B","hasNoIconFill":true,"width":"1.5rem"} -->
                <div class="wp-block-outermost-icon-block"><div class="icon-container has-icon-color has-no-icon-fill-color has-secondary-color" style="color:#B3001B;width:1.5rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg></div></div>
                <!-- /wp:outermost/icon-block -->

                <!-- wp:heading {"fontSize":"xx-large"} -->
                <h2 class="wp-block-heading has-xx-large-font-size">Location Details</h2>
                <!-- /wp:heading --></div>
            <!-- /wp:group -->

            <!-- wp:happywood/showroom-location-details /--></div>
        <!-- /wp:column -->

        <!-- wp:column {"className":"is-style-bordered"} -->
        <div class="wp-block-column is-style-bordered"><!-- wp:heading {"fontSize":"xx-large"} -->
            <h2 class="wp-block-heading has-xx-large-font-size">What to Expect at This Showroom</h2>
            <!-- /wp:heading -->

            <!-- wp:pattern {"slug":"happywood/info-box-list-surface"} /--></div>
        <!-- /wp:column --></div>
    <!-- /wp:columns --></div>
<!-- /wp:group -->

<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:heading {"style":{"typography":{"textAlign":"center"}}} -->
    <h2 class="wp-block-heading has-text-align-center">Find Us on the Map</h2>
    <!-- /wp:heading --></div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"is-style-text-lead","style":{"typography":{"textAlign":"center"}}} -->
<p class="has-text-align-center is-style-text-lead">Located at [happywood field="field_showrooms_address"] with excellent access<br>and parking.</p>
<!-- /wp:paragraph -->

<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-xl","bottom":"var:preset|spacing|section-xl"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--section-xl);padding-bottom:var(--wp--preset--spacing--section-xl)"><!-- wp:happywood/showroom-map-link /--></div>
<!-- /wp:group -->
