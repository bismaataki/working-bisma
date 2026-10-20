<?php
/**
 * FAQ archive page helpers — grouped accordions, schema, single URL guard.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Lucide icon slug map for FAQ category term slugs.
 *
 * @return array<string, string>
 */
function skaflow_get_faq_category_default_icon_map(): array {
	return array(
		'ordering-online'   => 'shopping-cart',
		'delivering-faqs'   => 'truck',
		'warranty-returns'  => 'shield-check',
		'general-questions' => 'user',
		'product-faqs'      => 'tag',
	);
}

/**
 * Allowed order values for {@see skaflow_sort_faq_groups_term_ids()}.
 *
 * @return string[]
 */
function skaflow_get_faq_groups_term_orderby_options(): array {
	return array(
		'manual',
		'menu_order',
		'name',
		'slug',
		'term_id',
		'count',
		'description',
		'term_group',
	);
}

/**
 * Normalize block order attribute for WP_Term_Query.
 *
 * @param string $order Block order setting (`asc` or `desc`).
 * @return string `ASC` or `DESC`.
 */
function skaflow_normalize_faq_groups_term_order( string $order ): string {
	return 'desc' === strtolower( $order ) ? 'DESC' : 'ASC';
}

/**
 * Sort selected skaflow_faq_category term IDs for the FAQ groups block.
 *
 * @param int[]  $term_ids Term IDs in editor selection order.
 * @param string $order_by Block order setting. `manual` preserves selection order.
 * @param string $order    Sort direction (`asc` or `desc`). Ignored when `$order_by` is `manual`.
 * @return int[]
 */
function skaflow_sort_faq_groups_term_ids( array $term_ids, string $order_by = 'manual', string $order = 'asc' ): array {
	$term_ids = array_values(
		array_filter(
			array_map( 'absint', $term_ids ),
			static function ( int $term_id ): bool {
				return $term_id > 0;
			}
		)
	);

	if ( empty( $term_ids ) || 'manual' === $order_by ) {
		return $term_ids;
	}

	if ( ! in_array( $order_by, skaflow_get_faq_groups_term_orderby_options(), true ) ) {
		return $term_ids;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'skaflow_faq_category',
			'include'    => $term_ids,
			'hide_empty' => false,
			'orderby'    => $order_by,
			'order'      => skaflow_normalize_faq_groups_term_order( $order ),
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $term_ids;
	}

	$sorted_ids = array_map(
		static function ( WP_Term $term ): int {
			return (int) $term->term_id;
		},
		$terms
	);

	foreach ( $term_ids as $term_id ) {
		if ( ! in_array( $term_id, $sorted_ids, true ) ) {
			$sorted_ids[] = $term_id;
		}
	}

	return $sorted_ids;
}

/**
 * Resolve Lucide icon slug for an FAQ category term.
 *
 * @param WP_Term $term FAQ category term.
 * @return string
 */
function skaflow_get_faq_category_icon_slug( WP_Term $term ): string {
	$icon = '';

	if ( function_exists( 'get_field' ) ) {
		$icon = (string) get_field( 'icon', $term );
	}

	$icon = sanitize_html_class( $icon );

	if ( '' !== $icon ) {
		return $icon;
	}

	$map = skaflow_get_faq_category_default_icon_map();

	return $map[ $term->slug ] ?? 'file-text';
}

/**
 * Published FAQ posts assigned to a category term.
 *
 * @param int $term_id FAQ category term ID.
 * @return WP_Post[]
 */
function skaflow_get_faq_posts_for_term( int $term_id ): array {
	if ( $term_id <= 0 ) {
		return array();
	}

	$posts = get_posts(
		array(
			'post_type'              => 'skaflow_faq',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'title',
			'order'                  => 'ASC',
			'ignore_sticky_posts'    => true,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'tax_query'              => array(
				array(
					'taxonomy' => 'skaflow_faq_category',
					'field'    => 'term_id',
					'terms'    => array( $term_id ),
				),
			),
		)
	);

	return is_array( $posts ) ? $posts : array();
}

