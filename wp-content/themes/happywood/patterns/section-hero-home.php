<?php
/**
 * Title: Hero Home Banner
 * Slug: happywood/section-hero-home
 * Description: Homepage cover hero with headline, lead copy, and shop CTAs.
 * Categories: section, banner, happywood
 * Keywords: hero, home, cover, banner, marketing, landing
 */
?>

<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-16x9.png' ) ); ?>","alt":"Hero placeholder","dimRatio":0,"overlayColor":"primary","isUserOverlayColor":true,"focalPoint":{"x":0.49,"y":0.33},"minHeight":55,"minHeightUnit":"vh","isDark":false,"tagName":"section","sizeSlug":"full","metadata":{"name":"Hero Home Banner"},"align":"full","className":"pattern-section-hero-home","style":{"spacing":{"padding":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"layout":{"type":"constrained"},"hwHideBackgroundOnSmall":true,"hwHideBackgroundOnMedium":true} -->
<section class="wp-block-cover alignfull is-light pattern-section-hero-home hw-hide-bg-on-small hw-hide-bg-on-medium" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--0);min-height:55vh"><img class="wp-block-cover__image-background size-full" alt="Hero placeholder" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-16x9.png' ) ); ?>" style="object-position:49% 33%" data-object-fit="cover" data-object-position="49% 33%"/><span aria-hidden="true" class="wp-block-cover__background has-primary-background-color has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|0"}}},"isStackedOnTablet":true,"isPhoneReversed":true} -->
        <div class="wp-block-columns alignwide is-stacked-on-tablet hw-columns-reverse-on-mobile">

            <!-- wp:column {"verticalAlignment":"bottom","style":{"spacing":{"padding":{"top":"var:preset|spacing|xx-large","bottom":"var:preset|spacing|xx-large","left":"var:preset|spacing|xx-large","right":"var:preset|spacing|xx-large"},"blockGap":"var:preset|spacing|small"}},"backgroundColor":"background"} -->
            <div class="wp-block-column is-vertically-aligned-bottom has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--xx-large);padding-right:var(--wp--preset--spacing--xx-large);padding-bottom:var(--wp--preset--spacing--xx-large);padding-left:var(--wp--preset--spacing--xx-large)"><!-- wp:paragraph {"className":"is-style-text-eyebrow"} -->
                <p class="is-style-text-eyebrow">✦ New season · in stock</p>
                <!-- /wp:paragraph -->

                <!-- wp:heading {"level":1} -->
                <h1 class="wp-block-heading">Simple, honest quality for every space</h1>
                <!-- /wp:heading -->

                <!-- wp:paragraph {"className":"is-style-text-lead"} -->
                <p class="is-style-text-lead">Commercial-grade products designed to last, priced to move, and ready to ship nationwide.</p>
                <!-- /wp:paragraph -->

                <!-- wp:buttons {"className":"is-style-full-on-small","layout":{"type":"flex","justifyContent":"left"}} -->
                <div class="wp-block-buttons is-style-full-on-small"><!-- wp:button {"style":{"typography":{"textAlign":"center"}},"happywoodIcon":"arrow-right","happywoodIconPosition":"right"} -->
                    <div class="wp-block-button hw-has-icon hw-icon-right hw-icon-arrow-right"><a class="wp-block-button__link has-text-align-center wp-element-button" href="/shop/">Shop now</a></div>
                    <!-- /wp:button -->

                    <!-- wp:button {"className":"is-style-light"} -->
                    <div class="wp-block-button is-style-light"><a class="wp-block-button__link wp-element-button" href="/shop/">Browse the shop</a></div>
                    <!-- /wp:button --></div>
                <!-- /wp:buttons --></div>
            <!-- /wp:column -->

            <!-- wp:column -->
            <div class="wp-block-column"></div>
            <!-- /wp:column -->

            <!-- wp:column {"verticalAlignment":"stretch"} -->
            <div class="wp-block-column is-vertically-aligned-stretch"><!-- wp:group {"style":{"dimensions":{"minHeight":"25rem"},"background":{"backgroundImage":{"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-16x9.png' ) ); ?>","source":"file","title":"Hero placeholder"},"backgroundSize":"cover"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"right","verticalAlignment":"bottom"},"hwHideBackgroundOnLarge":true} -->
                <div class="wp-block-group hw-hide-bg-on-large" style="min-height:25rem"><!-- wp:group {"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}}},"spacing":{"padding":{"top":"var:preset|spacing|xx-small","bottom":"var:preset|spacing|xx-small","left":"var:preset|spacing|medium","right":"var:preset|spacing|medium"},"blockGap":"var:preset|spacing|0"}},"backgroundColor":"secondary","textColor":"white","layout":{"type":"constrained","justifyContent":"center"}} -->
                    <div class="wp-block-group has-white-color has-secondary-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--xx-small);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--xx-small);padding-left:var(--wp--preset--spacing--medium)"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"500"}}} -->
                        <p style="font-style:normal;font-weight:500">In stock now</p>
                        <!-- /wp:paragraph -->

                        <!-- wp:paragraph {"fontSize":"small"} -->
                        <p class="has-small-font-size">New lower prices</p>
                        <!-- /wp:paragraph --></div>
                    <!-- /wp:group --></div>
                <!-- /wp:group --></div>
            <!-- /wp:column --></div>
        <!-- /wp:columns --></div></section>
<!-- /wp:cover -->
