<?php
/**
 * Title: Page - Request a Quote
 * Slug: happywood/page-request-quote
 * Description: Request a Quote page with YITH form and quote summary sidebar.
 * Categories: page, happywood
 * Keywords: quote, request, yith, form, commerce
 * Template Types: page
 */
?>
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|section-l","bottom":"var:preset|spacing|section-l"},"margin":{"top":"var:preset|spacing|0","bottom":"var:preset|spacing|0"}}},"backgroundColor":"background","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull has-background-background-color has-background" style="margin-top:var(--wp--preset--spacing--0);margin-bottom:var(--wp--preset--spacing--0);padding-top:var(--wp--preset--spacing--section-l);padding-bottom:var(--wp--preset--spacing--section-l)"><!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|large"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide"><!-- wp:heading {"level":1,"align":"wide"} -->
		<h1 class="wp-block-heading alignwide">Request a Quote</h1>
		<!-- /wp:heading -->

		<!-- wp:shortcode -->
		[happywood_ywraq_notices]
		<!-- /wp:shortcode -->

		<!-- wp:columns {"align":"wide","className":"is-style-has-sidebar-right hw-request-quote-layout","isStackedOnTablet":true} -->
		<div class="wp-block-columns alignwide is-style-has-sidebar-right hw-request-quote-layout is-stacked-on-tablet"><!-- wp:column {"className":"hw-sidebar-main"} -->
			<div class="wp-block-column hw-sidebar-main"><!-- wp:group {"className":"hw-request-quote-panel hw-request-quote-form","style":{"spacing":{"padding":{"top":"var:preset|spacing|block-gap","bottom":"var:preset|spacing|block-gap","left":"var:preset|spacing|block-gap","right":"var:preset|spacing|block-gap"},"blockGap":"var:preset|spacing|large"},"border":{"width":"1px"}},"backgroundColor":"white","borderColor":"outline","layout":{"type":"constrained"}} -->
				<div class="wp-block-group hw-request-quote-panel hw-request-quote-form has-border-color has-outline-border-color has-white-background-color has-background" style="border-width:1px;padding-top:var(--wp--preset--spacing--block-gap);padding-right:var(--wp--preset--spacing--block-gap);padding-bottom:var(--wp--preset--spacing--block-gap);padding-left:var(--wp--preset--spacing--block-gap)"><!-- wp:shortcode -->
					[yith_ywraq_quote_request_form]
					<!-- /wp:shortcode -->

					<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"center"}} -->
					<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"happywood-clock","iconColor":"tertiary","iconColorValue":"#2E7D52","hasNoIconFill":true,"width":"0.9rem"} -->
						<div class="wp-block-outermost-icon-block"><div class="icon-container has-icon-color has-no-icon-fill-color has-tertiary-color" style="color:#2E7D52;width:0.9rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div></div>
						<!-- /wp:outermost/icon-block -->

						<!-- wp:paragraph {"className":"is-style-text-lead","fontSize":"small"} -->
						<p class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Average response time: 4-6 hours during business days', 'happywood' ); ?></p>
						<!-- /wp:paragraph --></div>
					<!-- /wp:group --></div>
				<!-- /wp:group --></div>
			<!-- /wp:column -->

			<!-- wp:column {"className":"hw-sidebar-aside"} -->
			<div class="wp-block-column hw-sidebar-aside"><!-- wp:group {"tagName":"aside","className":"hw-request-quote-panel hw-request-quote-summary","style":{"spacing":{"padding":{"top":"var:preset|spacing|block-gap","bottom":"var:preset|spacing|block-gap","left":"var:preset|spacing|block-gap","right":"var:preset|spacing|block-gap"},"blockGap":"var:preset|spacing|medium"},"border":{"width":"1px"}},"backgroundColor":"white","borderColor":"outline","layout":{"type":"constrained"}} -->
				<aside class="wp-block-group hw-request-quote-panel hw-request-quote-summary has-border-color has-outline-border-color has-white-background-color has-background" style="border-width:1px;padding-top:var(--wp--preset--spacing--block-gap);padding-right:var(--wp--preset--spacing--block-gap);padding-bottom:var(--wp--preset--spacing--block-gap);padding-left:var(--wp--preset--spacing--block-gap)"><!-- wp:heading {"level":2,"fontSize":"large"} -->
					<h2 class="wp-block-heading has-large-font-size"><?php esc_html_e( 'Quote Summary', 'happywood' ); ?></h2>
					<!-- /wp:heading -->

					<!-- wp:shortcode -->
					[yith_ywraq_request_quote_table show_back_to_shop="false" show_update_button="no" show_thumbnail="true" show_sku="true" show_single_price="true" show_quantity="true" show_line_total="false" show_totals="true"]
					<!-- /wp:shortcode --></aside>
				<!-- /wp:group --></div>
			<!-- /wp:column --></div>
		<!-- /wp:columns --></div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