/**
 * Render one FAQ category section (header + accordion).
 *
 * @param WP_Term   $term        FAQ category term.
 * @param WP_Post[] $posts       FAQ posts for the term.
 * @param string    $instance_id Accordion instance ID prefix.
 * @param array{
 *     title_tag?: string,
 * } $args Section options.
 * @return string
 */
function skaflow_get_faq_groups_section_html( WP_Term $term, array $posts, string $instance_id, array $args = array() ): string {
	if ( empty( $posts ) ) {
		return '';
	}

	$post_ids = wp_list_pluck( $posts, 'ID' );
	$post_ids = array_map( 'absint', is_array( $post_ids ) ? $post_ids : array() );

	if ( empty( $post_ids ) ) {
		return '';
	}

	$title_tag = skaflow_get_accordion_title_tag( isset( $args['title_tag'] ) ? (string) $args['title_tag'] : 'span' );

	$accordion_html = skaflow_get_accordion_html_from_post_ids(
		$post_ids,
		array(
			'post_type'             => 'skaflow_faq',
			'first_open'            => false,
			'instance_id'           => $instance_id,
			'empty_message'         => '',
			'initial_visible_count' => 0,
			'title_tag'             => $title_tag,
		)
	);

	if ( '' === trim( $accordion_html ) ) {
		return '';
	}

	$icon_slug   = skaflow_get_faq_category_icon_slug( $term );
	$count       = count( $posts );
	$count_label = sprintf(
		/* translators: %d: number of FAQ questions in this category section. */
		_n( '%d QUESTION', '%d QUESTIONS', $count, 'skaflow' ),
		$count
	);

	ob_start();
	?>
	<section
		class="sk-faq-groups__section"
		data-sk-faq-section
		data-term-id="<?php echo esc_attr( (string) $term->term_id ); ?>"
	>
		<div class="sk-faq-groups__section-header">
			<div class="sk-faq-groups__section-leading">
				<div class="sk-faq-groups__icon sk-faq-groups__icon--style-1 sk-icon-preview sk-icon-<?php echo esc_attr( $icon_slug ); ?>" aria-hidden="true"></div>
				<h2 class="sk-faq-groups__title"><?php echo esc_html( $term->name ); ?></h2>
			</div>
			<p class="sk-faq-groups__count" data-sk-faq-count="<?php echo esc_attr( (string) $count ); ?>"><?php echo esc_html( $count_label ); ?></p>
		</div>
		<div class="sk-faq-groups__accordion sk-accordion" data-sk-accordion>
			<?php echo $accordion_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper. ?>
		</div>
	</section>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render grouped FAQ sections for selected category term IDs.
 *
 * @param int[]  $term_ids Term IDs in display order (after sort).
 * @param array{
 *     queue_schema?: bool,
 *     title_tag?: string,
 * } $args Options.
 * @return string
 */
function skaflow_get_faq_groups_html( array $term_ids, array $args = array() ): string {
	$term_ids     = skaflow_sort_faq_groups_term_ids( $term_ids );
	$queue_schema = ! isset( $args['queue_schema'] ) || (bool) $args['queue_schema'];
	$title_tag    = skaflow_get_accordion_title_tag( isset( $args['title_tag'] ) ? (string) $args['title_tag'] : 'span' );
	$sections     = array();
	$schema_ids   = array();

	foreach ( $term_ids as $term_id ) {
		$term = get_term( absint( $term_id ), 'skaflow_faq_category' );

		if ( ! $term instanceof WP_Term || is_wp_error( $term ) ) {
			continue;
		}

		$posts = skaflow_get_faq_posts_for_term( (int) $term->term_id );

		if ( empty( $posts ) ) {
			continue;
		}

		$instance_id = 'sk-faq-group-' . (int) $term->term_id;
		$section     = skaflow_get_faq_groups_section_html(
			$term,
			$posts,
			$instance_id,
			array(
				'title_tag' => $title_tag,
			)
		);

		if ( '' === trim( $section ) ) {
			continue;
		}

		$sections[] = $section;

		foreach ( $posts as $post ) {
			if ( $post instanceof WP_Post ) {
				$schema_ids[] = (int) $post->ID;
			}
		}
	}

	if ( empty( $sections ) ) {
		return '';
	}

	if ( $queue_schema && ! empty( $schema_ids ) ) {
		skaflow_queue_faq_archive_schema( $schema_ids );
	}

	return implode( '', $sections );
}

/**
 * FAQ archive post IDs queued for FAQPage JSON-LD in wp_footer.
 *
 * @return int[]
 */
function &skaflow_faq_archive_schema_get_queue(): array {
	static $queue = array();

	return $queue;
}

/**
 * Queue FAQPage schema for the FAQ archive (deduped post IDs).
 *
 * @param int[] $post_ids FAQ post IDs rendered on the archive.
 */
function skaflow_queue_faq_archive_schema( array $post_ids ): void {
	$queue = &skaflow_faq_archive_schema_get_queue();

	foreach ( $post_ids as $post_id ) {
		$post_id = absint( $post_id );

		if ( $post_id < 1 || in_array( $post_id, $queue, true ) ) {
			continue;
		}

		$queue[] = $post_id;
	}
}

/**
 * Build FAQPage schema from FAQ post IDs.
 *
 * @param int[] $post_ids FAQ post IDs in display order.
 * @return array<string, mixed>|null
 */
function skaflow_get_faqpage_schema_from_post_ids( array $post_ids ): ?array {
	$items = skaflow_get_accordion_items_from_post_ids( $post_ids, 'skaflow_faq' );

	if ( empty( $items ) ) {
		return null;
	}

	$main_entity = array();

	foreach ( $items as $item ) {
		$question = trim( (string) ( $item['title'] ?? '' ) );
		$answer   = trim( wp_strip_all_tags( (string) ( $item['content'] ?? '' ) ) );

		if ( '' === $question || '' === $answer ) {
			continue;
		}

		$main_entity[] = array(
			'@type'          => 'Question',
			'name'           => $question,
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $answer,
			),
		);
	}

	if ( empty( $main_entity ) ) {
		return null;
	}

	return array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $main_entity,
	);
}

