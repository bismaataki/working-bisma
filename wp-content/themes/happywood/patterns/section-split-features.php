<?php
/**
 * Title: Split — Features
 * Slug: happywood/section-split-features
 * Description: Full-width media-text split with image on the right and a 2-column info-box grid on the left.
 * Categories: happywood, section
 * Keywords: split, features, media text, info box, two column
 */
?>
<!-- wp:group {"tagName":"section","metadata":{"name":"Split — Features"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-background-background-color has-background" style="padding-top:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--0)"><!-- wp:media-text {"align":"wide","mediaPosition":"right","mediaId":0,"mediaLink":"","linkDestination":"none","mediaType":"image","imageFill":false} -->
    <div class="wp-block-media-text alignwide has-media-on-the-right is-stacked-on-mobile"><div class="wp-block-media-text__content"><!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|xx-large","bottom":"var:preset|spacing|xx-large"}}},"layout":{"type":"constrained"}} -->
            <div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--xx-large);padding-bottom:var(--wp--preset--spacing--xx-large)"><!-- wp:paragraph {"className":"is-style-text-eyebrow-filled"} -->
                <p class="is-style-text-eyebrow-filled">Commercial Solutions</p>
                <!-- /wp:paragraph -->

                <!-- wp:heading -->
                <h2 class="wp-block-heading">My Heading Here</h2>
                <!-- /wp:heading -->

                <!-- wp:paragraph {"className":"is-style-text-lead"} -->
                <p class="is-style-text-lead">Change this text please as per the usage of the pattern in the page. This is a placeholder text to be replaced with actual content</p>
                <!-- /wp:paragraph -->

                <!-- wp:pattern {"slug":"happywood/info-box-bar-navy"} /-->

                <!-- wp:buttons -->
                <div class="wp-block-buttons"><!-- wp:button {"happywoodIcon":"arrow-right","happywoodIconPosition":"right"} -->
                    <div class="wp-block-button hw-has-icon hw-icon-right hw-icon-arrow-right"><a class="wp-block-button__link wp-element-button">Change Me</a></div>
                    <!-- /wp:button -->

                    <!-- wp:button {"className":"is-style-light"} -->
                    <div class="wp-block-button is-style-light"><a class="wp-block-button__link wp-element-button">Change Me</a></div>
                    <!-- /wp:button --></div>
                <!-- /wp:buttons --></div>
            <!-- /wp:group --></div><figure class="wp-block-media-text__media"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder.png' ) ); ?>" alt="placeholder"/></figure></div>
    <!-- /wp:media-text --></section>
<!-- /wp:group -->
