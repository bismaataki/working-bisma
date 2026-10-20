<?php
/**
 * Title: Related Posts
 * Slug: skaflow/section-related-posts
 * Description: Related-posts Query Loop grid for the single post template.
 * Categories: section, skaflow
 * Keywords: related, posts, blog, query, grid
 */
?>
<!-- wp:group {"metadata":{"name":"Related Posts"},"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-white-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","layout":{"type":"default"}} -->
    <div class="wp-block-group alignwide"><!-- wp:heading {"style":{"spacing":{"margin":{"top":"0","bottom":"var:preset|spacing|large"}}}} -->
        <h2 class="wp-block-heading" style="margin-top:0;margin-bottom:var(--wp--preset--spacing--large)">Related Posts</h2>
        <!-- /wp:heading -->

        <!-- wp:query {"queryId":0,"query":{"perPage":3,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"isRelated":true,"relatedTaxonomy":"category","relatedFallbackToDefault":true}} -->
        <div class="wp-block-query"><!-- wp:post-template {"style":{"spacing":{"blockGap":"var:preset|spacing|block-gap"}},"layout":{"type":"grid","columnCount":3}} -->
            <!-- wp:skaflow/post-card /-->
            <!-- /wp:post-template -->

            <!-- wp:query-no-results -->
            <!-- wp:paragraph {"placeholder":"Add text or blocks that will display when a query returns no results."} -->
            <p>No related articles could be found, please check <a href="/blogs/">all articles</a>.</p>
            <!-- /wp:paragraph -->
            <!-- /wp:query-no-results --></div>
        <!-- /wp:query --></div>
    <!-- /wp:group --></div>
<!-- /wp:group -->
