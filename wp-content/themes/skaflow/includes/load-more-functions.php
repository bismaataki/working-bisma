<?php
/**
 * Load More helpers — generic query replay. Commerce catalog branches live in
 * `includes/woocommerce/load-more-functions.php`.
 *
 * Shop append uses page-fetch in `src/js/shared/load-more.js` (SSR cards from
 * FSE templates). Card HTML is not rendered here.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Posts per page for a Load More request.
 *
 * Default is the Reading Settings value. Commerce replaces this with
 * Customizer catalog columns × rows via `skaflow_load_more_per_page`.
 */
function skaflow_get_load_more_per_page(): int {

	return max(
		1,
		(int) apply_filters(
			'skaflow_load_more_per_page',
			(int) get_option( 'posts_per_page', 10 )
		)
	);
}

/**
 * Query var keys that must not be replayed from the AJAX payload.
 *
 * @return string[]
 */
function skaflow_get_load_more_excluded_query_var_keys(): array {

	return array(
		'paged',
		'page',
		'posts_per_page',
		'offset',
		'nopaging',
		'no_found_rows',
		'fields',
		'cache_results',
		'update_post_meta_cache',
		'update_post_term_cache',
		'lazy_load_term_meta',
		'suppress_filters',
		'error',
		'm',
		'p',
		'attachment',
		'attachment_id',
		'subpost',
		'subpost_id',
		'preview',
		'perm',
		'comments_per_page',
		'posts_per_archive_page',
	);
}

/**
 * Export main-query vars from page 1 for the Load More AJAX payload.
 *
 * @return array<string, mixed>
 */
function skaflow_get_load_more_query_vars_for_client( ?\WP_Query $query = null ): array {

	global $wp_query;

	$source_query = $query instanceof \WP_Query ? $query : $wp_query;

	if ( ! $source_query instanceof \WP_Query ) {
		return array();
	}

	if ( null === $query && ! apply_filters( 'skaflow_load_more_should_export_main_query', false, $source_query ) ) {
		return array();
	}

	$export  = array();
	$exclude = array_flip( skaflow_get_load_more_excluded_query_var_keys() );

	foreach ( $source_query->query_vars as $key => $value ) {
		if ( isset( $exclude[ $key ] ) ) {
			continue;
		}

		if ( null === $value || '' === $value || array() === $value ) {
			continue;
		}

		$export[ $key ] = $value;
	}

	// Shop/category archives often omit post_type in query_vars; REST has no main query to infer it.
	$post_type = $export['post_type'] ?? '';

	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}

	if ( ! is_string( $post_type ) || '' === $post_type ) {
		$export['post_type'] = apply_filters( 'skaflow_load_more_default_post_type', 'post' );
	}

	return $export;
}

/**
 * Resolve post type from replayed query vars (defaults to main-query post type).
 *
 * @param array<string, mixed> $query_vars Query vars array.
 * @return string
 */
function skaflow_get_load_more_post_type_from_vars( array $query_vars ): string {

	$post_type = $query_vars['post_type'] ?? '';

	if ( is_array( $post_type ) ) {
		$post_type = reset( $post_type );
	}

	if ( is_string( $post_type ) && '' !== $post_type ) {
		return sanitize_key( $post_type );
	}

	return sanitize_key( (string) apply_filters( 'skaflow_load_more_default_post_type', 'post' ) );
}

/**
 * Sanitize replayed main-query vars from the Load More AJAX payload.
 *
 * @param array<string, mixed> $vars Raw decoded JSON from the client.
 * @return array<string, mixed>
 */