/**
 * Print FAQ archive FAQPage JSON-LD in the footer.
 */
function skaflow_print_faq_archive_schema_json_ld(): void {
	$queue = skaflow_faq_archive_schema_get_queue();

	if ( empty( $queue ) ) {
		return;
	}

	$schema = skaflow_get_faqpage_schema_from_post_ids( $queue );

	if ( null === $schema ) {
		return;
	}

	$json = wp_json_encode( $schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	if ( ! $json ) {
		return;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		$json // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON encoded.
	);
}

add_action( 'wp_footer', 'skaflow_print_faq_archive_schema_json_ld', 20 );

/**
 * FAQ archive output is block-driven (faq-groups), not the main query loop.
 *
 * Disable main-query pagination on the public FAQ archive only so core does not
 * emit rel=next or treat /faq/ as a paged post list.
 *
 * @param WP_Query $query Query instance.
 */
function skaflow_faq_archive_pre_get_posts( WP_Query $query ): void {
	if (
		! is_admin()
		&& ! wp_doing_ajax()
		&& ! is_feed()
		&& $query->is_main_query()
		&& $query->is_post_type_archive( 'skaflow_faq' )
		&& ! $query->is_tax()
		&& ! $query->is_search()
	) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'nopaging', true );
	}
}

add_action( 'pre_get_posts', 'skaflow_faq_archive_pre_get_posts' );

/**
 * Block public single FAQ URLs — archive-only surface at /faq/.
 *
 * @return void
 */
function skaflow_block_skaflow_faq_singles(): void {
	if ( ! is_singular( 'skaflow_faq' ) ) {
		return;
	}

	global $wp_query;

	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
}

add_action( 'template_redirect', 'skaflow_block_skaflow_faq_singles' );
