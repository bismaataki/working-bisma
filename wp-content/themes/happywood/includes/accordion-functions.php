<?php
/**
 * Reusable accordion markup helpers.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize a list of post IDs from ACF or other sources.
 *
 * @param mixed $value Raw field value.
 * @return int[]
 */
function happywood_normalize_post_ids( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$ids = array();

	foreach ( $value as $item ) {
		if ( is_object( $item ) && isset( $item->ID ) ) {
			$item = (int) $item->ID;
		}

		$id = absint( $item );

		if ( $id > 0 ) {
			$ids[] = $id;
		}
	}

	return $ids;
}

/**
 * Load published posts by ID, preserving input order.
 *
 * @param int[]  $post_ids  Post IDs in display order.
 * @param string $post_type Optional post type filter.
 * @return WP_Post[]
 */
function happywood_get_posts_by_ids( array $post_ids, string $post_type = '' ): array {
	$posts = array();

	foreach ( $post_ids as $post_id ) {
		$post = get_post( absint( $post_id ) );

		if ( ! $post instanceof WP_Post || 'publish' !== $post->post_status ) {
			continue;
		}

		if ( '' !== $post_type && $post_type !== $post->post_type ) {
			continue;
		}

		$posts[] = $post;
	}

	return $posts;
}

/**
 * Build accordion item rows from posts.
 *
 * @param WP_Post[] $posts Posts in display order.
 * @return array<int, array{id: int, title: string, content: string}>
 */
function happywood_get_accordion_items_from_posts( array $posts ): array {
	$items = array();

	foreach ( $posts as $post ) {
		if ( ! $post instanceof WP_Post ) {
			continue;
		}

		$items[] = array(
			'id'      => (int) $post->ID,
			'title'   => $post->post_title,
			'content' => $post->post_content,
		);
	}

	return $items;
}

/**
 * Build accordion item rows from post IDs.
 *
 * @param int[]  $post_ids  Post IDs in display order.
 * @param string $post_type Optional post type filter.
 * @return array<int, array{id: int, title: string, content: string}>
 */
function happywood_get_accordion_items_from_post_ids( array $post_ids, string $post_type = '' ): array {
	return happywood_get_accordion_items_from_posts(
		happywood_get_posts_by_ids( $post_ids, $post_type )
	);
}

/**
 * Sanitize the HTML tag used for accordion question titles.
 *
 * @param string $tag Requested tag name.
 * @return string `h3` or `span`.
 */
function happywood_get_accordion_title_tag( string $tag = 'span' ): string {
	$tag = strtolower( sanitize_key( $tag ) );

	return in_array( $tag, array( 'h3', 'span' ), true ) ? $tag : 'span';
}

/**
 * Render accordion markup from prepared items.
 *
 * @param array<int, array{id: int, title: string, content: string}> $items Accordion rows.
 * @param array{
 *     first_open?: bool,
 *     instance_id?: string,
 *     content_callback?: callable|null,
 *     initial_visible_count?: int,
 *     title_tag?: string,
 * } $args Accordion options.
 * @return string
 */
