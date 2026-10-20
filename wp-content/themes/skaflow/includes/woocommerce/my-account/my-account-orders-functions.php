<?php
/**
 * My Account Orders list and filters.
 *
 * @package Skaflow
 */

defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_my_account_my_orders_query', 'skaflow_filter_account_orders_query' );

/**
 * GET keys for the Orders filter bar. Prefixed so they do not collide with WC.
 */
function skaflow_get_account_orders_filter_keys(): array {
	return array(
		'number' => 'sk_number',
		'status' => 'sk_status',
		'date'   => 'sk_date',
		'sort'   => 'sk_sort',
	);
}

/**
 * Date presets available on the Orders bar.
 *
 * @return array<string, string>
 */
function skaflow_get_account_orders_date_choices(): array {
	return array(
		''     => __( 'All time', 'skaflow' ),
		'7d'   => __( 'Last 7 days', 'skaflow' ),
		'30d'  => __( 'Last 30 days', 'skaflow' ),
		'90d'  => __( 'Last 3 months', 'skaflow' ),
		'year' => __( 'This year', 'skaflow' ),
	);
}

/**
 * Sort choices — WC date order only.
 *
 * @return array<string, string>
 */
function skaflow_get_account_orders_sort_choices(): array {
	return array(
		''       => __( 'Most recent', 'skaflow' ),
		'oldest' => __( 'Oldest', 'skaflow' ),
	);
}

/**
 * Status choices from WooCommerce, keyed by the slug `wc_get_orders` accepts.
 *
 * @return array<string, string>
 */
function skaflow_get_account_orders_status_choices(): array {
	$choices = array(
		'' => __( 'All statuses', 'skaflow' ),
	);

	if ( ! function_exists( 'wc_get_order_statuses' ) ) {
		return $choices;
	}

	foreach ( wc_get_order_statuses() as $key => $label ) {
		$slug = str_starts_with( $key, 'wc-' ) ? substr( $key, 3 ) : $key;

		if ( 'checkout-draft' === $slug || str_starts_with( $slug, 'ywraq-' ) ) {
			continue;
		}

		$choices[ $slug ] = $label;
	}

	return $choices;
}

/**
 * Sanitized filter values from the request.
 *
 * @return array{number: string, status: string, date: string, sort: string}
 */