function skaflow_sanitize_load_more_query_vars( array $vars ): array {

	$allowed = array();
	$exclude = array_flip( skaflow_get_load_more_excluded_query_var_keys() );

	foreach ( $vars as $key => $value ) {
		if ( isset( $exclude[ $key ] ) ) {
			continue;
		}

		if ( 'post_type' === $key ) {
			if ( is_array( $value ) ) {
				$allowed['post_type'] = array_values(
					array_filter(
						array_map(
							static function ( $type ) {
								return sanitize_key( (string) $type );
							},
							$value
						)
					)
				);
			} elseif ( is_string( $value ) && '' !== $value ) {
				$allowed['post_type'] = sanitize_key( $value );
			}
			continue;
		}

		if ( 'post_status' === $key ) {
			if ( is_array( $value ) ) {
				$allowed['post_status'] = array_values(
					array_filter(
						array_map(
							static function ( $status ) {
								return sanitize_key( (string) $status );
							},
							$value
						)
					)
				);
			} elseif ( is_string( $value ) && '' !== $value ) {
				$allowed['post_status'] = sanitize_key( $value );
			}
			continue;
		}

		if ( 'tax_query' === $key && is_array( $value ) ) {
			$allowed['tax_query'] = skaflow_sanitize_load_more_tax_query( $value );
			continue;
		}

		if ( 'meta_query' === $key && is_array( $value ) ) {
			$allowed['meta_query'] = skaflow_sanitize_load_more_meta_query( $value );
			continue;
		}

		if ( in_array( $key, array( 'orderby', 'order', 's', 'meta_key', 'wc_query' ), true ) && is_string( $value ) ) {
			if ( 'orderby' === $key ) {
				$allowed[ $key ] = apply_filters( 'skaflow_sanitize_load_more_orderby', sanitize_text_field( $value ), $value );
			} else {
				$allowed[ $key ] = sanitize_text_field( $value );
			}
			continue;
		}

		if ( str_starts_with( $key, 'filter_' ) && is_string( $value ) ) {
			$allowed[ $key ] = sanitize_text_field( $value );
			continue;
		}

		if ( in_array( $key, array( 'min_price', 'max_price' ), true ) && is_scalar( $value ) ) {
			$allowed[ $key ] = sanitize_text_field( (string) $value );
		}
	}

	$post_type = skaflow_get_load_more_post_type_from_vars( $allowed );
	$tax_names = get_object_taxonomies( $post_type, 'names' );

	foreach ( $vars as $key => $value ) {
		if ( isset( $exclude[ $key ] ) || isset( $allowed[ $key ] ) ) {
			continue;
		}

		if ( in_array( $key, $tax_names, true ) ) {
			if ( is_array( $value ) ) {
				$allowed[ $key ] = array_map( 'sanitize_title', array_map( 'strval', $value ) );
			} else {
				$allowed[ $key ] = sanitize_title( (string) $value );
			}
		}
	}

	return apply_filters( 'skaflow_load_more_normalized_query_vars', $allowed );
}

/**
 * @param array<int, mixed> $tax_query Raw tax_query.
 * @return array<int, mixed>
 */
