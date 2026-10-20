<?php
/**
 * Product archive layered-nav — parse, URLs, facet counts, and query apply.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Valid panel orientation values.
 *
 * @return string[]
 */
function skaflow_get_filters_panel_orientation_choices(): array {
	return array( 'vertical', 'horizontal' );
}

/**
 * Filters panel orientation for desktop archives.
 *
 * @return string `vertical`|`horizontal`
 */
function skaflow_get_filters_panel_desktop(): string {
	$value = function_exists( 'get_field' ) ? get_field( 'filters_panel_desktop', 'option' ) : 'vertical';

	return in_array( $value, skaflow_get_filters_panel_orientation_choices(), true ) ? $value : 'vertical';
}

/**
 * Filters panel orientation for mobile archives.
 *
 * @return string `vertical`|`horizontal`
 */
function skaflow_get_filters_panel_mobile(): string {
	$value = function_exists( 'get_field' ) ? get_field( 'filters_panel_mobile', 'option' ) : 'vertical';

	return in_array( $value, skaflow_get_filters_panel_orientation_choices(), true ) ? $value : 'vertical';
}

/**
 * Whether the current request is a WooCommerce product archive that supports layered filters.
 */
function skaflow_is_product_filter_archive(): bool {
	if (
		function_exists( 'is_shop' )
		&& ( is_shop() || is_product_taxonomy() || is_post_type_archive( 'product' ) )
	) {
		return true;
	}

	global $wp_query;

	if ( $wp_query instanceof \WP_Query ) {
		$post_types = (array) ( $wp_query->get( 'post_type' ) ?: 'product' );

		if ( in_array( 'product', $post_types, true ) && 'product_query' === $wp_query->get( 'wc_query' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Layered-nav query type for an attribute.
 *
 * Must match `WC_Query::get_layered_nav_chosen_attributes()` — WooCommerce and
 * Woodmart default to `and`, which keeps the active attribute in facet counts so
 * only terms on products in the current filtered set remain visible.
 *
 * @param string $attribute_taxonomy Full taxonomy name e.g. `pa_colour`.
 * @return string `and`|`or`
 */
function skaflow_get_layered_nav_query_type( string $attribute_taxonomy ): string {

	$attribute_slug = wc_attribute_taxonomy_slug( $attribute_taxonomy );

	if ( class_exists( 'WC_Query' ) ) {
		$chosen = \WC_Query::get_layered_nav_chosen_attributes();

		if ( isset( $chosen[ $attribute_taxonomy ]['query_type'] ) ) {
			$query_type = (string) $chosen[ $attribute_taxonomy ]['query_type'];

			if ( in_array( $query_type, array( 'and', 'or' ), true ) ) {
				return $query_type;
			}
		}
	}

	if ( isset( $_GET[ 'query_type_' . $attribute_slug ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$query_type = wc_clean( wp_unslash( (string) $_GET[ 'query_type_' . $attribute_slug ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( in_array( $query_type, array( 'and', 'or' ), true ) ) {
			return $query_type;
		}
	}

	/**
	 * Same default as `WC_Query::get_layered_nav_chosen_attributes()` and Woodmart
	 * layered-nav widgets (`query_type` std = `and`).
	 */
	return (string) apply_filters( 'woocommerce_layered_nav_default_query_type', 'and' );
}

/**
 * WC Filterer service when available.
 *
 * @return object|null
 */
function skaflow_get_wc_product_filterer() {
	if ( ! function_exists( 'wc_get_container' ) ) {
		return null;
	}

	if ( ! class_exists( '\Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer' ) ) {
		return null;
	}

	try {
		return wc_get_container()->get( \Automattic\WooCommerce\Internal\ProductAttributesLookup\Filterer::class );
	} catch ( \Throwable $e ) {
		return null;
	}
}

/**
 * Facet counts for terms in the current main-query archive context.
 *
 * @param int[]  $term_ids Term IDs.
 * @param string $taxonomy Attribute taxonomy e.g. `pa_colour`.
 * @return array<int, int> term_id => count
 */
function skaflow_get_filtered_term_product_counts( array $term_ids, string $taxonomy ): array {
	$term_ids = array_values( array_filter( array_map( 'absint', $term_ids ) ) );

	if ( empty( $term_ids ) || ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}

	$filterer = skaflow_get_wc_product_filterer();

	if ( ! $filterer || ! method_exists( $filterer, 'get_filtered_term_product_counts' ) ) {
		return array();
	}

	$query_type = skaflow_get_layered_nav_query_type( $taxonomy );
	$counts     = $filterer->get_filtered_term_product_counts( $term_ids, $taxonomy, $query_type );

	return is_array( $counts ) ? $counts : array();
}

/**
 * Base catalog URL for layered-nav links on the current archive.
 *
 * @return string
 */
function skaflow_get_catalog_filter_base_url(): string {
	if ( is_shop() ) {
		return (string) wc_get_page_permalink( 'shop' );
	}

	if ( is_product_taxonomy() ) {
		$term = get_queried_object();

		if ( $term instanceof \WP_Term ) {
			$link = get_term_link( $term );

			if ( ! is_wp_error( $link ) ) {
				return (string) $link;
			}
		}
	}

	if ( is_post_type_archive( 'product' ) ) {
		$link = get_post_type_archive_link( 'product' );

		if ( is_string( $link ) && '' !== $link ) {
			return $link;
		}
	}

	return (string) wc_get_page_permalink( 'shop' );
}

/**
 * Parse layered-nav filter params from a query string or $_GET-style array.
 *
 * @param string|array<string, mixed>|null $source Query string, array, or null for $_GET.
 * @return array<string, string> Map of param name => value (`filter_colour`, `min_price`, …).
 */
function skaflow_parse_catalog_filter_params( $source = null ): array {
	if ( null === $source ) {
		$source = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$params = array();

	if ( is_string( $source ) ) {
		parse_str( ltrim( $source, '?' ), $params );
	} elseif ( is_array( $source ) ) {
		$params = $source;
	}

	$filters = array();

	foreach ( $params as $key => $value ) {
		if ( ! is_string( $key ) || ! is_scalar( $value ) ) {
			continue;
		}

		$string_value = sanitize_text_field( (string) $value );

		if ( '' === $string_value ) {
			continue;
		}

		if ( str_starts_with( $key, 'filter_' ) || in_array( $key, array( 'min_price', 'max_price', 'orderby', 'order' ), true ) ) {
			$filters[ $key ] = $string_value;
		}
	}

	return $filters;
}

/**
 * Layered-nav params only (excludes sort params unless explicitly passed in $changes).
 *
 * @return array<string, string>
 */
function skaflow_get_active_catalog_filter_params(): array {
	$params = skaflow_parse_catalog_filter_params();
	$active = array();

	foreach ( $params as $key => $value ) {
		if ( str_starts_with( $key, 'filter_' ) || in_array( $key, array( 'min_price', 'max_price' ), true ) ) {
			$active[ $key ] = $value;
		}
	}

	return $active;
}

/**
 * Build a catalog URL preserving active filter params with optional overrides/removals.
 *
 * Sort params (`orderby` / `order`) are only included when explicitly set in `$changes`.
 *
 * @param array<string, string|null> $changes Param => value; null removes param.
 * @param string|null                $base_url Base URL; defaults to current archive base.
 * @return string
 */
function skaflow_build_catalog_filter_url( array $changes, ?string $base_url = null ): string {
	$base_url = $base_url ? $base_url : skaflow_get_catalog_filter_base_url();
	$params   = skaflow_get_active_catalog_filter_params();

	foreach ( $changes as $key => $value ) {
		if ( null === $value || '' === $value ) {
			unset( $params[ $key ] );
			continue;
		}

		$params[ $key ] = $value;
	}

	unset( $params['paged'], $params['page'] );

	if ( empty( $params ) ) {
		return $base_url;
	}

	return add_query_arg( $params, $base_url );
}

/**
 * Toggle one attribute term in the layered-nav query string (OR within attribute).
 *
 * @param string $taxonomy  Attribute taxonomy e.g. `pa_colour`.
 * @param string $term_slug Term slug.
 * @return string URL with updated filter param.
 */
function skaflow_build_layered_nav_toggle_url( string $taxonomy, string $term_slug ): string {
	$term_slug = sanitize_title( $term_slug );

	if ( ! taxonomy_exists( $taxonomy ) || '' === $term_slug ) {
		return skaflow_get_catalog_filter_base_url();
	}

	$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
	$params      = skaflow_parse_catalog_filter_params();
	$current     = array();

	if ( ! empty( $params[ $filter_name ] ) ) {
		$current = array_filter(
			array_map(
				'sanitize_title',
				explode( ',', $params[ $filter_name ] )
			)
		);
	}

	if ( in_array( $term_slug, $current, true ) ) {
		$current = array_values( array_diff( $current, array( $term_slug ) ) );
	} else {
		$current[] = $term_slug;
	}

	return skaflow_build_catalog_filter_url(
		array(
			$filter_name => empty( $current ) ? null : implode( ',', $current ),
		)
	);
}

/**
 * Parse filter params from a REST request (query_vars JSON + explicit filter_* args).
 *
 * @param \WP_REST_Request $request REST request.
 * @return array<string, string>
 */
function skaflow_parse_catalog_filter_params_from_request( \WP_REST_Request $request ): array {
	$params = skaflow_parse_catalog_filter_params( $request->get_query_params() );

	$query_vars_raw = $request->get_param( 'query_vars' );

	if ( is_string( $query_vars_raw ) && '' !== $query_vars_raw ) {
		$decoded = json_decode( $query_vars_raw, true );

		if ( is_array( $decoded ) ) {
			foreach ( $decoded as $key => $value ) {
				if ( ! is_string( $key ) || ! is_scalar( $value ) ) {
					continue;
				}

				if (
					str_starts_with( $key, 'filter_' )
					|| in_array( $key, array( 'min_price', 'max_price', 'orderby', 'order' ), true )
				) {
					$params[ $key ] = sanitize_text_field( (string) $value );
				}
			}
		}
	}

	return $params;
}

/**
 * Apply layered-nav + price params to WP_Query args using WooCommerce helpers.
 *
 * @param array<string, mixed>  $query_args    WP_Query args.
 * @param array<string, string> $filter_params Catalog filter params.
 * @return array<string, mixed>
 */
function skaflow_apply_catalog_filter_params_to_query( array $query_args, array $filter_params ): array {
	if ( empty( $filter_params ) || ! function_exists( 'WC' ) || ! WC()->query instanceof \WC_Query ) {
		return $query_args;
	}

	$backup_get = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	foreach ( $filter_params as $key => $value ) {
		$_GET[ $key ] = $value; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$wc_query  = WC()->query;
	$tax_query = isset( $query_args['tax_query'] ) && is_array( $query_args['tax_query'] )
		? $query_args['tax_query']
		: array();

	$merged_tax_query = $wc_query->get_tax_query( $tax_query, true );

	if ( ! empty( $merged_tax_query ) ) {
		$query_args['tax_query'] = $merged_tax_query;
	}

	$meta_query = isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] )
		? $query_args['meta_query']
		: array();

	$merged_meta_query = $wc_query->get_meta_query( $meta_query, true );

	if ( ! empty( $merged_meta_query ) ) {
		$query_args['meta_query'] = $merged_meta_query;
	}

	if ( ! empty( $filter_params['min_price'] ) ) {
		$query_args['min_price'] = (float) $filter_params['min_price'];
	}

	if ( ! empty( $filter_params['max_price'] ) ) {
		$query_args['max_price'] = (float) $filter_params['max_price'];
	}

	$_GET = $backup_get; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	return $query_args;
}

/**
 * Bootstrap main query context so Filterer facet counts match a catalog query.
 *
 * @param array<string, mixed>  $query_args    WP_Query args (page 1).
 * @param array<string, string> $filter_params Active filter params.
 * @return \WP_Query
 */
function skaflow_bootstrap_product_filter_main_query( array $query_args, array $filter_params ): \WP_Query {
	global $wp_query;

	$previous_query = ( $wp_query instanceof \WP_Query ) ? $wp_query : null;
	$query          = new \WP_Query( $query_args );

	$wp_query = $query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	if ( function_exists( 'wc_setup_loop' ) ) {
		wc_setup_loop(
			array(
				'total'        => (int) $query->found_posts,
				'total_pages'  => (int) $query->max_num_pages,
				'per_page'     => (int) $query->get( 'posts_per_page' ),
				'current_page' => max( 1, (int) $query->get( 'paged', 1 ) ),
				'is_shortcode' => false,
				'is_search'    => false,
			)
		);
	}

	$backup_get = $_GET; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	foreach ( $filter_params as $key => $value ) {
		$_GET[ $key ] = $value; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	return $query;
}

/**
 * Restore global query after temporary bootstrap.
 *
 * @param \WP_Query|null $previous_query Previous global query.
 */
function skaflow_restore_product_filter_main_query( ?\WP_Query $previous_query ): void {
	global $wp_query;

	$wp_query = $previous_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

	if ( function_exists( 'wc_reset_loop' ) ) {
		wc_reset_loop();
	}
}

/**
 * Clear-all URL for active catalog filters (base archive URL, no query params).
 *
 * @return string
 */
function skaflow_get_clear_catalog_filters_url(): string {
	return skaflow_get_catalog_filter_base_url();
}
