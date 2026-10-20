<?php
/**
 * Title: Page Meta
 * Slug: skaflow/component-page-meta
 * Description: Page meta row with last-updated date for title band patterns.
 * Categories: skaflow, component
 * Keywords: meta, page, date, updated, title
 */
?>
<!-- wp:group {"metadata":{"name":"Page Meta"},"style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group has-neutral-color has-text-color has-link-color"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"skaflow-user","hasNoIconFill":true,"width":"1rem"} -->
		<div class="wp-block-outermost-icon-block"><div class="icon-container has-no-icon-fill-color" style="width:1rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div></div>
		<!-- /wp:outermost/icon-block -->

		<!-- wp:post-author-name {"style":{"typography":{"textTransform":"capitalize","fontStyle":"normal","fontWeight":"400"},"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small"} /--></div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"skaflow-clock","hasNoIconFill":true,"width":"1rem"} -->
		<div class="wp-block-outermost-icon-block"><div class="icon-container has-no-icon-fill-color" style="width:1rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div></div>
		<!-- /wp:outermost/icon-block -->

		<!-- wp:post-time-to-read {"fontSize":"small"} /--></div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
