<?php
/**
 * Blog post archive query defaults (Phase 5).
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts per page for public `post` archives.
 *
 * Reads Settings → Reading (`posts_per_page`). The theme does not keep its
 * own count.
 *
 * @return int
 */
function skaflow_get_post_archive_posts_per_page(): int {
	$per_page = (int) get_option( 'posts_per_page', 10 );

	if ( $per_page < 1 ) {
		$per_page = 10;
	}

	/**
	 * Filter the posts-per-page value for the built-in post type.
	 *
	 * @param int $per_page Posts per page from Reading settings.
	 */
	return (int) apply_filters( 'skaflow_post_archive_posts_per_page', $per_page );
}

/**
 * Whether a WP_Query is listing the built-in post type on the front end.
 *
 * @param WP_Query $query Query instance.
 * @return bool
 */
function skaflow_is_post_type_post_query( WP_Query $query ): bool {
	$post_type = $query->get( 'post_type' );

	if ( empty( $post_type ) || 'any' === $post_type ) {
		return true;
	}

	if ( is_string( $post_type ) ) {
		return 'post' === $post_type;
	}

	if ( is_array( $post_type ) ) {
		return 1 === count( $post_type ) && in_array( 'post', $post_type, true );
	}

	return false;
}

/**
 * Whether the main query is a public blog archive surface for posts.
 *
 * @param WP_Query $query Query instance.
 * @return bool
 */
function skaflow_is_post_archive_main_query( WP_Query $query ): bool {
	if ( $query->is_home() ) {
		return skaflow_is_post_type_post_query( $query );
	}

	if ( $query->is_category() || $query->is_tag() || $query->is_author() || $query->is_date() ) {
		return skaflow_is_post_type_post_query( $query );
	}

	return false;
}

/**
 * Override main-query pagination for blog listing and post taxonomy archives.
 *
 * @param WP_Query $query Query instance.
 * @return void
 */
function skaflow_post_archive_pre_get_posts( WP_Query $query ): void {
	if (
		is_admin()
		|| wp_doing_ajax()
		|| is_feed()
		|| ! $query->is_main_query()
		|| ! skaflow_is_post_archive_main_query( $query )
	) {
		return;
	}

	$query->set( 'posts_per_page', skaflow_get_post_archive_posts_per_page() );
}

add_action( 'pre_get_posts', 'skaflow_post_archive_pre_get_posts' );

/**
 * Whether Query Loop related-posts mode is enabled for the block.
 *
 * @param array<string, mixed> $block_query Query settings from block context.
 * @return bool
 */
function skaflow_query_loop_is_related_posts_enabled( array $block_query ): bool {
	return ! empty( $block_query['isRelated'] );
}

/**
 * Whether related posts should fall back to the block's default query when empty.
 *
 * @param array<string, mixed> $block_query Query settings from block context.
 * @return bool
 */
function skaflow_query_loop_related_posts_fallback_enabled( array $block_query ): bool {
	return ! empty( $block_query['relatedFallbackToDefault'] );
}

/**
 * Query Loop inner blocks that build a secondary WP_Query (never the main query).
 *
 * @param WP_Block $block Block instance passed to query_loop_block_query_vars.
 * @return bool
 */
function skaflow_is_query_loop_secondary_query_block( WP_Block $block ): bool {
	static $allowed_blocks = array(
		'core/post-template',
		'core/query-pagination-next',
		'core/query-pagination-previous',
		'core/query-pagination-numbers',
		'core/query-no-results',
		'core/query-total',
	);

	if ( ! in_array( $block->name, $allowed_blocks, true ) ) {
		return false;
	}

	$block_query = $block->context['query'] ?? null;

	if ( ! is_array( $block_query ) ) {
		return false;
	}

	// inherit:true uses the main query directly and should never reach this filter.
	if ( ! empty( $block_query['inherit'] ) ) {
		return false;
	}

	return true;
}

/**
 * Normalize a Query Loop post type value to a string slug.
 *
 * @param mixed $post_type Post type from query vars.
 * @return string
 */
function skaflow_query_loop_get_post_type_slug( $post_type ): string {
	if ( is_array( $post_type ) ) {
		return (string) ( $post_type[0] ?? 'post' );
	}

	if ( is_string( $post_type ) && '' !== $post_type ) {
		return $post_type;
	}

	return 'post';
}

/**
 * Resolve the related-posts taxonomy slug from block query settings.
 *
 * @param array<string, mixed> $block_query Query settings from block context.
 * @return string
 */
function skaflow_get_related_posts_taxonomy( array $block_query ): string {
	$taxonomy = ! empty( $block_query['relatedTaxonomy'] )
		? sanitize_key( (string) $block_query['relatedTaxonomy'] )
		: 'category';

	if ( ! is_taxonomy_viewable( $taxonomy ) ) {
		return 'category';
	}

	return $taxonomy;
}

/**
 * Merge post IDs into post__not_in for a Query Loop query.
 *
 * @param array<string, mixed> $query    Query vars for WP_Query.
 * @param int[]                $post_ids Post IDs to exclude.
 * @return array<string, mixed>
 */
function skaflow_query_loop_merge_post_not_in( array $query, array $post_ids ): array {
	$exclude_ids = array_merge(
		(array) ( $query['post__not_in'] ?? array() ),
		$post_ids
	);

	$query['post__not_in'] = array_values(
		array_unique(
			array_filter(
				array_map( 'intval', $exclude_ids )
			)
		)
	);

	return $query;
}

