<?php
/**
 * Review card block helpers (Phase 4b product reviews slider).
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build inline block comment markup for a review card (for do_blocks).
 *
 * @param int                  $comment_id Review comment ID.
 * @param array<string, mixed> $args       Block attribute overrides.
 * @return string
 */
function skaflow_get_review_card_blocks_markup( int $comment_id, array $args = array() ): string {
	if ( $comment_id <= 0 ) {
		return '';
	}

	$defaults = array(
		'commentId' => $comment_id,
		'variant'   => 'carousel',
		'className' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$block_attrs = array(
		'commentId' => absint( $comment_id ),
		'variant'   => sanitize_key( (string) $args['variant'] ),
	);

	$class_name = trim( (string) $args['className'] );

	if ( '' !== $class_name ) {
		$block_attrs['className'] = $class_name;
	}

	$json = wp_json_encode( $block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	return sprintf( '<!-- wp:skaflow/review-card %s /-->', $json );
}

/**
 * Enqueue review-card block styles when rendered outside a saved post context.
 *
 * @return void
 */
function skaflow_enqueue_review_card_assets(): void {
	static $enqueued = false;

	if ( $enqueued || ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'skaflow/review-card' );

	if ( $block_type && ! empty( $block_type->style ) ) {
		wp_enqueue_style( $block_type->style );
	}

	skaflow_enqueue_review_rating_assets();

	$enqueued = true;
}

/**
 * Whether the current request is a block editor server render.
 *
 * @return bool
 */
function skaflow_is_review_card_editor_preview(): bool {
	return ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/**
 * Resolve the review comment ID for card rendering.
 *
 * Order: block attribute → REST SSR query arg.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return int
 */
function skaflow_resolve_review_card_comment_id( array $attributes ): int {
	if ( ! empty( $attributes['commentId'] ) ) {
		return absint( $attributes['commentId'] );
	}

	if ( skaflow_is_review_card_editor_preview() && ! empty( $_GET['comment_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( wp_unslash( $_GET['comment_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	return 0;
}

/**
 * Render a review card for a specific comment ID via do_blocks().
 *
 * @param int                  $comment_id Review comment ID.
 * @param array<string, mixed> $args       Block attribute overrides.
 * @return string
 */
function skaflow_render_review_card_for_comment( int $comment_id, array $args = array() ): string {
	if ( $comment_id <= 0 ) {
		return '';
	}

	$comment = get_comment( $comment_id );

	if ( ! $comment instanceof WP_Comment || 'review' !== $comment->comment_type ) {
		return '';
	}

	$verified_only = true;

	if ( isset( $args['verifiedOnly'] ) ) {
		$verified_only = (bool) $args['verifiedOnly'];
	} elseif ( isset( $args['verified_only'] ) ) {
		$verified_only = (bool) $args['verified_only'];
	}

	if ( $verified_only && ! skaflow_is_verified_product_review( $comment_id ) ) {
		return '';
	}

	skaflow_enqueue_review_card_assets();

	$args['commentId'] = $comment_id;

	return do_blocks( skaflow_get_review_card_blocks_markup( $comment_id, $args ) );
}

/**
 * Render the review card block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param WP_Block|null        $block      Block instance.
 * @return string
 */
function skaflow_render_review_card( array $attributes, $block = null ): string {
	$comment_id = skaflow_resolve_review_card_comment_id( $attributes );

	if ( $comment_id <= 0 ) {
		if ( skaflow_is_review_card_editor_preview() ) {
			return skaflow_get_review_card_editor_placeholder_html();
		}

		return '';
	}

	$comment = get_comment( $comment_id );

	if ( ! $comment instanceof WP_Comment || 'review' !== $comment->comment_type || ! in_array( (string) $comment->comment_approved, array( '1', 'approve' ), true ) ) {
		if ( skaflow_is_review_card_editor_preview() ) {
			return skaflow_get_review_card_editor_placeholder_html();
		}

		return '';
	}

	$verified_only = ! isset( $attributes['verifiedOnly'] ) || (bool) $attributes['verifiedOnly'];

	if ( $verified_only && ! skaflow_is_verified_product_review( $comment_id ) ) {
		if ( skaflow_is_review_card_editor_preview() ) {
			return skaflow_get_review_card_editor_placeholder_html();
		}

		return '';
	}

	$show_rating  = ! isset( $attributes['showReviewRating'] ) || (bool) $attributes['showReviewRating'];
	$show_author  = ! isset( $attributes['showReviewerName'] ) || (bool) $attributes['showReviewerName'];
	$show_content = ! isset( $attributes['showReviewContent'] ) || (bool) $attributes['showReviewContent'];
	$show_product = ! isset( $attributes['showProductName'] ) || (bool) $attributes['showProductName'];
	$show_date    = isset( $attributes['showReviewDate'] ) && (bool) $attributes['showReviewDate'];

	$variant = isset( $attributes['variant'] ) ? sanitize_key( (string) $attributes['variant'] ) : 'carousel';

	if ( ! in_array( $variant, array( 'carousel', 'single' ), true ) ) {
		$variant = 'carousel';
	}

	$rating        = (int) get_comment_meta( $comment_id, 'rating', true );
	$parts         = skaflow_split_review_content_for_card( (string) $comment->comment_content );
	$author        = get_comment_author( $comment );
	$product_id    = (int) $comment->comment_post_ID;
	$product_title = '';

	if ( $product_id > 0 ) {
		$product_title = get_the_title( $product_id );
	}

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class' => sprintf( 'sk-review-card sk-review-card--%s', esc_attr( $variant ) ),
		)
	);

	ob_start();
	?>
	<article <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="sk-review-card__content">
			<?php if ( $show_rating && $rating > 0 ) : ?>
				<?php echo skaflow_get_product_review_card_rating_markup( $rating ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>

			<?php if ( $show_content && '' !== $parts['title'] ) : ?>
				<h3 class="sk-review-card__title"><?php echo esc_html( $parts['title'] ); ?></h3>
			<?php endif; ?>

			<?php if ( $show_content && '' !== $parts['body'] ) : ?>
				<div class="sk-review-card__body">
					<p><?php echo esc_html( $parts['body'] ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( $show_author || ( $show_product && '' !== $product_title ) || $show_date ) : ?>
		<footer class="sk-review-card__footer">
			<?php if ( $show_content && ( $show_author || ( $show_product && '' !== $product_title ) || $show_date ) ) : ?>
			<hr class="sk-review-card__rule" />
			<?php endif; ?>

			<?php if ( $show_author ) : ?>
				<p class="sk-review-card__author"><?php echo esc_html( $author ); ?></p>
			<?php endif; ?>

			<?php if ( $show_date ) : ?>
				<p class="sk-review-card__date">
					<time datetime="<?php echo esc_attr( get_comment_date( 'c', $comment ) ); ?>">
						<?php echo esc_html( get_comment_date( 'F j, Y', $comment ) ); ?>
					</time>
				</p>
			<?php endif; ?>

			<?php if ( $show_product && '' !== $product_title ) : ?>
				<p class="sk-review-card__verified">
					<span class="sk-review-card__verified-icon" aria-hidden="true"></span>
					<span class="sk-review-card__verified-text">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: product title in uppercase */
								__( 'VERIFIED • %s', 'skaflow' ),
								mb_strtoupper( $product_title )
							)
						);
						?>
					</span>
				</p>
			<?php endif; ?>
		</footer>
		<?php endif; ?>
	</article>
	<?php

	return (string) ob_get_clean();
}

/**
 * Static placeholder card for the block editor when no comment is selected.
 *
 * @return string
 */
function skaflow_get_review_card_editor_placeholder_html(): string {
	ob_start();
	?>
	<article class="sk-review-card sk-review-card--carousel sk-review-card--placeholder">
		<div class="sk-review-card__content">
			<?php echo skaflow_get_product_review_card_rating_markup( 5 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<h3 class="sk-review-card__title"><?php esc_html_e( 'Exactly what we needed', 'skaflow' ); ?></h3>
			<div class="sk-review-card__body">
				<p><?php esc_html_e( 'We ordered in bulk for a new workspace and couldn\'t be happier. Great quality, fast delivery, and the volume discount made it easy. Highly recommend.', 'skaflow' ); ?></p>
			</div>
		</div>
		<footer class="sk-review-card__footer">
			<hr class="sk-review-card__rule" />
			<p class="sk-review-card__author"><?php esc_html_e( 'Jordan Hale', 'skaflow' ); ?></p>
			<p class="sk-review-card__verified">
				<span class="sk-review-card__verified-icon" aria-hidden="true"></span>
				<span class="sk-review-card__verified-text"><?php esc_html_e( 'VERIFIED • SAMPLE PRODUCT', 'skaflow' ); ?></span>
			</p>
		</footer>
	</article>
	<?php

	return (string) ob_get_clean();
}