function skaflow_sanitize_load_more_tax_query( array $tax_query ): array {

	$clean = array();

	foreach ( $tax_query as $clause ) {
		if ( ! is_array( $clause ) ) {
			continue;
		}

		if ( isset( $clause['relation'] ) && in_array( $clause['relation'], array( 'AND', 'OR' ), true ) ) {
			$clean[] = array( 'relation' => $clause['relation'] );
			continue;
		}

		if ( empty( $clause['taxonomy'] ) || ! taxonomy_exists( (string) $clause['taxonomy'] ) ) {
			continue;
		}

		$field = isset( $clause['field'] ) ? sanitize_key( (string) $clause['field'] ) : 'term_id';
		$terms = $clause['terms'] ?? array();

		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		if ( in_array( $field, array( 'slug', 'name' ), true ) ) {
			$terms = array_values(
				array_filter(
					array_map(
						static function ( $term ) use ( $field ) {
							return 'name' === $field
								? sanitize_text_field( (string) $term )
								: sanitize_title( (string) $term );
						},
						$terms
					)
				)
			);
		} else {
			$terms = array_values( array_filter( array_map( 'absint', $terms ) ) );
		}

		if ( empty( $terms ) ) {
			continue;
		}

		$operator = isset( $clause['operator'] )
			? strtoupper( trim( sanitize_text_field( (string) $clause['operator'] ) ) )
			: 'IN';

		if ( ! in_array( $operator, array( 'IN', 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS' ), true ) ) {
			$operator = 'IN';
		}

		$sanitized = array(
			'taxonomy' => sanitize_key( $clause['taxonomy'] ),
			'field'    => $field,
			'terms'    => $terms,
			'operator' => $operator,
		);

		if ( isset( $clause['include_children'] ) ) {
			$sanitized['include_children'] = (bool) $clause['include_children'];
		}

		$clean[] = $sanitized;
	}

	return $clean;
}

/**
 * @param array<int, mixed> $meta_query Raw meta_query.
 * @return array<int, mixed>
 */
function skaflow_sanitize_load_more_meta_query( array $meta_query ): array {

	$clean = array();

	foreach ( $meta_query as $clause ) {
		if ( ! is_array( $clause ) ) {
			continue;
		}

		if ( isset( $clause['relation'] ) && in_array( $clause['relation'], array( 'AND', 'OR' ), true ) ) {
			$clean[] = array( 'relation' => $clause['relation'] );
			continue;
		}

		if ( empty( $clause['key'] ) ) {
			continue;
		}

		$clean[] = array(
			'key'     => sanitize_key( (string) $clause['key'] ),
			'value'   => isset( $clause['value'] ) ? sanitize_text_field( (string) $clause['value'] ) : '',
			'compare' => isset( $clause['compare'] ) ? sanitize_key( (string) $clause['compare'] ) : '=',
		);
	}

	return $clean;
}

/**
 * Strip replayed filter artifacts before rebuilding layered-nav tax/meta queries.
 *
 * @param array<string, mixed>  $client_vars   Sanitized query vars from the client.
 * @param array<string, string> $filter_params Active catalog filter params.
 * @return array<string, mixed>
 */
function skaflow_prepare_load_more_client_vars( array $client_vars, array $filter_params ): array {

	foreach ( array_keys( $client_vars ) as $key ) {
		if ( str_starts_with( $key, 'filter_' ) || in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
			unset( $client_vars[ $key ] );
		}
	}

	unset( $client_vars['taxonomy'], $client_vars['term'] );

	if ( ! empty( $filter_params ) ) {
		unset( $client_vars['tax_query'], $client_vars['meta_query'] );
	}

	return $client_vars;
}

/**
 * Build WP_Query args for a Load More request.
 *
 * Pagination is always derived server-side: calculated perPage + offset for the
 * requested page. Client payload must not replay posts_per_page, paged, or offset.
 *
 * @param array<string, mixed> $client_vars Sanitized query vars from AJAX.
 * @param int                  $page        Target page number (≥ 2).
 * @return array<string, mixed>
 */
function skaflow_build_load_more_query_args( array $client_vars, int $page, array $filter_params = array() ): array {

	$per_page = skaflow_get_load_more_per_page();
	$page     = max( 1, $page );

	$client_vars = skaflow_prepare_load_more_client_vars( $client_vars, $filter_params );

	foreach ( skaflow_get_load_more_excluded_query_var_keys() as $excluded_key ) {
		unset( $client_vars[ $excluded_key ] );
	}

	$query_args = wp_parse_args(
		$client_vars,
		array(
			'post_status' => 'publish',
		)
	);

	if ( empty( $query_args['post_type'] ) ) {
		$query_args['post_type'] = skaflow_get_load_more_post_type_from_vars( $client_vars );
	}

	$query_args['post_status']    = $query_args['post_status'] ?? 'publish';
	$query_args['posts_per_page'] = $per_page;
	$query_args['offset']         = ( $page - 1 ) * $per_page;

	unset( $query_args['paged'], $query_args['page'] );

	return apply_filters( 'skaflow_load_more_query_args', $query_args, $client_vars, $page, $filter_params );
}

/**
 * Max pages for Load More using the active per-page count.
 *
 * @param int      $found_posts Total matching posts.
 * @param int|null $per_page    Optional override; defaults to load-more per page.
 * @return int
 */
function skaflow_get_load_more_max_pages( int $found_posts, ?int $per_page = null ): int {

	$per_page = $per_page ?? skaflow_get_load_more_per_page();

	if ( $per_page <= 0 || $found_posts <= 0 ) {
		return 0;
	}

	return (int) ceil( $found_posts / $per_page );
}
