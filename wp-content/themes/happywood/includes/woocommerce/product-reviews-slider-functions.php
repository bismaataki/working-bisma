<?php
/**
 * Product reviews — verified-summary cache and marketing slider.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient key for site-wide verified review summary cache.
 */
const HAPPYWOOD_VERIFIED_REVIEW_SUMMARY_TRANSIENT = 'happywood_verified_review_summary';

/**
 * Delete the cached verified review summary.
 *
 * @return void
 */
function happywood_delete_verified_review_summary_cache(): void {
	delete_transient( HAPPYWOOD_VERIFIED_REVIEW_SUMMARY_TRANSIENT );
}

/**
 * Store the verified review summary in a transient.
 *
 * @param array{average: float, total: int} $data Summary data.
 * @return void
 */
function happywood_set_verified_review_summary_cache( array $data ): void {
	set_transient(
		HAPPYWOOD_VERIFIED_REVIEW_SUMMARY_TRANSIENT,
		array(
			'average' => (float) ( $data['average'] ?? 0 ),
			'total'   => (int) ( $data['total'] ?? 0 ),
		),
		DAY_IN_SECONDS
	);
}

/**
 * Calculate site-wide verified review summary from the database.
 *
 * @return array{average: float, total: int}
 */
function happywood_calculate_verified_review_summary(): array {
	global $wpdb;

	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT AVG(CAST(rating.meta_value AS DECIMAL(3,2))) AS average, COUNT(*) AS total
			FROM {$wpdb->comments} AS comments
			INNER JOIN {$wpdb->commentmeta} AS rating
				ON rating.comment_id = comments.comment_ID
				AND rating.meta_key = 'rating'
			INNER JOIN {$wpdb->commentmeta} AS verified
				ON verified.comment_id = comments.comment_ID
				AND verified.meta_key = 'verified'
				AND verified.meta_value = '1'
			WHERE comments.comment_approved = '1'
				AND comments.comment_type = %s",
			'review'
		),
		ARRAY_A
	);

	if ( ! is_array( $row ) ) {
		return array(
			'average' => 0.0,
			'total'   => 0,
		);
	}

	return array(
		'average' => round( (float) ( $row['average'] ?? 0 ), 1 ),
		'total'   => (int) ( $row['total'] ?? 0 ),
	);
}

/**
 * Get cached site-wide verified review summary.
 *
 * @return array{average: float, total: int}
 */
function happywood_get_verified_review_summary(): array {
	$cached = get_transient( HAPPYWOOD_VERIFIED_REVIEW_SUMMARY_TRANSIENT );

	if ( is_array( $cached ) && isset( $cached['average'], $cached['total'] ) ) {
		return array(
			'average' => (float) $cached['average'],
			'total'   => (int) $cached['total'],
		);
	}

	$data = happywood_calculate_verified_review_summary();
	happywood_set_verified_review_summary_cache( $data );

	return $data;
}

/**
 * Register hooks that invalidate the verified review summary cache.
 *
 * @return void
 */
function happywood_register_verified_review_summary_cache_hooks(): void {
	$invalidate = static function (): void {
		happywood_delete_verified_review_summary_cache();
	};

	add_action( 'comment_post', $invalidate );
	add_action( 'edit_comment', $invalidate );
	add_action( 'wp_set_comment_status', $invalidate );
	add_action( 'deleted_comment', $invalidate );
	add_action( 'trashed_comment', $invalidate );
	add_action( 'untrashed_comment', $invalidate );

	add_action(
		'updated_comment_meta',
		static function ( $meta_id, $comment_id, $meta_key ) use ( $invalidate ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( in_array( $meta_key, array( 'rating', 'verified' ), true ) ) {
				$invalidate();
			}
		},
		10,
		3
	);

	add_action(
		'added_comment_meta',
		static function ( $meta_id, $comment_id, $meta_key ) use ( $invalidate ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( in_array( $meta_key, array( 'rating', 'verified' ), true ) ) {
				$invalidate();
			}
		},
		10,
		3
	);

	add_action(
		'deleted_comment_meta',
		static function ( $meta_ids, $comment_id, $meta_key ) use ( $invalidate ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.Found
			if ( in_array( $meta_key, array( 'rating', 'verified' ), true ) ) {
				$invalidate();
			}
		},
		10,
		3
	);
}

add_action( 'init', 'happywood_register_verified_review_summary_cache_hooks' );

/**
 * Fetch verified product reviews for the marketing slider.
 *
 * @param array<string, mixed> $args {
 *     @type int      $limit         Number of reviews (4–20).
 *     @type string   $order_by      rating|date.
 *     @type bool     $verified_only Default true.
 *     @type int      $product_id    Optional single product filter.
 *     @type int[]    $category_ids  Optional product_cat term IDs.
 * }
 * @return WP_Comment[]
 */
