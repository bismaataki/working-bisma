<?php
/**
 * Shared blog post card block markup (Phase 5).
 *
 * Inner fields render via core blocks and do_blocks().
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Block markup for the post card (inner blocks only).
 *
 * Canonical source for `happywood/post-card` render via do_blocks().
 *
 * @param array<string, mixed> $args Block attribute configuration.
 * @return string Serialized block markup for do_blocks() / parse_blocks().
 */
function happywood_get_post_card_blocks_markup( array $args = array() ): string {
	$args = wp_parse_args(
		$args,
		array(
			'titleLevel'             => 3,
			'dateFormat'             => 'F j, Y',
			'showImage'              => true,
			'showCategory'           => true,
			'showSingleCategoryTerm' => true,
			'showReadingTime'        => true,
			'showExcerpt'            => true,
			'showAuthor'             => true,
			'showDate'               => true,
		)
	);

	$title_level = max( 2, min( 6, absint( $args['titleLevel'] ) ) );
	$date_format = sanitize_text_field( (string) $args['dateFormat'] );

	if ( '' === $date_format ) {
		$date_format = 'F j, Y';
	}

	$date_format_json = wp_json_encode( $date_format, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	$parts = array();

	if ( ! empty( $args['showImage'] ) ) {
		$parts[] = '<!-- wp:post-featured-image {"isLink":true,"className":"hw-post-card__media"} /-->';
	}

	$body_blocks = array();

	if ( ! empty( $args['showCategory'] ) || ! empty( $args['showReadingTime'] ) ) {
		$meta_blocks = array();

		if ( ! empty( $args['showCategory'] ) && ! empty( $args['showSingleCategoryTerm'] ) ) {
			$meta_blocks[] = '<!-- wp:happywood/taxonomy-terms {"taxonomy":"category","limit":1,"isLink":false,"className":"hw-post-card__categories"} /-->';
		} elseif ( ! empty( $args['showCategory'] ) ) {
			$meta_blocks[] = '<!-- wp:post-terms {"term":"category","className":"hw-post-card__categories"} /-->';
		}

		if ( ! empty( $args['showReadingTime'] ) ) {
			$meta_blocks[] = '<!-- wp:post-time-to-read {"className":"hw-post-card__reading-time"} /-->';
		}

		if ( ! empty( $meta_blocks ) ) {
			$body_blocks[] = '<!-- wp:group {"className":"hw-post-card__meta alignfull","layout":{"type":"flex","flexWrap":"wrap"}} -->
<div class="wp-block-group hw-post-card__meta alignfull">' . implode( "\n\n", $meta_blocks ) . '
</div>
<!-- /wp:group -->';
		}
	}

	$body_blocks[] = '<!-- wp:post-title {"level":' . $title_level . ',"isLink":true,"className":"hw-post-card__title alignfull"} /-->';

	if ( ! empty( $args['showExcerpt'] ) ) {
		$body_blocks[] = '<!-- wp:post-excerpt {"className":"hw-post-card__excerpt"} /-->';
	}

	if ( ! empty( $args['showAuthor'] ) || ! empty( $args['showDate'] ) ) {
		$footer_blocks = array(
			'<!-- wp:separator {"className":"hw-post-card__rule is-style-wide"} -->
<hr class="wp-block-separator has-alpha-channel-opacity is-style-wide hw-post-card__rule"/>
<!-- /wp:separator -->',
		);

		if ( ! empty( $args['showAuthor'] ) ) {
			$footer_blocks[] = '<!-- wp:post-author-name {"className":"hw-post-card__author","fontSize":"x-small"} /-->';
		}

		if ( ! empty( $args['showDate'] ) ) {
			$footer_blocks[] = '<!-- wp:post-date {"format":' . $date_format_json . ',"className":"hw-post-card__date"} /-->';
		}

		$body_blocks[] = '<!-- wp:group {"className":"hw-post-card__footer alignfull","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
<div class="wp-block-group hw-post-card__footer alignfull">' . implode( "\n\n", $footer_blocks ) . '
</div>
<!-- /wp:group -->';
	}

	$parts[] = '<!-- wp:group {"className":"hw-post-card__body","layout":{"type":"constrained"}} -->
<div class="wp-block-group hw-post-card__body">' . implode( "\n\n", $body_blocks ) . '
</div>
<!-- /wp:group -->';

	return implode( "\n\n", $parts );
}
