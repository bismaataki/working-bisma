<?php
/**
 * Product reviews — comment queries, rating stats, and shared card helpers.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base args for approved product review comments.
 *
 * @param int $product_id Product post ID.
 * @return array<string, mixed>
 */
function happywood_get_product_review_query_args( int $product_id ): array {
	return array(
		'post_id' => $product_id,
		'status'  => 'approve',
		'type'    => 'review',
		'orderby' => 'comment_date_gmt',
		'order'   => 'DESC',
	);
}

/**
 * Fetch paginated approved review comments for a product.
 *
 * @param int                  $product_id Product post ID.
 * @param array<string, mixed> $args       Optional overrides (number, offset, paged).
 * @return WP_Comment[]
 */
function happywood_get_product_review_comments( int $product_id, array $args = array() ): array {
	if ( $product_id <= 0 ) {
		return array();
	}

	$query_args = array_merge(
		happywood_get_product_review_query_args( $product_id ),
		$args
	);

	$comments = get_comments( $query_args );

	return is_array( $comments ) ? $comments : array();
}

/**
 * Count approved review comments for a product.
 *
 * @param int $product_id Product post ID.
 * @return int
 */
function happywood_get_product_review_comment_count( int $product_id ): int {
	if ( $product_id <= 0 ) {
		return 0;
	}

	return (int) get_comments(
		array_merge(
			happywood_get_product_review_query_args( $product_id ),
			array( 'count' => true )
		)
	);
}

/**
 * Average rating, total count, and per-star histogram.
 *
 * @param int $product_id Product post ID.
 * @return array{average: float, total: int, counts: array<int, int>}
 */
function happywood_get_product_review_rating_stats( int $product_id ): array {
	$counts = array(
		5 => 0,
		4 => 0,
		3 => 0,
		2 => 0,
		1 => 0,
	);

	$average = 0.0;
	$total   = 0;

	if ( $product_id <= 0 || ! function_exists( 'wc_get_product' ) ) {
		return array(
			'average' => $average,
			'total'   => $total,
			'counts'  => $counts,
		);
	}

	$product = wc_get_product( $product_id );

	if ( ! $product ) {
		return array(
			'average' => $average,
			'total'   => $total,
			'counts'  => $counts,
		);
	}

	$average = (float) $product->get_average_rating();
	$total   = (int) $product->get_review_count();

	$comments = happywood_get_product_review_comments( $product_id );

	foreach ( $comments as $comment ) {
		$rating = (int) get_comment_meta( $comment->comment_ID, 'rating', true );

		if ( $rating >= 1 && $rating <= 5 ) {
			++$counts[ $rating ];
		}
	}

	return array(
		'average' => $average,
		'total'   => $total,
		'counts'  => $counts,
	);
}

/**
 * Current review list page from query string (native cpage var).
 *
 * @return int
 */
function happywood_get_product_review_page(): int {
	$paged = get_query_var( 'cpage' );

	if ( ! $paged && isset( $_GET['cpage'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$paged = absint( wp_unslash( $_GET['cpage'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	return max( 1, (int) $paged );
}

/**
 * Whether a review comment is from a verified owner.
 *
 * @param int $comment_id Review comment ID.
 * @return bool
 */
function happywood_is_verified_product_review( int $comment_id ): bool {
	if ( $comment_id <= 0 ) {
		return false;
	}

	if ( function_exists( 'wc_review_is_from_verified_owner' ) ) {
		return (bool) wc_review_is_from_verified_owner( $comment_id );
	}

	return '1' === (string) get_comment_meta( $comment_id, 'verified', true );
}

/**
 * Split review comment content into card title and body.
 *
 * Uses the first sentence when possible; otherwise splits at ~60 characters.
 *
 * @param string $content Raw comment content.
 * @return array{title: string, body: string}
 */
function happywood_split_review_content_for_card( string $content ): array {
	$content = trim( wp_strip_all_tags( $content ) );

	if ( '' === $content ) {
		return array(
			'title' => '',
			'body'  => '',
		);
	}

	$max_title_length = 80;

	if ( preg_match( '/^(.+?[.!?])(?:\s+(.+))?$/su', $content, $matches ) ) {
		$title = trim( (string) $matches[1] );
		$body  = isset( $matches[2] ) ? trim( (string) $matches[2] ) : '';

		if ( mb_strlen( $title ) <= $max_title_length ) {
			return array(
				'title' => $title,
				'body'  => $body,
			);
		}
	}

	if ( mb_strlen( $content ) > 60 ) {
		$split_at = 60;

		if ( preg_match( '/\s/u', mb_substr( $content, 0, 60 ), $word_match, PREG_OFFSET_CAPTURE ) ) {
			$last_space = mb_strrpos( mb_substr( $content, 0, 60 ), ' ' );

			if ( false !== $last_space && $last_space > 20 ) {
				$split_at = $last_space;
			}
		}

		return array(
			'title' => trim( mb_substr( $content, 0, $split_at ) ),
			'body'  => trim( mb_substr( $content, $split_at ) ),
		);
	}

	return array(
		'title' => $content,
		'body'  => '',
	);
}
