<?php
/**
 * Product reviews — PDP cards, form, pagination, and section columns.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build star rating markup for a review (inner `.star-rating` only).
 *
 * @param int $rating Rating 1–5.
 * @return string
 */
function happywood_get_review_rating_html( int $rating ): string {
	if ( ! function_exists( 'wc_get_rating_html' ) || ! wc_review_ratings_enabled() ) {
		return '';
	}

	return wc_get_rating_html( $rating );
}

/**
 * Integer review rating markup for product review cards.
 *
 * @param int $rating Rating 1–5.
 * @return string
 */
function happywood_get_product_review_card_rating_markup( int $rating ): string {
	if ( $rating <= 0 ) {
		return '';
	}

	$rating_html = happywood_get_average_rating_stars_markup( (float) $rating, 0 );

	if ( '' === $rating_html ) {
		return '';
	}

	return sprintf(
		'<div class="hw-product-review-card__rating">%s</div>',
		$rating_html
	);
}

/**
 * Partial average rating stars (WooCommerce Blocks markup).
 *
 * @param float $average      Average rating (0–5).
 * @param int   $review_count Optional review count for accessible label.
 * @return string
 */
function happywood_get_average_rating_stars_markup( float $average, int $review_count = 0 ): string {
	if ( $average <= 0 || ! function_exists( 'wc_review_ratings_enabled' ) || ! wc_review_ratings_enabled() ) {
		return '';
	}

	$average           = max( 0.0, min( 5.0, $average ) );
	$width             = ( $average / 5 ) * 100;
	$formatted_average = abs( $average - round( $average ) ) < 0.01
		? number_format_i18n( (int) round( $average ), 0 )
		: number_format_i18n( $average, 1 );

	if ( $review_count > 0 ) {
		$aria_label = sprintf(
			/* translators: 1: average rating, 2: review count */
			_n(
				'Rated %1$s out of 5 based on %2$s customer rating',
				'Rated %1$s out of 5 based on %2$s customer ratings',
				$review_count,
				'happywood'
			),
			$formatted_average,
			number_format_i18n( $review_count )
		);

		$inner_html = sprintf(
			'Rated <strong class="rating">%1$s</strong> out of 5 based on <span class="rating">%2$s</span> customer %3$s',
			esc_html( $formatted_average ),
			esc_html( (string) $review_count ),
			esc_html( _n( 'rating', 'ratings', $review_count, 'happywood' ) )
		);
	} else {
		$aria_label = sprintf(
			/* translators: %s: average rating */
			__( 'Rated %s out of 5', 'happywood' ),
			$formatted_average
		);

		$inner_html = sprintf(
			'Rated <strong class="rating">%s</strong> out of 5',
			esc_html( $formatted_average )
		);
	}

	happywood_enqueue_review_rating_assets();

	return sprintf(
		'<div class="wc-block-components-product-rating__stars wc-block-grid__product-rating__stars" role="img" aria-label="%1$s"><span style="width:%2$s%%">%3$s</span></div>',
		esc_attr( $aria_label ),
		esc_attr( (string) $width ),
		$inner_html
	);
}

/**
 * Enqueue WooCommerce star-rating font/styles for review cards and summaries.
 *
 * @return void
 */
function happywood_enqueue_review_rating_assets(): void {
	static $enqueued = false;

	if ( $enqueued ) {
		return;
	}

	if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
		wp_enqueue_style( 'woocommerce-general' );
	}

	$enqueued = true;
}

/**
 * Serialized block markup for a single product review card.
 *
 * Matches the core/group layout used in the Site Editor (name, verified, stars, date, content).
 *
 * @param WP_Comment $comment Review comment.
 * @return string
 */
