<?php
/**
 * Commerce Load More — catalog per-page, orderby, and shop query gates.
 *
 * Generic replay lives in `includes/load-more-functions.php`. This file plugs
 * WooCommerce catalog behaviour in through filters so the root file stays
 * deletable-capability clean.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Products per page for shop loops (Customizer columns × rows, filterable).
 */
function skaflow_get_loop_shop_per_page(): int {

	if ( function_exists( 'wc_get_default_products_per_row' ) && function_exists( 'wc_get_default_product_rows_per_page' ) ) {
		return (int) apply_filters(
			'loop_shop_per_page',
			wc_get_default_products_per_row() * wc_get_default_product_rows_per_page()
		);
	}

	$columns = max( 1, (int) get_option( 'woocommerce_catalog_columns', 4 ) );
	$rows    = max( 1, (int) get_option( 'woocommerce_catalog_rows', 4 ) );

	return (int) apply_filters( 'loop_shop_per_page', $columns * $rows );
}

/**
 * Sanitize a catalog orderby value without breaking WC compound keys.
 *
 * WooCommerce defaults to `menu_order title` (space-separated). sanitize_key()
 * strips spaces and would corrupt that to `menu_ordertitle`, breaking pagination.
 *
 * @param string $orderby Raw orderby value.
 * @return string
 */
function skaflow_sanitize_catalog_orderby( string $orderby ): string {

	$orderby = strtolower( trim( sanitize_text_field( $orderby ) ) );

	if ( '' === $orderby ) {
		return '';
	}

	if ( str_contains( $orderby, ' ' ) ) {
		$parts = preg_split( '/\s+/', $orderby ) ?: array();

		return implode(
			' ',
			array_values(
				array_filter(
					array_map(
						static function ( $part ) {
							return sanitize_key( (string) $part );
						},
						$parts
					)
				)
			)
		);
	}

	return sanitize_key( $orderby );
}

/**
 * Split WooCommerce hyphenated catalog orderby values (e.g. price-desc).
 *
 * Matches WC_Query::get_catalog_ordering_args() when reading ?orderby=price-desc.
 *
 * @param string $orderby Raw orderby value.
 * @param string $order   Optional explicit order (ASC|DESC).
 * @return array{orderby: string, order: string} Normalized orderby + order.
 */
function skaflow_parse_catalog_orderby( string $orderby, string $order = '' ): array {

	$orderby = strtolower( trim( sanitize_text_field( $orderby ) ) );
	$order   = strtoupper( trim( sanitize_text_field( $order ) ) );

	if ( str_contains( $orderby, '-' ) ) {
		$parts   = explode( '-', $orderby );
		$orderby = (string) ( $parts[0] ?? $orderby );

		if ( '' === $order && ! empty( $parts[1] ) ) {
			$order = strtoupper( (string) $parts[1] );
		}
	}

	$orderby = skaflow_sanitize_catalog_orderby( $orderby );

	if ( ! in_array( $order, array( 'ASC', 'DESC' ), true ) ) {
		$order = '';
	}

	return array(
		'orderby' => $orderby,
		'order'   => $order,
	);
}

/**
 * Normalize orderby/order inside replayed Load More query vars.
 *
 * @param array<string, mixed> $query_vars Query vars array.
 * @return array<string, mixed>
 */
function skaflow_normalize_load_more_ordering( array $query_vars ): array {

	if ( empty( $query_vars['orderby'] ) || ! is_string( $query_vars['orderby'] ) ) {
		return $query_vars;
	}

	$parsed = skaflow_parse_catalog_orderby(
		$query_vars['orderby'],
		isset( $query_vars['order'] ) && is_string( $query_vars['order'] ) ? $query_vars['order'] : ''
	);

	$query_vars['orderby'] = $parsed['orderby'];

	if ( '' !== $parsed['order'] ) {
		$query_vars['order'] = $parsed['order'];
	}

	return $query_vars;
}

/**
 * Apply WooCommerce catalog ordering to Load More query args.
 *
 * Plain `orderby => price` (and popularity/rating) is not enough — WC registers
 * `posts_clauses` filters via WC_Query::get_catalog_ordering_args().
 *
 * @param array<string, mixed> $query_args WP_Query args.
 * @return array<string, mixed>
 */
function skaflow_apply_load_more_catalog_ordering( array $query_args ): array {

	$post_types = (array) ( $query_args['post_type'] ?? 'product' );

	if (
		! in_array( 'product', $post_types, true )
		&& ! in_array( 'product_variation', $post_types, true )
	) {
		return $query_args;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->query instanceof \WC_Query ) {
		return $query_args;
	}

	$orderby = isset( $query_args['orderby'] ) ? (string) $query_args['orderby'] : '';
	$order   = isset( $query_args['order'] ) ? (string) $query_args['order'] : '';

	$parsed   = skaflow_parse_catalog_orderby( $orderby, $order );
	$ordering = WC()->query->get_catalog_ordering_args( $parsed['orderby'], $parsed['order'] );

	$query_args             = array_merge( $query_args, $ordering );
	$query_args['wc_query'] = 'product_query';

	return $query_args;
}

/**
 * @param int $per_page Current Load More per-page count.
 */
function skaflow_filter_load_more_per_page( int $per_page ): int {

	unset( $per_page );

	return skaflow_get_loop_shop_per_page();
}

/**
 * @param bool     $export Whether to export the main query.
 * @param WP_Query $query  Main query.
 */
function skaflow_filter_load_more_should_export_main_query( bool $export, $query ): bool {

	unset( $query );

	if ( $export ) {
		return true;
	}

	return function_exists( 'is_shop' )
		&& ( is_shop() || is_product_taxonomy() || is_post_type_archive( 'product' ) );
}

/**
 * @param string $post_type Current default post type.
 */
function skaflow_filter_load_more_default_post_type( string $post_type ): string {

	unset( $post_type );

	return 'product';
}

/**
 * @param string $sanitized sanitize_text_field() result.
 * @param string $raw       Original orderby value.
 */
function skaflow_filter_sanitize_load_more_orderby( string $sanitized, string $raw ): string {

	unset( $sanitized );

	return skaflow_sanitize_catalog_orderby( $raw );
}

/**
 * @param array<string, mixed>  $query_args    Built query args.
 * @param array<string, mixed>  $client_vars   Sanitized client vars.
 * @param int                   $page          Requested page.
 * @param array<string, string> $filter_params Catalog filters.
 * @return array<string, mixed>
 */
function skaflow_filter_load_more_query_args( array $query_args, array $client_vars, int $page, array $filter_params ): array {

	unset( $client_vars, $page );

	$query_args = skaflow_apply_load_more_catalog_ordering( $query_args );

	if ( function_exists( 'skaflow_apply_catalog_filter_params_to_query' ) ) {
		$query_args = skaflow_apply_catalog_filter_params_to_query( $query_args, $filter_params );
	}

	return $query_args;
}

add_filter( 'skaflow_load_more_per_page', 'skaflow_filter_load_more_per_page' );
add_filter( 'skaflow_load_more_should_export_main_query', 'skaflow_filter_load_more_should_export_main_query', 10, 2 );
add_filter( 'skaflow_load_more_default_post_type', 'skaflow_filter_load_more_default_post_type' );
add_filter( 'skaflow_sanitize_load_more_orderby', 'skaflow_filter_sanitize_load_more_orderby', 10, 2 );
add_filter( 'skaflow_load_more_normalized_query_vars', 'skaflow_normalize_load_more_ordering' );
add_filter( 'skaflow_load_more_query_args', 'skaflow_filter_load_more_query_args', 10, 4 );
