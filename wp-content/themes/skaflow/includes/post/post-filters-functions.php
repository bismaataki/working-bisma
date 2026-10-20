<?php
/**
 * Blog listing filter helpers (Phase 5).
 *
 * URL-driven filters on the Posts page (`/blogs/`) using public query vars.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the current request is the public blog posts index.
 *
 * @return bool
 */
function skaflow_is_blog_listing(): bool {
	return is_home() && ! is_front_page();
}

/**
 * Permalink for the configured Posts page.
 *
 * @return string
 */
function skaflow_get_blog_listing_base_url(): string {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id <= 0 ) {
		return home_url( '/' );
	}

	$url = get_permalink( $posts_page_id );

	return is_string( $url ) ? $url : home_url( '/' );
}

/**
 * Sort options for the blog listing toolbar.
 *
 * @return array<string, array{label: string, orderby: string, order: string}>
 */
function skaflow_get_blog_listing_sort_options(): array {
	return array(
		'date-desc'  => array(
			'label'   => __( 'Latest First', 'skaflow' ),
			'orderby' => 'date',
			'order'   => 'desc',
		),
		'date-asc'   => array(
			'label'   => __( 'Oldest to newest', 'skaflow' ),
			'orderby' => 'date',
			'order'   => 'asc',
		),
		'title-asc'  => array(
			'label'   => __( 'A–Z', 'skaflow' ),
			'orderby' => 'title',
			'order'   => 'asc',
		),
		'title-desc' => array(
			'label'   => __( 'Z–A', 'skaflow' ),
			'orderby' => 'title',
			'order'   => 'desc',
		),
	);
}

/**
 * Resolve the active sort key from orderby/order query vars.
 *
 * @param string $orderby Optional orderby override.
 * @param string $order   Optional order override.
 * @return string
 */
function skaflow_get_blog_listing_sort_key( string $orderby = '', string $order = '' ): string {
	if ( '' === $orderby ) {
		$orderby = (string) get_query_var( 'orderby' );
	}

	if ( '' === $order ) {
		$order = (string) get_query_var( 'order' );
	}

	$orderby = sanitize_key( $orderby );
	$order   = strtolower( sanitize_key( $order ) );

	if ( 'title' === $orderby && 'asc' === $order ) {
		return 'title-asc';
	}

	if ( 'title' === $orderby && 'desc' === $order ) {
		return 'title-desc';
	}

	if ( 'date' === $orderby && 'asc' === $order ) {
		return 'date-asc';
	}

	return 'date-desc';
}

/**
 * Current blog listing filter state from public query vars.
 *
 * @return array{category_name: string, s: string, orderby: string, order: string, sort_key: string}
 */
function skaflow_get_blog_listing_filter_state(): array {
	$category_name = sanitize_title( (string) get_query_var( 'category_name' ) );

	if ( '' === $category_name ) {
		$cat_id = absint( get_query_var( 'cat' ) );

		if ( $cat_id > 0 ) {
			$term = get_term( $cat_id, 'category' );

			if ( $term instanceof \WP_Term && ! is_wp_error( $term ) ) {
				$category_name = $term->slug;
			}
		}
	}

	$orderby = sanitize_key( (string) get_query_var( 'orderby' ) );
	$order   = strtolower( sanitize_key( (string) get_query_var( 'order' ) ) );

	return array(
		'category_name' => $category_name,
		's'             => sanitize_text_field( (string) get_query_var( 's' ) ),
		'orderby'       => $orderby,
		'order'         => $order,
		'sort_key'      => skaflow_get_blog_listing_sort_key( $orderby, $order ),
	);
}

/**
 * Build a blog listing URL, merging current filter state with overrides.
 *
 * Pass an empty string for a key to remove it from the URL. Omitted keys keep
 * the current value when present.
 *
 * @param array<string, string|null> $overrides Query arg overrides.
 * @return string
 */
function skaflow_build_blog_listing_url( array $overrides = array() ): string {
	$state  = skaflow_get_blog_listing_filter_state();
	$params = array();

	foreach ( array( 'category_name', 's' ) as $key ) {
		if ( array_key_exists( $key, $overrides ) ) {
			$value = is_string( $overrides[ $key ] ) ? $overrides[ $key ] : '';

			if ( '' !== $value ) {
				$params[ $key ] = $value;
			}

			continue;
		}

		if ( '' !== $state[ $key ] ) {
			$params[ $key ] = $state[ $key ];
		}
	}

	$sort_key = $state['sort_key'];

	if ( array_key_exists( 'sort_key', $overrides ) ) {
		$sort_key = is_string( $overrides['sort_key'] ) ? $overrides['sort_key'] : 'date-desc';
	} elseif ( array_key_exists( 'orderby', $overrides ) || array_key_exists( 'order', $overrides ) ) {
		$sort_key = skaflow_get_blog_listing_sort_key(
			is_string( $overrides['orderby'] ?? '' ) ? (string) $overrides['orderby'] : '',
			is_string( $overrides['order'] ?? '' ) ? (string) $overrides['order'] : ''
		);
	}

	$sort_options = skaflow_get_blog_listing_sort_options();

	if ( isset( $sort_options[ $sort_key ] ) && 'date-desc' !== $sort_key ) {
		$params['orderby'] = $sort_options[ $sort_key ]['orderby'];
		$params['order']   = $sort_options[ $sort_key ]['order'];
	}

	$base = skaflow_get_blog_listing_base_url();

	return empty( $params ) ? $base : add_query_arg( $params, $base );
}

/**
 * Categories shown in blog filter UI.
 *
 * @return array<int, \WP_Term>
 */
function skaflow_get_blog_listing_filter_categories(): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}

	return array_values(
		array_filter(
			$terms,
			static function ( $term ): bool {
				return $term instanceof \WP_Term;
			}
		)
	);
}

/**
 * Published post count for the current blog listing main query.
 *
 * @return int
 */
function skaflow_get_blog_listing_results_count(): int {
	global $wp_query;

	if ( $wp_query instanceof \WP_Query ) {
		return (int) $wp_query->found_posts;
	}

	return 0;
}

/**
 * Enqueue post-filters block assets when rendered.
 *
 * @return void
 */
function skaflow_enqueue_post_filters_assets(): void {
	static $enqueued = false;

	if ( $enqueued || ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'skaflow/post-filters' );

	if ( $block_type && ! empty( $block_type->style ) ) {
		wp_enqueue_style( $block_type->style );
	}

	if ( $block_type && ! empty( $block_type->view_script ) ) {
		wp_enqueue_script( $block_type->view_script );
	}

	$enqueued = true;
}

/**
 * Render blog listing filters markup.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param \WP_Block|null       $block      Block instance.
 * @return string
 */
function skaflow_render_post_filters( array $attributes, $block = null ): string {
	if ( ! skaflow_is_blog_listing() ) {
		return '';
	}

	skaflow_enqueue_post_filters_assets();

	$attributes = wp_parse_args(
		$attributes,
		array(
			'showSearch'           => true,
			'showCategoryDropdown' => true,
			'showSort'             => true,
			'showPills'            => true,
			'showCount'            => true,
		)
	);

	ob_start();

	get_template_part(
		'partials/post-filters',
		null,
		array(
			'attributes' => $attributes,
			'block'      => $block,
		)
	);

	$markup = (string) ob_get_clean();

	if ( '' === trim( $markup ) ) {
		return '';
	}

	if ( $block instanceof \WP_Block ) {
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => 'sk-post-filters alignwide',
			),
			$block
		);
	} else {
		$wrapper_attributes = 'class="sk-post-filters wp-block-skaflow-post-filters"';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$markup
	);
}