function happywood_get_accordion_html( array $items, array $args = array() ): string {
	if ( empty( $items ) ) {
		return '';
	}

	$first_open            = ! isset( $args['first_open'] ) || (bool) $args['first_open'];
	$instance_id           = isset( $args['instance_id'] ) ? sanitize_html_class( (string) $args['instance_id'] ) : 'hw-accordion';
	$content_callback      = $args['content_callback'] ?? null;
	$initial_visible_count = isset( $args['initial_visible_count'] ) ? absint( $args['initial_visible_count'] ) : 0;
	$title_tag             = happywood_get_accordion_title_tag( isset( $args['title_tag'] ) ? (string) $args['title_tag'] : 'span' );
	$visible_limit         = $initial_visible_count > 0 ? $initial_visible_count : 0;
	$rendered_count        = 0;
	$has_load_more_hidden  = false;

	if ( ! is_callable( $content_callback ) ) {
		$content_callback = static function ( string $content ): string {
			return wp_kses_post( apply_filters( 'the_content', $content ) );
		};
	}

	ob_start();
	?>
	<div class="hw-accordion__list" role="presentation">
		<?php
		$item_index = 0;

		foreach ( $items as $item ) {
			if ( empty( $item['title'] ) ) {
				continue;
			}

			$item_id    = isset( $item['id'] ) ? absint( $item['id'] ) : 0;
			$suffix     = $item_id > 0 ? (string) $item_id : (string) $item_index;
			$is_open    = $first_open && 0 === $item_index;
			$panel_id   = $instance_id . '-panel-' . $suffix;
			$trigger_id = $instance_id . '-trigger-' . $suffix;
			$content    = isset( $item['content'] ) ? (string) $item['content'] : '';
			$is_hidden  = $visible_limit > 0 && $rendered_count >= $visible_limit;

			if ( $is_hidden ) {
				$has_load_more_hidden = true;
			}

			++$rendered_count;
			++$item_index;

			$item_classes = 'hw-accordion__item';

			if ( $is_open ) {
				$item_classes .= ' is-open';
			}

			if ( $is_hidden ) {
				$item_classes .= ' hw-accordion__item--load-more-hidden';
			}
			?>
			<div class="<?php echo esc_attr( $item_classes ); ?>" data-hw-accordion-item>
				<?php if ( 'h3' === $title_tag ) : ?>
					<h3 class="hw-accordion__title">
						<button
							type="button"
							class="hw-accordion__trigger"
							aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
							aria-controls="<?php echo esc_attr( $panel_id ); ?>"
							id="<?php echo esc_attr( $trigger_id ); ?>"
						>
							<?php echo esc_html( (string) $item['title'] ); ?>
							<span class="hw-accordion__icon" aria-hidden="true"></span>
						</button>
					</h3>
				<?php else : ?>
					<button
						type="button"
						class="hw-accordion__trigger"
						aria-expanded="<?php echo $is_open ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $panel_id ); ?>"
						id="<?php echo esc_attr( $trigger_id ); ?>"
					>
						<span class="hw-accordion__label"><?php echo esc_html( (string) $item['title'] ); ?></span>
						<span class="hw-accordion__icon" aria-hidden="true"></span>
					</button>
				<?php endif; ?>
				<div
					id="<?php echo esc_attr( $panel_id ); ?>"
					class="hw-accordion__panel"
					role="region"
					aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
					aria-hidden="<?php echo $is_open ? 'false' : 'true'; ?>"
				>
					<div class="hw-accordion__content entry-content">
						<?php echo $content_callback( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- callback escapes. ?>
					</div>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<?php
	if ( $has_load_more_hidden ) {
		echo happywood_get_accordion_load_more_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
	}

	return (string) ob_get_clean();
}

/**
 * Load More button markup for accordions with hidden items.
 *
 * @return string
 */
function happywood_get_accordion_load_more_html(): string {
	$button_markup = happywood_get_buttons_markup(
		array(
			array(
				'label'           => __( 'Load More', 'happywood' ),
				'style'           => 'is-style-ghost',
				'tag'             => 'button',
				'element_class'   => 'hw-accordion__load-more-button',
				'html_attributes' => array(
					'type'                        => 'button',
					'data-hw-accordion-load-more' => 'true',
				),
			),
		),
		array(
			'wrapper_class' => 'hw-accordion__load-more-actions',
			'layout'        => array(
				'type'           => 'flex',
				'justifyContent' => 'center',
			),
		)
	);

	if ( '' === trim( $button_markup ) ) {
		return '';
	}

	return '<div class="hw-accordion__load-more">' . $button_markup . '</div>';
}

/**
 * Render accordion markup from post IDs or an empty-state message.
 *
 * @param int[]  $post_ids Post IDs in display order.
 * @param array{
 *     post_type?: string,
 *     empty_message?: string,
 *     first_open?: bool,
 *     instance_id?: string,
 *     content_callback?: callable|null,
 *     initial_visible_count?: int,
 *     title_tag?: string,
 * } $args Accordion options.
 * @return string
 */
function happywood_get_accordion_html_from_post_ids( array $post_ids, array $args = array() ): string {
	$post_type     = isset( $args['post_type'] ) ? (string) $args['post_type'] : '';
	$empty_message = isset( $args['empty_message'] ) ? (string) $args['empty_message'] : '';
	$items         = happywood_get_accordion_items_from_post_ids( $post_ids, $post_type );
	$html          = happywood_get_accordion_html( $items, $args );

	if ( '' !== trim( $html ) ) {
		return $html;
	}

	if ( '' === trim( $empty_message ) ) {
		return '';
	}

	return '<p class="hw-accordion__empty">' . esc_html( $empty_message ) . '</p>';
}
