<?php
/**
 * Title: Sidebar
 * Slug: skaflow/sidebar
 * Description: Reusable sidebar aside wrapper for template parts and sidebar layouts.
 * Categories: component, skaflow
 * Keywords: sidebar, aside, widget, layout
 * Inserter: no
 */
?>
<!-- wp:group {"tagName":"aside","className":"is-style-default","layout":{"type":"default"}} -->
<aside class="wp-block-group is-style-default"><!-- wp:ht/block-toc {"headerEntries":"[]","mappingHeaders":[false,true,true,true,false,false],"isPost":true,"className":"is-style-outline"} -->
    <div class="wp-block-ht-block-toc is-style-outline htoc htoc--position-wide toc-list-style-plain" data-htoc-state="expanded" data-htoc-scrollspy="true" data-htoc-auto-collapse="true" data-htoc-mapping-headers="[false,true,true,true,false,false]" style="--kb-toc-font-size:14px;--kb-toc-title-font-size:16px"><span class="htoc__title"><span class="ht_toc_title">Table of Contents</span><span class="htoc__toggle"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"><g fill="#444"><path d="M15 7H1c-.6 0-1 .4-1 1s.4 1 1 1h14c.6 0 1-.4 1-1s-.4-1-1-1z"></path><path d="M15 1H1c-.6 0-1 .4-1 1s.4 1 1 1h14c.6 0 1-.4 1-1s-.4-1-1-1zM15 13H1c-.6 0-1 .4-1 1s.4 1 1 1h14c.6 0 1-.4 1-1s-.4-1-1-1z"></path></g></svg></span></span><div class="htoc__itemswrap"><ul class="ht_toc_list"></ul></div></div>
    <!-- /wp:ht/block-toc -->

    <!-- wp:skaflow/shop-the-look {"fallbackHeading":"Shop the Look","fallbackMessage":"Transform your space with these handpicked pieces.","fallbackProducts":[],"showViewAll":true,"viewAllLabel":"View All"} /-->


    <!-- wp:skaflow/info-box {"iconPosition":"top","alignment":"start","layout":{"type":"flex","orientation":"vertical"},"style":{"elements":{"link":{"color":{"text":"var:preset|color|white"}},"heading":{"color":{"text":"var:preset|color|white"}}},"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large","left":"var:preset|spacing|medium","right":"var:preset|spacing|medium"}}},"backgroundColor":"primary","textColor":"white"} -->
    <div class="wp-block-skaflow-info-box is-icon-top is-align-start has-white-color has-primary-background-color has-text-color has-background has-link-color" style="padding-top:var(--wp--preset--spacing--large);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--large);padding-left:var(--wp--preset--spacing--medium)"><!-- wp:outermost/icon-block {"iconName":"skaflow-mail","hasNoIconFill":true,"width":"4rem","lock":{"remove":true,"move":true},"className":"is-style-style-2"} -->
        <div class="wp-block-outermost-icon-block is-style-style-2"><div class="icon-container has-no-icon-fill-color" style="width:4rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m22 7-8.991 5.727a2 2 0 0 1-2.009 0L2 7"></path><rect x="2" y="4" width="20" height="16" rx="2"></rect></svg></div></div>
        <!-- /wp:outermost/icon-block -->

        <!-- wp:group {"lock":{"remove":true},"style":{"spacing":{"blockGap":"var:preset|spacing|x-small"}}} -->
        <div class="wp-block-group"><!-- wp:paragraph {"style":{"typography":{"fontStyle":"normal","fontWeight":"700"}},"fontSize":"large"} -->
            <p class="has-large-font-size" style="font-style:normal;font-weight:700">Design Tips Newsletter</p>
            <!-- /wp:paragraph -->

            <!-- wp:paragraph {"className":"is-style-default","fontSize":"small"} -->
            <p class="is-style-default has-small-font-size">Get weekly design tips and exclusive offers straight to your inbox.</p>
            <!-- /wp:paragraph -->

            <!-- wp:group {"className":"sk-form-subscribe","layout":{"type":"default"}} -->
            <div class="wp-block-group sk-form-subscribe"><!-- wp:gravityforms/form {"formId":"2","title":false,"description":false,"ajax":true} /--></div>
            <!-- /wp:group --></div>
        <!-- /wp:group --></div>
    <!-- /wp:skaflow/info-box -->


</aside>
<!-- /wp:group -->
