<?php
/**
 * Public site search query scoping (WooCommerce product search).
 *
 * Scope unscoped `/?s=` requests to products so WooCommerce serves
 * `product-search-results.html`. Does not affect blog listing search on
 * `/blogs/` (`is_home()`, not `is_search()`).
 *
 * Uses the `request` filter so `post_type` is set before `WP_Query::parse_query()`
 * runs. That lets core/WooCommerce set `is_post_type_archive( 'product' )`, which
 * `pre_get_posts` alone cannot do after conditional tags are already computed.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether parsed request vars target the public blog posts index.
 *
 * @param array<string, mixed> $query_vars Parsed main-query vars.
 * @return bool
 */
function skaflow_is_blog_listing_query_vars( array $query_vars ): bool {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id <= 0 ) {
		return false;
	}

	if ( isset( $query_vars['page_id'] ) && (int) $query_vars['page_id'] === $posts_page_id ) {
		return true;
	}

	$posts_page = get_post( $posts_page_id );

	if ( ! ( $posts_page instanceof WP_Post ) || empty( $query_vars['pagename'] ) ) {
		return false;
	}

	$pagename = (string) $query_vars['pagename'];

	return $posts_page->post_name === $pagename
		|| str_ends_with( $pagename, '/' . $posts_page->post_name );
}

/**
 * Whether the request explicitly limits search to a non-product post type.
 *
 * Only unscoped searches (`post_type` empty or `any`) are candidates for
 * product scoping. Explicit `post_type=product` is treated as already scoped.
 *
 * @param mixed $post_type Raw post_type query var.
 * @return bool
 */
function skaflow_is_unscoped_public_search_post_type( $post_type ): bool {
	if ( empty( $post_type ) || 'any' === $post_type ) {
		return true;
	}

	if ( is_string( $post_type ) ) {
		return 'product' === $post_type;
	}

	if ( is_array( $post_type ) ) {
		return 1 === count( $post_type ) && in_array( 'product', $post_type, true );
	}

	return false;
}

/**
 * Sanitized post types for scoped public site search.
 *
 * @param array<string, mixed>|WP_Query $context Parsed request vars or query.
 * @return string[]
 */
function skaflow_get_product_search_post_types( $context ): array {
	/**
	 * Post types for the public site search results page.
	 *
	 * @param string[]                    $post_types Default post type slugs.
	 * @param array<string, mixed>|WP_Query $context    Parsed request vars or query.
	 */
	$post_types = apply_filters(
		'skaflow_product_search_post_types',
		array( 'product' ),
		$context
	);

	return array_values(
		array_filter(
			array_map( 'sanitize_key', (array) $post_types )
		)
	);
}

/**
 * Whether unscoped public site search should be scoped to products.
 *
 * Positive gates only — returns false unless every condition matches.
 *
 * @param array<string, mixed> $query_vars Parsed main-query vars.
 * @return bool
 */
function skaflow_should_scope_public_search_to_products( array $query_vars ): bool {
	if ( is_admin() || wp_doing_ajax() || wp_doing_cron() ) {
		return false;
	}

	if ( skaflow_is_blog_listing_query_vars( $query_vars ) ) {
		return false;
	}

	$search_term = isset( $query_vars['s'] ) ? trim( (string) $query_vars['s'] ) : '';

	if ( '' === $search_term ) {
		return false;
	}

	if ( ! skaflow_is_unscoped_public_search_post_type( $query_vars['post_type'] ?? '' ) ) {
		return false;
	}

	if ( ! post_type_exists( 'product' ) ) {
		return false;
	}

	/**
	 * Final gate before scoping public site search to products.
	 *
	 * @param bool                 $scope      Scope this request to product search.
	 * @param array<string, mixed> $query_vars Parsed main-query vars.
	 */
	return (bool) apply_filters( 'skaflow_scope_main_search_to_products', true, $query_vars );
}

/**
 * Scope unscoped public site search to WooCommerce products.
 *
 * @param array<string, mixed> $query_vars Parsed main-query vars.
 * @return array<string, mixed>
 */
function skaflow_product_search_request( array $query_vars ): array {
	if ( ! skaflow_should_scope_public_search_to_products( $query_vars ) ) {
		return $query_vars;
	}

	$post_types = skaflow_get_product_search_post_types( $query_vars );

	if ( empty( $post_types ) ) {
		return $query_vars;
	}

	$query_vars['post_type'] = 1 === count( $post_types ) ? $post_types[0] : $post_types;

	return $query_vars;
}

add_filter( 'request', 'skaflow_product_search_request' );