function happywood_get_verified_reviews_for_slider( array $args ): array {
	$limit         = isset( $args['limit'] ) ? absint( $args['limit'] ) : 8;
	$limit         = max( 1, min( 20, $limit ) );
	$order_by      = isset( $args['order_by'] ) ? sanitize_key( (string) $args['order_by'] ) : 'rating';
	$verified_only = ! isset( $args['verified_only'] ) || (bool) $args['verified_only'];
	$product_id    = isset( $args['product_id'] ) ? absint( $args['product_id'] ) : 0;
	$category_ids  = isset( $args['category_ids'] ) && is_array( $args['category_ids'] )
		? array_values( array_filter( array_map( 'absint', $args['category_ids'] ) ) )
		: array();

	if ( ! in_array( $order_by, array( 'rating', 'date' ), true ) ) {
		$order_by = 'rating';
	}

	if ( ! happywood_is_product_reviews_enabled() ) {
		return array();
	}

	$post_in = happywood_get_product_ids_for_review_slider_filters( $product_id, $category_ids );

	if ( null === $post_in ) {
		return array();
	}

	$query_args = array(
		'type'   => 'review',
		'status' => 'approve',
		'number' => $limit * 4,
	);

	if ( ! empty( $post_in ) ) {
		$query_args['post__in'] = $post_in;
	}

	$meta_query = array(
		array(
			'key'     => 'rating',
			'compare' => 'EXISTS',
		),
	);

	if ( $verified_only ) {
		$meta_query[] = array(
			'key'     => 'verified',
			'value'   => '1',
			'compare' => '=',
		);
	}

	if ( count( $meta_query ) > 1 ) {
		$meta_query['relation'] = 'AND';
	}

	$query_args['meta_query'] = $meta_query;

	if ( 'date' === $order_by ) {
		$query_args['orderby'] = 'comment_date_gmt';
		$query_args['order']   = 'DESC';
	} else {
		$query_args['meta_key'] = 'rating';
		$query_args['orderby']  = 'meta_value_num';
		$query_args['order']    = 'DESC';
	}

	$comments = get_comments( $query_args );

	if ( ! is_array( $comments ) || empty( $comments ) ) {
		return array();
	}

	$results = array();

	foreach ( $comments as $comment ) {
		if ( ! $comment instanceof WP_Comment ) {
			continue;
		}

		if ( $verified_only && ! happywood_is_verified_product_review( (int) $comment->comment_ID ) ) {
			continue;
		}

		$results[] = $comment;

		if ( count( $results ) >= $limit ) {
			break;
		}
	}

	if ( 'rating' === $order_by && count( $results ) > 1 ) {
		usort(
			$results,
			static function ( WP_Comment $a, WP_Comment $b ): int {
				$rating_a = (int) get_comment_meta( $a->comment_ID, 'rating', true );
				$rating_b = (int) get_comment_meta( $b->comment_ID, 'rating', true );

				if ( $rating_a !== $rating_b ) {
					return $rating_b <=> $rating_a;
				}

				return strcmp( $b->comment_date_gmt, $a->comment_date_gmt );
			}
		);
	}

	return array_slice( $results, 0, $limit );
}

/**
 * Resolve product IDs for slider/category filters.
 *
 * @param int   $product_id   Optional single product ID.
 * @param int[] $category_ids Optional product_cat term IDs.
 * @return int[]|null Null when filters match no products.
 */
function happywood_get_product_ids_for_review_slider_filters( int $product_id, array $category_ids ): ?array {
	if ( $product_id > 0 ) {
		return 'product' === get_post_type( $product_id ) ? array( $product_id ) : null;
	}

	if ( empty( $category_ids ) ) {
		return array();
	}

	$term_ids = array();

	foreach ( $category_ids as $category_id ) {
		if ( $category_id <= 0 ) {
			continue;
		}

		$term_ids[] = $category_id;
		$children   = get_term_children( $category_id, 'product_cat' );

		if ( is_array( $children ) && ! empty( $children ) ) {
			$term_ids = array_merge( $term_ids, array_map( 'absint', $children ) );
		}
	}

	$term_ids = array_values( array_unique( array_filter( array_map( 'absint', $term_ids ) ) ) );

	if ( empty( $term_ids ) ) {
		return null;
	}

	$product_ids = get_objects_in_term( $term_ids, 'product_cat' );

	if ( ! is_array( $product_ids ) || empty( $product_ids ) ) {
		return null;
	}

	return array_values( array_unique( array_map( 'absint', $product_ids ) ) );
}