function skaflow_get_account_orders_filters(): array {
	$keys    = skaflow_get_account_orders_filter_keys();
	$filters = array(
		'number' => '',
		'status' => '',
		'date'   => '',
		'sort'   => '',
	);

	if ( isset( $_GET[ $keys['number'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$filters['number'] = skaflow_normalize_account_order_number( wp_unslash( $_GET[ $keys['number'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	}

	if ( isset( $_GET[ $keys['status'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$status = sanitize_key( wp_unslash( $_GET[ $keys['status'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( skaflow_get_account_orders_status_choices()[ $status ] ) ) {
			$filters['status'] = $status;
		}
	}

	if ( isset( $_GET[ $keys['date'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$date = sanitize_key( wp_unslash( $_GET[ $keys['date'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( skaflow_get_account_orders_date_choices()[ $date ] ) ) {
			$filters['date'] = $date;
		}
	}

	if ( isset( $_GET[ $keys['sort'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$sort = sanitize_key( wp_unslash( $_GET[ $keys['sort'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( skaflow_get_account_orders_sort_choices()[ $sort ] ) ) {
			$filters['sort'] = $sort;
		}
	}

	return $filters;
}

/**
 * Strip # and surrounding space from a typed order number.
 */
function skaflow_normalize_account_order_number( $raw ): string {
	$number = trim( (string) $raw );
	$number = ltrim( $number, '#' );
	$number = trim( $number );

	return $number;
}

/**
 * Whether any Orders filter is active.
 *
 * @param array{number: string, status: string, date: string, sort: string} $filters Filter values.
 */
function skaflow_account_orders_filters_are_active( array $filters ): bool {
	return '' !== $filters['number'] || '' !== $filters['status'] || '' !== $filters['date'] || '' !== $filters['sort'];
}

/**
 * Query args to persist on pagination / Clear (defaults omitted).
 *
 * @param array{number: string, status: string, date: string, sort: string} $filters Filter values.
 * @return array<string, string>
 */
function skaflow_get_account_orders_filter_query_args( array $filters ): array {
	$keys = skaflow_get_account_orders_filter_keys();
	$args = array();

	foreach ( $keys as $field => $key ) {
		if ( '' !== $filters[ $field ] ) {
			$args[ $key ] = $filters[ $field ];
		}
	}

	return $args;
}

/**
 * Restrict WC's account Orders query. Never runs on Overview (not the orders endpoint).
 *
 * @param array<string, mixed> $args wc_get_orders args.
 * @return array<string, mixed>
 */
function skaflow_filter_account_orders_query( array $args ): array {
	if ( is_admin() && ! wp_doing_ajax() ) {
		return $args;
	}

	if ( ! function_exists( 'is_wc_endpoint_url' ) || ! is_wc_endpoint_url( 'orders' ) ) {
		return $args;
	}

	$filters         = skaflow_get_account_orders_filters();
	$customer        = isset( $args['customer'] ) ? (int) $args['customer'] : get_current_user_id();
	$args['orderby'] = 'date';
	$args['order']   = 'oldest' === $filters['sort'] ? 'ASC' : 'DESC';

	if ( '' !== $filters['status'] ) {
		$args['status'] = $filters['status'];
	}

	$after = skaflow_get_account_orders_date_after( $filters['date'] );

	if ( '' !== $after ) {
		$args['date_created'] = '>' . $after;
	}

	if ( '' !== $filters['number'] ) {
		$order_ids = skaflow_resolve_account_order_ids_by_visible_number( $filters['number'], $customer );
		// `id => 0` is empty. `include => [0]` is every order — never use that.
		$args['id'] = ! empty( $order_ids ) ? $order_ids : 0;
	}

	return $args;
}

/**
 * Start of the date preset, or empty for all time.
 */
function skaflow_get_account_orders_date_after( string $preset ): string {
	switch ( $preset ) {
		case '7d':
			return gmdate( 'Y-m-d', strtotime( '-7 days' ) );
		case '30d':
			return gmdate( 'Y-m-d', strtotime( '-30 days' ) );
		case '90d':
			return gmdate( 'Y-m-d', strtotime( '-3 months' ) );
		case 'year':
			return gmdate( 'Y-01-01' );
		default:
			return '';
	}
}

/**
 * Resolve a typed fragment to this customer's order IDs via the visible number.
 *
 * Matches a case-insensitive substring of `$order->get_order_number()`, not the
 * raw database ID. Sequential-number plugins remap that value.
 *
 * @return int[] Matching order IDs, or empty when nothing matches.
 */
function skaflow_resolve_account_order_ids_by_visible_number( string $number, int $customer_id ): array {
	if ( '' === $number || $customer_id < 1 || ! function_exists( 'wc_get_orders' ) ) {
		return array();
	}

	$orders = wc_get_orders(
		array(
			'customer_id' => $customer_id,
			'limit'       => -1,
			'return'      => 'objects',
		)
	);

	if ( ! is_array( $orders ) ) {
		return array();
	}

	$ids = array();

	foreach ( $orders as $order ) {
		if ( ! $order instanceof WC_Order ) {
			continue;
		}

		if ( false !== stripos( (string) $order->get_order_number(), $number ) ) {
			$ids[] = (int) $order->get_id();
		}
	}

	return $ids;
}

/**
 * Orders endpoint: heading, filters, cards or empty, WC pagination.
 *
 * @param object|null $customer_orders WC paginated orders object.
 */
function skaflow_render_account_orders( $customer_orders, bool $has_orders, int $current_page ): void {
	$filters = skaflow_get_account_orders_filters();
	$active  = skaflow_account_orders_filters_are_active( $filters );

	$total = ( is_object( $customer_orders ) && isset( $customer_orders->total ) )
		? (int) $customer_orders->total
		: 0;

	echo '<div class="sk-account-orders-page">';
	echo '<header class="sk-account-orders__head">';
	echo '<div class="sk-account-orders__head-intro">';
	echo '<h2 class="wp-block-heading">' . esc_html__( 'Order history', 'skaflow' ) . '</h2>';
	echo '<p class="is-style-text-lead has-small-font-size">' . esc_html__( 'View and manage all your past orders', 'skaflow' ) . '</p>';
	echo '</div>';
	echo '<div class="sk-account-orders__head-orders-count">';
	printf(
		/* translators: %s: number of matching orders */
		esc_html( _n( '%s order', '%s orders', $total, 'skaflow' ) ),
		esc_html( number_format_i18n( $total ) )
	);
	echo '</div>';
	echo '</header>';

	if ( $has_orders || $active ) {
		skaflow_render_account_orders_filters( $filters );
	}

	if ( ! $has_orders ) {
		if ( $active ) {
			echo skaflow_get_account_orders_empty_filtered_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
		} else {
			echo skaflow_get_account_orders_empty_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
		}

		echo '</div>';

		return;
	}

	echo '<div class="sk-account-orders__list">';

	$raw = ( is_object( $customer_orders ) && isset( $customer_orders->orders ) && is_array( $customer_orders->orders ) )
		? $customer_orders->orders
		: array();

	foreach ( $raw as $customer_order ) {
		$order = $customer_order instanceof WC_Order ? $customer_order : wc_get_order( $customer_order );

		if ( $order instanceof WC_Order ) {
			skaflow_render_account_order_card( $order );
		}
	}

	echo '</div>';

	do_action( 'woocommerce_before_account_orders_pagination' );
	echo skaflow_get_account_orders_pagination_markup( $customer_orders, $current_page, $filters ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
	echo '</div>';
}

/**
 * Filter bar — GET form so pagination can repeat the same args.
 *
 * @param array{number: string, status: string, date: string, sort: string} $filters Filter values.
 */
function skaflow_render_account_orders_filters( array $filters ): void {
	get_template_part(
		'partials/account-orders',
		'filters',
		array(
			'filters' => $filters,
		)
	);
}

/**
 * Empty Orders list — customer has never ordered.
 */
function skaflow_get_account_orders_empty_markup(): string {
	$html  = '<div class="sk-account-orders__empty is-style-bordered">';
	$html .= '<p>' . esc_html__( 'No order has been made yet.', 'woocommerce' ) . '</p>';

	if ( function_exists( 'wc_get_page_permalink' ) && function_exists( 'skaflow_get_buttons_markup' ) ) {
		$html .= skaflow_get_buttons_markup(
			array(
				array(
					'label' => __( 'Browse products', 'woocommerce' ),
					'url'   => apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
					'style' => 'is-style-light',
					'class' => 'has-small-font-size',
				),
			)
		);
	}

	$html .= '</div>';

	return $html;
}

/**
 * Empty Orders list — filters matched nothing.
 */
function skaflow_get_account_orders_empty_filtered_markup(): string {
	$html  = '<div class="sk-account-orders__empty is-style-bordered">';
	$html .= '<p>' . esc_html__( 'No orders match these filters.', 'skaflow' ) . '</p>';

	if ( function_exists( 'wc_get_account_endpoint_url' ) && function_exists( 'skaflow_get_buttons_markup' ) ) {
		$html .= skaflow_get_buttons_markup(
			array(
				array(
					'label' => __( 'Clear filters', 'skaflow' ),
					'url'   => wc_get_account_endpoint_url( 'orders' ),
					'style' => 'is-style-link',
					'class' => 'has-small-font-size',
				),
			)
		);
	}

	$html .= '</div>';

	return $html;
}

/**
 * Numbered pager — WP `paginate_links()`, WC endpoint URLs, filters preserved.
 *
 * @param object|null                                                       $customer_orders WC paginated orders object.
 * @param int                                                               $current_page    Current page number.
 * @param array{number: string, status: string, date: string, sort: string} $filters         Filter values.
 */
function skaflow_get_account_orders_pagination_markup( $customer_orders, int $current_page, array $filters = array() ): string {
	if ( ! is_object( $customer_orders ) || ! isset( $customer_orders->max_num_pages ) ) {
		return '';
	}

	$max = (int) $customer_orders->max_num_pages;

	if ( $max < 2 || ! function_exists( 'wc_get_account_endpoint_url' ) ) {
		return '';
	}

	$endpoint = wc_get_account_endpoint_url( 'orders' );
	$query    = skaflow_get_account_orders_filter_query_args( $filters );
	$links    = paginate_links(
		array(
			'base'      => trailingslashit( $endpoint ) . '%_%',
			'format'    => user_trailingslashit( '%#%', 'single' ),
			'current'   => max( 1, $current_page ),
			'total'     => $max,
			'add_args'  => $query,
			'type'      => 'list',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
		)
	);

	if ( ! is_string( $links ) || '' === $links ) {
		return '';
	}

	return '<nav class="woocommerce-pagination sk-account-orders-page__pagination" aria-label="' . esc_attr__( 'Orders pagination', 'skaflow' ) . '">' . $links . '</nav>';
}
