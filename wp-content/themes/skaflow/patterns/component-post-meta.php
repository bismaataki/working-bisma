<?php
/**
 * Title: Post Meta
 * Slug: skaflow/component-post-meta
 * Description: Post meta row with author, publish date, and estimated reading time.
 * Categories: skaflow, component
 * Keywords: meta, post, author, date, reading time
 */
?>
<!-- wp:group {"metadata":{"name":"Post Meta"},"style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group has-neutral-color has-text-color has-link-color"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"skaflow-user","hasNoIconFill":true,"width":"1rem"} -->
		<div class="wp-block-outermost-icon-block"><div class="icon-container has-no-icon-fill-color" style="width:1rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div></div>
		<!-- /wp:outermost/icon-block -->

		<!-- wp:post-author-name {"style":{"typography":{"textTransform":"capitalize","fontStyle":"normal","fontWeight":"400"},"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small"} /--></div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"wordpress-calendar","width":"1rem"} -->
		<div class="wp-block-outermost-icon-block"><div class="icon-container" style="width:1rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm.5 16c0 .3-.2.5-.5.5H5c-.3 0-.5-.2-.5-.5V7h15v12zM9 10H7v2h2v-2zm0 4H7v2h2v-2zm4-4h-2v2h2v-2zm4 0h-2v2h2v-2zm-4 4h-2v2h2v-2zm4 0h-2v2h2v-2z"></path></svg></div></div>
		<!-- /wp:outermost/icon-block -->

		<!-- wp:post-date {"format":"F j, Y","fontSize":"small"} /--></div>
	<!-- /wp:group -->

	<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xx-small"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
	<div class="wp-block-group"><!-- wp:outermost/icon-block {"iconName":"skaflow-clock","hasNoIconFill":true,"width":"1rem"} -->
		<div class="wp-block-outermost-icon-block"><div class="icon-container has-no-icon-fill-color" style="width:1rem;transform:rotate(0deg) scaleX(1) scaleY(1)"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg></div></div>
		<!-- /wp:outermost/icon-block -->

		<!-- wp:post-time-to-read {"fontSize":"small"} /--></div>
	<!-- /wp:group --></div>
<!-- /wp:group -->