/**
 * Parse product-reviews-slider block attributes into query/render args.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return array<string, mixed>
 */
function happywood_parse_product_reviews_slider_block_args( array $attributes ): array {
	$order_by = isset( $attributes['orderBy'] ) ? sanitize_key( (string) $attributes['orderBy'] ) : 'rating';

	if ( ! in_array( $order_by, array( 'rating', 'date' ), true ) ) {
		$order_by = 'rating';
	}

	$category_ids = array();

	if ( ! empty( $attributes['categoryIds'] ) && is_array( $attributes['categoryIds'] ) ) {
		foreach ( $attributes['categoryIds'] as $term_row ) {
			if ( is_array( $term_row ) && ! empty( $term_row['id'] ) ) {
				$category_ids[] = absint( $term_row['id'] );
			} elseif ( is_numeric( $term_row ) ) {
				$category_ids[] = absint( $term_row );
			}
		}
	}

	return array(
		'limit'               => max( 4, min( 20, absint( $attributes['reviewsToShow'] ?? 8 ) ) ),
		'order_by'            => $order_by,
		'verified_only'       => ! isset( $attributes['verifiedOnly'] ) || (bool) $attributes['verifiedOnly'],
		'product_id'          => absint( $attributes['productId'] ?? 0 ),
		'category_ids'        => array_values( array_filter( $category_ids ) ),
		'show_summary'        => ! isset( $attributes['showSummary'] ) || (bool) $attributes['showSummary'],
		'show_arrows_desktop' => ! isset( $attributes['showArrowsDesktop'] ) || (bool) $attributes['showArrowsDesktop'],
		'show_arrows_mobile'  => isset( $attributes['showArrowsMobile'] ) && (bool) $attributes['showArrowsMobile'],
		'show_progress_bar'   => ! isset( $attributes['showProgressBar'] ) || (bool) $attributes['showProgressBar'],
		'card'                => array(
			'showReviewRating'  => ! isset( $attributes['showReviewRating'] ) || (bool) $attributes['showReviewRating'],
			'showReviewerName'  => ! isset( $attributes['showReviewerName'] ) || (bool) $attributes['showReviewerName'],
			'showReviewContent' => ! isset( $attributes['showReviewContent'] ) || (bool) $attributes['showReviewContent'],
			'showProductName'   => ! isset( $attributes['showProductName'] ) || (bool) $attributes['showProductName'],
			'showReviewDate'    => isset( $attributes['showReviewDate'] ) && (bool) $attributes['showReviewDate'],
		),
	);
}

/**
 * Get verified review summary for optional product/category scope.
 *
 * @param array<string, mixed> $scope {
 *     @type int   $product_id
 *     @type int[] $category_ids
 * }
 * @return array{average: float, total: int}
 */
function happywood_get_verified_review_summary_for_scope( array $scope = array() ): array {
	$product_id   = isset( $scope['product_id'] ) ? absint( $scope['product_id'] ) : 0;
	$category_ids = isset( $scope['category_ids'] ) && is_array( $scope['category_ids'] )
		? array_values( array_filter( array_map( 'absint', $scope['category_ids'] ) ) )
		: array();

	if ( $product_id <= 0 && empty( $category_ids ) ) {
		return happywood_get_verified_review_summary();
	}

	$post_in = happywood_get_product_ids_for_review_slider_filters( $product_id, $category_ids );

	if ( null === $post_in ) {
		return array(
			'average' => 0.0,
			'total'   => 0,
		);
	}

	return happywood_calculate_verified_review_summary_for_posts( $post_in );
}

/**
 * Calculate verified review summary for a set of products (or site-wide).
 *
 * @param int[] $post_in Product post IDs; empty = all products.
 * @return array{average: float, total: int}
 */
function happywood_calculate_verified_review_summary_for_posts( array $post_in = array() ): array {
	global $wpdb;

	$post_clause = '';
	$prepare     = array( 'review' );

	if ( ! empty( $post_in ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $post_in ), '%d' ) );
		$post_clause  = " AND comments.comment_post_ID IN ( {$placeholders} )";
		$prepare      = array_merge( $prepare, array_map( 'absint', $post_in ) );
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $post_clause is only %d placeholders; values are in $prepare.
	$row = $wpdb->get_row(
		$wpdb->prepare(
			"SELECT AVG(CAST(rating.meta_value AS DECIMAL(3,2))) AS average, COUNT(*) AS total
			FROM {$wpdb->comments} AS comments
			INNER JOIN {$wpdb->commentmeta} AS rating
				ON rating.comment_id = comments.comment_ID
				AND rating.meta_key = 'rating'
			INNER JOIN {$wpdb->commentmeta} AS verified
				ON verified.comment_id = comments.comment_ID
				AND verified.meta_key = 'verified'
				AND verified.meta_value = '1'
			WHERE comments.comment_approved = '1'
				AND comments.comment_type = %s{$post_clause}",
			...$prepare
		),
		ARRAY_A
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	if ( ! is_array( $row ) ) {
		return array(
			'average' => 0.0,
			'total'   => 0,
		);
	}

	return array(
		'average' => round( (float) ( $row['average'] ?? 0 ), 1 ),
		'total'   => (int) ( $row['total'] ?? 0 ),
	);
}