/**
 * Build a tax_query clause for related posts.
 *
 * @param string $taxonomy Taxonomy slug.
 * @param int[]  $term_ids Term IDs.
 * @return array<int, array<string, mixed>>
 */
function skaflow_get_related_posts_tax_query( string $taxonomy, array $term_ids ): array {
	return array(
		array(
			'taxonomy'         => $taxonomy,
			'field'            => 'term_id',
			'terms'            => array_map( 'intval', $term_ids ),
			'include_children' => false,
		),
	);
}

/**
 * Cheap existence probe: does at least one related post exist?
 *
 * Uses a separate get_posts() lookup and does not touch the main query.
 *
 * @param int    $post_id   Current post ID.
 * @param string $post_type Post type slug.
 * @param string $taxonomy  Taxonomy slug.
 * @param int[]  $term_ids  Term IDs to match.
 * @return bool
 */
function skaflow_related_posts_exist( int $post_id, string $post_type, string $taxonomy, array $term_ids ): bool {
	if ( empty( $term_ids ) ) {
		return false;
	}

	$matches = get_posts(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'post__not_in'           => array( $post_id ),
			'tax_query'              => skaflow_get_related_posts_tax_query( $taxonomy, $term_ids ),
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'ignore_sticky_posts'    => true,
		)
	);

	return ! empty( $matches );
}

/**
 * Apply related-posts tax_query and exclusions to query vars.
 *
 * @param array<string, mixed> $query     Query vars for WP_Query.
 * @param int                  $post_id   Current post ID.
 * @param string               $taxonomy  Taxonomy slug.
 * @param int[]                $term_ids  Term IDs to match.
 * @return array<string, mixed>
 */
function skaflow_build_related_posts_query_vars( array $query, int $post_id, string $taxonomy, array $term_ids ): array {
	$query = skaflow_query_loop_merge_post_not_in( $query, array( $post_id ) );

	if ( ! empty( $term_ids ) ) {
		$query['tax_query'] = skaflow_get_related_posts_tax_query( $taxonomy, $term_ids );
	}

	return $query;
}

/**
 * Fall back to the Query Loop's default query vars on singular views.
 *
 * Keeps Core-built settings intact but excludes the current post.
 *
 * @param array<string, mixed> $query       Query vars for WP_Query.
 * @param array<string, mixed> $block_query Query settings from block context.
 * @return array<string, mixed>
 */
function skaflow_apply_related_posts_fallback_query_vars( array $query, array $block_query ): array {
	unset( $block_query );

	$post_type = skaflow_query_loop_get_post_type_slug( $query['post_type'] ?? 'post' );

	if ( ! is_singular( $post_type ) ) {
		return $query;
	}

	$post_id = (int) get_queried_object_id();

	if ( $post_id <= 0 ) {
		return $query;
	}

	return skaflow_query_loop_merge_post_not_in( $query, array( $post_id ) );
}

/**
 * Apply related-posts query vars, or signal fallback to the default Query Loop query.
 *
 * @param array<string, mixed> $query       Query vars for WP_Query.
 * @param array<string, mixed> $block_query Query settings from block context.
 * @return array<string, mixed>|null Null when the default Query Loop query should be used.
 */
function skaflow_maybe_apply_related_posts_query_loop_vars( array $query, array $block_query ): ?array {
	$post_type = skaflow_query_loop_get_post_type_slug( $query['post_type'] ?? 'post' );

	if ( ! is_singular( $post_type ) ) {
		$query['post__in'] = array( 0 );

		return $query;
	}

	$post_id = (int) get_queried_object_id();

	if ( $post_id <= 0 ) {
		$query['post__in'] = array( 0 );

		return $query;
	}

	$taxonomy = skaflow_get_related_posts_taxonomy( $block_query );
	$term_ids = wp_get_post_terms(
		$post_id,
		$taxonomy,
		array(
			'fields' => 'ids',
		)
	);

	if ( is_wp_error( $term_ids ) ) {
		$term_ids = array();
	}

	$fallback_enabled = skaflow_query_loop_related_posts_fallback_enabled( $block_query );
	$has_related      = skaflow_related_posts_exist( $post_id, $post_type, $taxonomy, $term_ids );

	if ( ! $has_related ) {
		if ( $fallback_enabled ) {
			return null;
		}

		$query['post__in'] = array( 0 );

		return $query;
	}

	return skaflow_build_related_posts_query_vars( $query, $post_id, $taxonomy, $term_ids );
}

/**
 * Apply related-posts query vars to Query Loop blocks that opt in.
 *
 * Non-related loops keep the block's own `perPage`. Blog archives use the
 * main query, which follows Settings → Reading.
 *
 * @param array    $query Query vars for WP_Query.
 * @param WP_Block $block Block instance.
 * @param int      $page  Current page.
 * @return array
 */
function skaflow_post_query_loop_block_query_vars( array $query, WP_Block $block, int $page ): array {
	unset( $page );

	if ( ! skaflow_is_query_loop_secondary_query_block( $block ) ) {
		return $query;
	}

	$block_query = $block->context['query'];

	if ( ! skaflow_query_loop_is_related_posts_enabled( $block_query ) ) {
		return $query;
	}

	$related_query = skaflow_maybe_apply_related_posts_query_loop_vars( $query, $block_query );

	if ( null === $related_query ) {
		return skaflow_apply_related_posts_fallback_query_vars( $query, $block_query );
	}

	return $related_query;
}

add_filter( 'query_loop_block_query_vars', 'skaflow_post_query_loop_block_query_vars', 10, 3 );
