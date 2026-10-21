<?php
/**
 * Title: Page - Contact Us
 * Slug: happywood/page-contact
 * Description: Full Contact Us page with hero, Gravity Form, and trust info boxes.
 * Categories: page, happywood
 * Keywords: contact, page, form, support
 * Template Types: page
 */
?>
<!-- wp:pattern {"slug":"happywood/title-contact"} /-->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
	<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--large)"><!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-left","isStackedOnTablet":true} -->
		<div class="wp-block-columns alignwide is-style-has-sidebar-left is-stacked-on-tablet"><!-- wp:column {"className":"hw-sidebar-aside"} -->
			<div class="wp-block-column hw-sidebar-aside"><!-- wp:group {"tagName":"aside","style":{"spacing":{"blockGap":"var:preset|spacing|medium"}},"layout":{"type":"default"}} -->
				<aside class="wp-block-group"><!-- wp:heading {"level":6,"className":"is-style-text-eyebrow","style":{"typography":{"textTransform":"uppercase"}},"fontSize":"small"} -->
					<h6 class="wp-block-heading is-style-text-eyebrow has-small-font-size" style="text-transform:uppercase">Send Us a Message</h6>
					<!-- /wp:heading -->

					<!-- wp:heading -->
					<h2 class="wp-block-heading">Send Us a Message</h2>
					<!-- /wp:heading -->

					<!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
					<p class="is-style-text-lead has-small-font-size">Questions about products, pricing, or an order. The form is the fastest way to reach the team.</p>
					<!-- /wp:paragraph -->

					<!-- wp:pattern {"slug":"happywood/component-notice-dark"} /-->
					</aside>
				<!-- /wp:group --></div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"hw-sidebar-main is-style-default","style":{"spacing":{"blockGap":"var:preset|spacing|huge"}}} -->
			<div class="wp-block-column hw-sidebar-main is-style-default"><!-- wp:group {"className":"is-style-default","style":{"spacing":{"padding":{"top":"var:preset|spacing|block-gap","bottom":"var:preset|spacing|block-gap","left":"var:preset|spacing|block-gap","right":"var:preset|spacing|block-gap"},"blockGap":"var:preset|spacing|xxx-large"},"border":{"width":"1px"}},"backgroundColor":"white","borderColor":"outline","layout":{"type":"constrained"}} -->
				<div class="wp-block-group is-style-default has-border-color has-outline-border-color has-white-background-color has-background" style="border-width:1px;padding-top:var(--wp--preset--spacing--block-gap);padding-right:var(--wp--preset--spacing--block-gap);padding-bottom:var(--wp--preset--spacing--block-gap);padding-left:var(--wp--preset--spacing--block-gap)"><!-- wp:gravityforms/form {"formId":"1","title":false,"description":false,"ajax":true} /-->

					<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
					<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"happywood-clock","iconColor":"tertiary","iconColorValue":"#2E7D52","hasNoIconFill":true,"width":"0.9rem"} -->
						<div class="wp-block-outermost-icon-block"><div class="icon-container has-icon-color has-no-icon-fill-color has-tertiary-color" style="color:#2E7D52;width:0.9rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div></div>
						<!-- /wp:outermost/icon-block -->

						<!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
						<p class="is-style-text-lead has-small-font-size">Average response time: 4–6 hours during business days</p>
						<!-- /wp:paragraph --></div>
					<!-- /wp:group --></div>
				<!-- /wp:group -->

				<!-- wp:pattern {"slug":"happywood/info-box-contact"} /--></div>
			<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