function happywood_get_product_review_card_blocks_markup( WP_Comment $comment ): string {
	$rating   = (int) get_comment_meta( $comment->comment_ID, 'rating', true );
	$author   = get_comment_author( $comment );
	$date     = get_comment_date( 'F j, Y', $comment );
	$date_iso = get_comment_date( 'c', $comment );
	$content  = wp_kses_post( $comment->comment_content );

	$verified_markup = '';

	if ( function_exists( 'wc_review_is_from_verified_owner' ) && wc_review_is_from_verified_owner( $comment->comment_ID ) ) {
		$verified_markup = '<!-- wp:paragraph {"className":"hw-product-review-card__verified"} -->
<p class="hw-product-review-card__verified">' . esc_html__( 'Verified Purchase', 'happywood' ) . '</p>
<!-- /wp:paragraph -->

';
	}

	$rating_markup = '';

	if ( $rating > 0 && function_exists( 'wc_review_ratings_enabled' ) && wc_review_ratings_enabled() ) {
		$rating_html = happywood_get_product_review_card_rating_markup( $rating );

		if ( '' !== $rating_html ) {
			$rating_markup = '<!-- wp:html -->
' . $rating_html . '
<!-- /wp:html -->

';
		}
	}

	$content_markup = '<!-- wp:html -->
<div class="hw-product-review-card__content">' . $content . '</div>
<!-- /wp:html -->';

	return '<!-- wp:group {"metadata":{"name":"Product Review Card"},"className":"hw-product-review-card","style":{"spacing":{"padding":{"top":"var:preset|spacing|large","bottom":"var:preset|spacing|large","left":"var:preset|spacing|medium","right":"var:preset|spacing|medium"}}},"backgroundColor":"white","layout":{"type":"constrained"}} -->
<div class="wp-block-group hw-product-review-card has-white-background-color has-background" style="padding-top:var(--wp--preset--spacing--large);padding-right:var(--wp--preset--spacing--medium);padding-bottom:var(--wp--preset--spacing--large);padding-left:var(--wp--preset--spacing--medium)"><!-- wp:group {"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"top"}} -->
<div class="wp-block-group"><!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|xxx-small"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group"><!-- wp:paragraph {"className":"hw-product-review-card__author","style":{"typography":{"fontStyle":"normal","fontWeight":"600"}}} -->
<p class="hw-product-review-card__author" style="font-style:normal;font-weight:600">' . esc_html( $author ) . '</p>
<!-- /wp:paragraph -->

' . $verified_markup . $rating_markup . '</div>
<!-- /wp:group -->

<!-- wp:paragraph {"className":"hw-product-review-card__date","style":{"elements":{"link":{"color":{"text":"var:preset|color|neutral"}}}},"textColor":"neutral","fontSize":"small"} -->
<p class="hw-product-review-card__date has-neutral-color has-text-color has-link-color has-small-font-size"><time datetime="' . esc_attr( $date_iso ) . '">' . esc_html( $date ) . '</time></p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

' . $content_markup . '</div>
<!-- /wp:group -->';
}

/**
 * Render a single review card.
 *
 * @param WP_Comment $comment Review comment.
 * @return void
 */
function happywood_render_product_review_item( WP_Comment $comment ): void {
	if ( ! function_exists( 'do_blocks' ) ) {
		return;
	}

	$markup = happywood_get_product_review_card_blocks_markup( $comment );

	if ( '' === trim( $markup ) ) {
		return;
	}

	printf(
		'<li id="comment-%1$s" class="hw-product-reviews__item">%2$s</li>',
		esc_attr( (string) $comment->comment_ID ),
		do_blocks( $markup ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- block render output.
	);
}

/**
 * Render review pagination links.
 *
 * @param int $product_id     Product post ID.
 * @param int $reviews_per_page Reviews per page.
 * @return void
 */
function happywood_render_product_review_pagination( int $product_id, int $reviews_per_page ): void {
	$total       = happywood_get_product_review_comment_count( $product_id );
	$total_pages = (int) ceil( $total / max( 1, $reviews_per_page ) );
	$current     = happywood_get_product_review_page();

	if ( $total_pages <= 1 ) {
		return;
	}

	$links = paginate_links(
		array(
			'base'      => esc_url_raw( add_query_arg( 'cpage', '%#%' ) ),
			'format'    => '',
			'current'   => $current,
			'total'     => $total_pages,
			'type'      => 'list',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
		)
	);

	if ( ! $links ) {
		return;
	}

	printf(
		'<nav class="hw-product-reviews__pagination woocommerce-pagination" aria-label="%1$s">%2$s</nav>',
		esc_attr__( 'Product reviews pagination', 'happywood' ),
		$links // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	);
}

/**
 * Output the WooCommerce review submission form.
 *
 * @param int $product_id Product post ID.
 * @return void
 */
function happywood_render_product_review_form( int $product_id ): void {
	if ( ! function_exists( 'wc_get_product' ) ) {
		return;
	}

	$product = wc_get_product( $product_id );

	if ( ! $product || ! $product->get_reviews_allowed() ) {
		return;
	}

	$can_review = get_option( 'woocommerce_review_rating_verification_required' ) === 'no'
		|| wc_customer_bought_product( '', get_current_user_id(), $product->get_id() );

	if ( ! $can_review ) {
		echo '<p class="woocommerce-verification-required">' . esc_html__( 'Only logged in customers who have purchased this product may leave a review.', 'woocommerce' ) . '</p>';
		return;
	}

	$commenter    = wp_get_current_commenter();
	$comment_form = array(
		/* translators: %s is product title */
		'title_reply'         => happywood_get_product_review_comment_count( $product_id ) > 0
			? esc_html__( 'Add a review', 'woocommerce' )
			: sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'woocommerce' ), get_the_title( $product_id ) ),
		'title_reply_to'      => esc_html__( 'Leave a Reply to %s', 'woocommerce' ),
		'title_reply_before'  => '<span id="reply-title" class="comment-reply-title" role="heading" aria-level="3">',
		'title_reply_after'   => '</span>',
		'comment_notes_after' => '',
		'label_submit'        => esc_html__( 'Submit', 'woocommerce' ),
		'logged_in_as'        => '',
		'comment_field'       => '',
	);

	$name_email_required = (bool) get_option( 'require_name_email', 1 );
	$fields              = array(
		'author' => array(
			'label'        => __( 'Name', 'woocommerce' ),
			'type'         => 'text',
			'value'        => $commenter['comment_author'],
			'required'     => $name_email_required,
			'autocomplete' => 'name',
		),
		'email'  => array(
			'label'        => __( 'Email', 'woocommerce' ),
			'type'         => 'email',
			'value'        => $commenter['comment_author_email'],
			'required'     => $name_email_required,
			'autocomplete' => 'email',
		),
	);

	$comment_form['fields'] = array();

	foreach ( $fields as $key => $field ) {
		$field_html  = '<p class="comment-form-' . esc_attr( $key ) . '">';
		$field_html .= '<label for="' . esc_attr( $key ) . '">' . esc_html( $field['label'] );

		if ( $field['required'] ) {
			$field_html .= '&nbsp;<span class="required">*</span>';
		}

		$field_html .= '</label><input id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" type="' . esc_attr( $field['type'] ) . '" autocomplete="' . esc_attr( $field['autocomplete'] ) . '" value="' . esc_attr( $field['value'] ) . '" size="30" ' . ( $field['required'] ? 'required' : '' ) . ' /></p>';

		$comment_form['fields'][ $key ] = $field_html;
	}

	$account_page_url = wc_get_page_permalink( 'myaccount' );

	if ( $account_page_url ) {
		/* translators: %s opening and closing link tags respectively */
		$comment_form['must_log_in'] = '<p class="must-log-in">' . sprintf( esc_html__( 'You must be %1$slogged in%2$s to post a review.', 'woocommerce' ), '<a href="' . esc_url( $account_page_url ) . '">', '</a>' ) . '</p>';
	}

	if ( wc_review_ratings_enabled() ) {
		$comment_form['comment_field'] = '<div class="comment-form-rating"><label for="rating" id="comment-form-rating-label">' . esc_html__( 'Your rating', 'woocommerce' ) . ( wc_review_ratings_required() ? '&nbsp;<span class="required">*</span>' : '' ) . '</label><select name="rating" id="rating" required>
			<option value="">' . esc_html__( 'Rate&hellip;', 'woocommerce' ) . '</option>
			<option value="5">' . esc_html__( 'Perfect', 'woocommerce' ) . '</option>
			<option value="4">' . esc_html__( 'Good', 'woocommerce' ) . '</option>
			<option value="3">' . esc_html__( 'Average', 'woocommerce' ) . '</option>
			<option value="2">' . esc_html__( 'Not that bad', 'woocommerce' ) . '</option>
			<option value="1">' . esc_html__( 'Very poor', 'woocommerce' ) . '</option>
		</select></div>';
	}

	$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your review', 'woocommerce' ) . '&nbsp;<span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

	global $post;
	$previous_post = $post;
	$post          = get_post( $product_id );

	if ( ! $post ) {
		return;
	}

	setup_postdata( $post );
	?>
	<div id="review_form_wrapper">
		<div id="review_form">
			<?php
			comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
			?>
		</div>
	</div>
	<?php
	wp_reset_postdata();
	$post = $previous_post;
}

/**
 * Build the Write a Review button markup.
 *
 * @param array<string, mixed> $args Optional overrides (wrapper_class, style, element_class).
 * @return string
 */
function happywood_get_product_reviews_write_review_button_html( array $args = array() ): string {
	$wrapper_class = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'hw-product-reviews__write-review-actions';
	$style         = isset( $args['style'] ) ? (string) $args['style'] : 'is-style-primary';
	$element_class = isset( $args['element_class'] ) ? (string) $args['element_class'] : 'hw-product-reviews__write-review';

	return happywood_get_buttons_markup(
		array(
			array(
				'label'           => __( 'Write a Review', 'happywood' ),
				'url'             => '#reviews',
				'style'           => $style,
				'element_class'   => $element_class,
				'html_attributes' => array(
					'data-hw-show-review-form' => 'true',
				),
			),
		),
		array(
			'wrapper_class' => $wrapper_class,
			'layout'        => array(
				'type'           => 'flex',
				'justifyContent' => 'stretch',
			),
		)
	);
}

/**
 * Render the product reviews summary block with explicit post context.
 *
 * Uses WP_Block so postId context is available to the nested dynamic block.
 *
 * @param int $product_id Product post ID.
 * @return string
 */
function happywood_render_product_reviews_summary_block( int $product_id ): string {
	if ( $product_id <= 0 || ! class_exists( 'WP_Block' ) ) {
		return '';
	}

	$parsed_blocks = parse_blocks( '<!-- wp:happywood/product-reviews-summary /-->' );

	if ( empty( $parsed_blocks[0]['blockName'] ) ) {
		return '';
	}

	$block = new WP_Block(
		$parsed_blocks[0],
		array(
			'postId'   => $product_id,
			'postType' => 'product',
		)
	);

	return $block->render();
}

/**
 * Build the review list + form column HTML for the product reviews block.
 *
 * @param int  $product_id       Product post ID.
 * @param int  $reviews_per_page Reviews per page.
 * @param bool $show_write_button Show Write a Review when summary is hidden.
 * @return string
 */
function happywood_get_product_reviews_main_column_html( int $product_id, int $reviews_per_page, bool $show_write_button ): string {
	$reviews_per_page = max( 1, $reviews_per_page );
	$total            = happywood_get_product_review_comment_count( $product_id );
	$paged            = happywood_get_product_review_page();
	$comments         = happywood_get_product_review_comments(
		$product_id,
		array(
			'number' => $reviews_per_page,
			'offset' => ( $paged - 1 ) * $reviews_per_page,
		)
	);

	$has_reviews = $total > 0;

	ob_start();
	?>
	<div class="hw-product-reviews__inner">
		<div class="hw-product-reviews__header">
			<h2 class="hw-product-reviews__title">
				<?php
				echo esc_html(
					sprintf(
						/* translators: %d: review count */
						_n( '%d Review', '%d Reviews', $total, 'happywood' ),
						$total
					)
				);
				?>
			</h2>
		</div>

		<div id="comments" class="hw-product-reviews__comments">
			<?php if ( $has_reviews ) : ?>
				<ol class="hw-product-reviews__list commentlist">
					<?php
					foreach ( $comments as $comment ) {
						happywood_render_product_review_item( $comment );
					}
					?>
				</ol>
				<?php happywood_render_product_review_pagination( $product_id, $reviews_per_page ); ?>
			<?php else : ?>
				<p class="hw-product-reviews__empty woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'woocommerce' ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $show_write_button ) : ?>
			<?php echo happywood_get_product_reviews_write_review_button_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		<?php endif; ?>

		<div class="hw-product-reviews__form-wrapper hw-product-reviews__form-wrapper--hidden">
			<?php happywood_render_product_review_form( $product_id ); ?>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Serialized core/columns markup for the product reviews section.
 *
 * @param string $main_html     Review list column HTML.
 * @param string $summary_html  Summary block render output.
 * @param bool   $show_summary  Whether to include the summary column.
 * @return string
 */
function happywood_get_product_reviews_columns_blocks_markup( string $main_html, string $summary_html, bool $show_summary ): string {
	if ( '' === trim( $main_html ) ) {
		return '';
	}

	if ( $show_summary && '' !== trim( $summary_html ) ) {
		return '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column {"width":"25%"} -->
<div class="wp-block-column" style="flex-basis:25%">' . $summary_html . '</div>
<!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">' . $main_html . '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->';
	}

	return '<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide"><!-- wp:column -->
<div class="wp-block-column">' . $main_html . '</div>
<!-- /wp:column --></div>
<!-- /wp:columns -->';
}
