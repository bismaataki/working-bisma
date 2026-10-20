<?php
/**
 * Title: Title Dark Artwork
 * Slug: skaflow/title-dark-artwork
 * Description: Dark archive-style title band with artwork background for category and archive pages.
 * Categories: banner, skaflow
 * Keywords: title, dark, artwork, archive, banner
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-16x9.png' ) ); ?>","alt":"Title placeholder","isUserOverlayColor":true,"gradient":"midnight-navy-hero","sizeSlug":"large","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0","left":"var:preset|spacing|0","right":"var:preset|spacing|0"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull" style="padding-top:var(--wp--preset--spacing--0);padding-right:var(--wp--preset--spacing--0);padding-bottom:var(--wp--preset--spacing--0);padding-left:var(--wp--preset--spacing--0)"><img class="wp-block-cover__image-background size-large" alt="Title placeholder" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder-16x9.png' ) ); ?>" data-object-fit="cover"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-100 has-background-dim wp-block-cover__gradient-background has-background-gradient has-midnight-navy-hero-gradient-background"></span><div class="wp-block-cover__inner-container"><!-- wp:cover {"url":"<?php echo esc_url( get_theme_file_uri( 'assets/images/skaflow-art.svg' ) ); ?>","alt":"","dimRatio":0,"isUserOverlayColor":true,"focalPoint":{"x":0.82,"y":0.2},"isDark":false,"sizeSlug":"full","align":"full","className":"artwork-cover","layout":{"type":"constrained"}} -->
<div class="wp-block-cover alignfull is-light artwork-cover"><img class="wp-block-cover__image-background size-full" alt="" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/skaflow-art.svg' ) ); ?>" style="object-position:82% 20%" data-object-fit="cover" data-object-position="82% 20%"/><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:group {"layout":{"type":"flex","orientation":"vertical","justifyContent":"center"}} -->
        <div class="wp-block-group"><!-- wp:heading -->
            <h2 class="wp-block-heading"></h2>
            <!-- /wp:heading --><!-- wp:paragraph -->
            <p></p>
            <!-- /wp:paragraph --></div>
        <!-- /wp:group --></div></div>
<!-- /wp:cover --></div></div>
<!-- /wp:cover -->
