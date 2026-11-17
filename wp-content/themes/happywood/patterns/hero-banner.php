<?php
/**
 * Title: hero banner
 * Slug: happywood/hero-banner
 * Categories: 
 */
?>
<!-- wp:cover {"url":"<?php echo esc_url( get_template_directory_uri() ); ?>/assets/images/6387ced1-a94c-469b-b259-3b81fcbfcd78.png","dimRatio":20,"overlayColor":"muted","isUserOverlayColor":true,"focalPoint":{"x":0.5,"y":0.5},"minHeight":70,"minHeightUnit":"vh","contentPosition":"center center","isDark":false,"sizeSlug":"full","align":"full","layout":{"type":"constrained"}} -->
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