/**
 * Render verified reviews summary markup.
 *
 * @param array{average: float, total: int} $summary Summary data.
 * @return string
 */
function happywood_render_verified_reviews_summary_markup( array $summary ): string {
	$average = (float) ( $summary['average'] ?? 0 );
	$total   = (int) ( $summary['total'] ?? 0 );

	if ( $total <= 0 ) {
		return '';
	}

	$rating_html = happywood_get_average_rating_stars_markup( $average, $total );

	ob_start();
	?>
	<div class="hw-verified-reviews-summary">
		<?php if ( '' !== $rating_html ) : ?>
			<div class="hw-verified-reviews-summary__stars" aria-hidden="true">
				<?php echo $rating_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>
		<p class="hw-verified-reviews-summary__text">
			<?php
			echo esc_html(
				sprintf(
					/* translators: 1: average rating, 2: verified review count */
					__( '%1$s out of 5 · %2$s verified reviews', 'happywood' ),
					number_format_i18n( $average, 1 ),
					number_format_i18n( $total )
				)
			);
			?>
		</p>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render the verified-reviews-summary block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function happywood_render_verified_reviews_summary_block( array $attributes ): string {
	if ( ! happywood_is_product_reviews_enabled() ) {
		return '';
	}

	$summary = happywood_get_verified_review_summary();
	$markup  = happywood_render_verified_reviews_summary_markup( $summary );

	if ( '' === trim( $markup ) ) {
		return '';
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => 'hw-verified-reviews-summary-block',
		)
	);

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$markup
	);
}

/**
 * Build aggregate summary markup for the product reviews slider.
 *
 * @param array{average: float, total: int} $summary Summary data.
 * @return string
 */
function happywood_get_verified_review_summary_display( array $summary ): string {
	return happywood_render_verified_reviews_summary_markup( $summary );
}

/**
 * Normalize product reviews slider display options.
 *
 * @param array<string, mixed> $args Raw options.
 * @return array{
 *     show_arrows_desktop: bool,
 *     show_arrows_mobile: bool,
 *     show_progress_bar: bool,
 *     instance_id: string,
 *     card: array<string, mixed>,
 * }
 */
function happywood_normalize_product_reviews_slider_args( array $args = array() ): array {
	$instance_id = isset( $args['instance_id'] )
		? sanitize_html_class( (string) $args['instance_id'] )
		: 'hw-product-reviews-slider';

	if ( '' === $instance_id ) {
		$instance_id = 'hw-product-reviews-slider';
	}

	return array(
		'show_arrows_desktop' => ! isset( $args['showArrowsDesktop'] ) || (bool) $args['showArrowsDesktop'],
		'show_arrows_mobile'  => isset( $args['showArrowsMobile'] ) && (bool) $args['showArrowsMobile'],
		'show_progress_bar'   => ! isset( $args['showProgressBar'] ) || (bool) $args['showProgressBar'],
		'instance_id'         => $instance_id,
		'card'                => isset( $args['card'] ) && is_array( $args['card'] ) ? $args['card'] : array(),
	);
}

/**
 * Render one review slide for the product reviews slider.
 *
 * @param WP_Comment           $comment Review comment.
 * @param array<string, mixed> $options Display options.
 * @return string
 */
function happywood_get_product_reviews_slider_slide_html( WP_Comment $comment, array $options ): string {
	$card_args = isset( $options['card'] ) && is_array( $options['card'] ) ? $options['card'] : array();
	$card_html = happywood_render_review_card_for_comment( (int) $comment->comment_ID, $card_args );

	if ( '' === trim( $card_html ) ) {
		return '';
	}

	$slide_class = 'swiper-slide hw-product-reviews-slider__slide';

	if ( ! empty( $options['static_slide'] ) ) {
		$slide_class .= ' hw-product-reviews-slider__slide--static';
	}

	return sprintf(
		'<div class="%1$s">%2$s</div>',
		esc_attr( $slide_class ),
		$card_html
	);
}

/**
 * Render the product reviews slider progress bar markup.
 *
 * @param bool $preview Static preview bar (editor SSR).
 * @return string
 */
function happywood_get_product_reviews_slider_scrollbar_html( bool $preview = false ): string {
	if ( $preview ) {
		return '<div class="hw-product-reviews-slider__scrollbar-wrap hw-product-reviews-slider__scrollbar-wrap--preview"><div class="hw-product-reviews-slider__scrollbar hw-product-reviews-slider__scrollbar--preview" aria-hidden="true"></div></div>';
	}

	return '<div class="hw-product-reviews-slider__scrollbar-wrap"><div class="swiper-scrollbar hw-product-reviews-slider__scrollbar" aria-hidden="true"></div></div>';
}

/**
 * Render swiper navigation buttons for the product reviews slider header.
 *
 * @return string
 */
function happywood_get_product_reviews_slider_nav_html(): string {
	ob_start();
	?>
	<div class="hw-product-reviews-slider__nav" aria-hidden="false">
		<button
			type="button"
			class="hw-product-reviews-slider__arrow hw-product-reviews-slider__arrow--prev"
			aria-label="<?php esc_attr_e( 'Previous reviews', 'happywood' ); ?>"
		></button>
		<button
			type="button"
			class="hw-product-reviews-slider__arrow hw-product-reviews-slider__arrow--next"
			aria-label="<?php esc_attr_e( 'Next reviews', 'happywood' ); ?>"
		></button>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render product reviews slider markup.
 *
 * @param WP_Comment[]         $comments Review comments.
 * @param array<string, mixed> $args     Display options.
 * @return string
 */
function happywood_get_product_reviews_slider_html( array $comments, array $args = array() ): string {
	if ( empty( $comments ) ) {
		return '';
	}

	$options = happywood_normalize_product_reviews_slider_args( $args );

	ob_start();
	?>
	<div class="hw-product-reviews-slider__track">
		<div class="swiper hw-product-reviews-slider__swiper">
			<div class="swiper-wrapper">
				<?php
				foreach ( $comments as $comment ) {
					if ( $comment instanceof WP_Comment ) {
						echo happywood_get_product_reviews_slider_slide_html( $comment, $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
				}
				?>
			</div>
			<?php if ( $options['show_progress_bar'] ) : ?>
				<?php echo happywood_get_product_reviews_slider_scrollbar_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render a static flex row of review cards (editor preview — no Swiper JS).
 *
 * @param WP_Comment[]         $comments Review comments.
 * @param array<string, mixed> $args     Display options.
 * @return string
 */
function happywood_get_product_reviews_slider_flex_list_html( array $comments, array $args = array() ): string {
	if ( empty( $comments ) ) {
		return '';
	}

	$options                 = happywood_normalize_product_reviews_slider_args( $args );
	$options['static_slide'] = true;

	ob_start();
	?>
	<div class="hw-product-reviews-slider__track">
		<div class="hw-product-reviews-slider__flex-list">
			<?php
			foreach ( $comments as $comment ) {
				if ( $comment instanceof WP_Comment ) {
					echo happywood_get_product_reviews_slider_slide_html( $comment, $options ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
			}
			?>
		</div>
		<?php if ( $options['show_progress_bar'] ) : ?>
			<?php echo happywood_get_product_reviews_slider_scrollbar_html( true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render placeholder slides for editor preview.
 *
 * @param int $count Number of placeholders.
 * @return string
 */
function happywood_get_product_reviews_slider_placeholder_slides_html( int $count = 3 ): string {
	$count  = max( 1, min( 5, $count ) );
	$slides = '';

	for ( $i = 0; $i < $count; $i++ ) {
		$slides .= sprintf(
			'<div class="hw-product-reviews-slider__slide hw-product-reviews-slider__slide--static">%s</div>',
			happywood_get_review_card_editor_placeholder_html()
		);
	}

	return sprintf(
		'<div class="hw-product-reviews-slider__track"><div class="hw-product-reviews-slider__flex-list">%s</div>%s</div>',
		$slides,
		happywood_get_product_reviews_slider_scrollbar_html( true )
	);
}

/**
 * Get placeholder review comments for editor preview when no verified reviews exist.
 *
 * @param int $limit Number of placeholders.
 * @return WP_Comment[]
 */
function happywood_get_product_reviews_slider_preview_comments( int $limit = 3 ): array {
	$comments = get_comments(
		array(
			'type'   => 'review',
			'status' => 'approve',
			'number' => max( 1, $limit ),
		)
	);

	return is_array( $comments ) ? $comments : array();
}
