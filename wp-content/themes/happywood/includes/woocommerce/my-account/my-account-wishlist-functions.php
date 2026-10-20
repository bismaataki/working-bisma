<?php
/**
 * My Account wishlist endpoint — list saved products.
 *
 * IDs come from Happywood\Wishlist. Cards go through happywood/product-card,
 * which renders happywood_get_product_card_blocks_markup().
 *
 * @package Happywood
 */

defined( 'ABSPATH' ) || exit;

use Happywood\Wishlist;

add_filter( 'woocommerce_get_query_vars', 'happywood_add_account_wishlist_query_var' );
add_filter( 'woocommerce_account_menu_items', 'happywood_account_menu_add_wishlist', 25 );
add_action( 'woocommerce_account_wishlist_endpoint', 'happywood_account_wishlist_endpoint' );
add_action( 'wp_loaded', 'happywood_maybe_flush_account_wishlist_rewrites', 20 );

/**
 * Option that records the last flushed wishlist rewrite version.
 */
function happywood_get_account_wishlist_rewrite_version(): string {
	return '1';
}

/**
 * Register the WC query var so /my-account/wishlist/ resolves.
 *
 * @param array<string, string> $vars Endpoint => query var.
 * @return array<string, string>
 */
function happywood_add_account_wishlist_query_var( array $vars ): array {
	if ( ! happywood_is_wishlist_enabled() ) {
		return $vars;
	}

	$vars['wishlist'] = 'wishlist';

	return $vars;
}

/**
 * Insert Wishlist after Orders. Slug stays `wishlist`.
 *
 * @param array<string, string> $items Endpoint => label.
 * @return array<string, string>
 */
function happywood_account_menu_add_wishlist( array $items ): array {
	if ( ! happywood_is_wishlist_enabled() ) {
		return $items;
	}

	if ( isset( $items['wishlist'] ) ) {
		return $items;
	}

	$label   = __( 'Wishlist', 'happywood' );
	$ordered = array();
	$did     = false;

	foreach ( $items as $endpoint => $item_label ) {
		$ordered[ $endpoint ] = $item_label;

		if ( 'orders' === $endpoint ) {
			$ordered['wishlist'] = $label;
			$did                 = true;
		}
	}

	if ( ! $did ) {
		$ordered['wishlist'] = $label;
	}

	return $ordered;
}

/**
 * Flush rewrites once after the wishlist endpoint is introduced.
 */
function happywood_maybe_flush_account_wishlist_rewrites(): void {
	if ( ! happywood_is_wishlist_enabled() ) {
		return;
	}

	$version   = happywood_get_account_wishlist_rewrite_version();
	$installed = (string) get_option( 'happywood_wishlist_rewrite_version', '' );

	if ( $version === $installed ) {
		return;
	}

	flush_rewrite_rules( false );
	update_option( 'happywood_wishlist_rewrite_version', $version );
}

/**
 * WC endpoint callback — thin template shim.
 */
function happywood_account_wishlist_endpoint(): void {
	wc_get_template( 'myaccount/wishlist.php' );
}

/**
 * GET keys for the Wishlist filter bar. Prefixed so they do not collide with WC.
 *
 * @return array{search: string, category: string, date: string, sort: string}
 */
function happywood_get_account_wishlist_filter_keys(): array {
	return array(
		'search'   => 'hw_search',
		'category' => 'hw_category',
		'date'     => 'hw_date',
		'sort'     => 'hw_sort',
	);
}

/**
 * Primary-nav product categories (the shop's real top-level types).
 *
 * @return array<int, WP_Term> Term ID => term.
 */
function happywood_get_account_wishlist_nav_category_terms(): array {
	$terms = array();

	if ( ! defined( 'HAPPYWOOD_MENU_PRIMARY' ) ) {
		return $terms;
	}

	$locations = get_nav_menu_locations();
	$menu_id   = isset( $locations[ HAPPYWOOD_MENU_PRIMARY ] ) ? (int) $locations[ HAPPYWOOD_MENU_PRIMARY ] : 0;

	if ( $menu_id < 1 ) {
		return $terms;
	}

	$items = wp_get_nav_menu_items( $menu_id );

	if ( ! is_array( $items ) ) {
		return $terms;
	}

	foreach ( $items as $item ) {
		if ( ! $item instanceof WP_Post || 'product_cat' !== $item->object || (int) $item->menu_item_parent > 0 ) {
			continue;
		}

		$term = get_term( (int) $item->object_id, 'product_cat' );

		if ( $term instanceof WP_Term ) {
			$terms[ (int) $term->term_id ] = $term;
		}
	}

	return $terms;
}

/**
 * Whether a saved product sits in a category or one of its children.
 */
function happywood_account_wishlist_product_in_category( int $product_id, string $slug ): bool {
	if ( $product_id < 1 || '' === $slug ) {
		return false;
	}

	if ( has_term( $slug, 'product_cat', $product_id ) ) {
		return true;
	}

	$target = get_term_by( 'slug', $slug, 'product_cat' );

	if ( ! $target instanceof WP_Term ) {
		return false;
	}

	$assigned = wp_get_post_terms( $product_id, 'product_cat' );

	if ( is_wp_error( $assigned ) || ! is_array( $assigned ) ) {
		return false;
	}

	foreach ( $assigned as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}

		if ( (int) $term->term_id === (int) $target->term_id ) {
			return true;
		}

		if ( in_array( (int) $target->term_id, array_map( 'intval', get_ancestors( $term->term_id, 'product_cat' ) ), true ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Shop categories that appear on this customer's saved products.
 *
 * Uses the primary nav's product_cat items (Chairs, Stools, …), not every
 * SEO/location term assigned in the catalog.
 *
 * @param array<int, array{product_id: int, date_added: string}> $items Saved items.
 * @return array<string, string> Slug => name, plus an empty "All categories" key.
 */
function happywood_get_account_wishlist_category_choices( array $items ): array {
	$choices = array(
		'' => __( 'All categories', 'happywood' ),
	);

	$nav_terms = happywood_get_account_wishlist_nav_category_terms();

	if ( array() === $nav_terms || array() === $items ) {
		return $choices;
	}

	$matched = array();

	foreach ( $items as $item ) {
		$product_id = (int) $item['product_id'];

		foreach ( $nav_terms as $term ) {
			if ( happywood_account_wishlist_product_in_category( $product_id, $term->slug ) ) {
				$matched[ $term->slug ] = $term->name;
			}
		}
	}

	natcasesort( $matched );

	return $choices + $matched;
}

/**
 * Sanitized filter values from the request.
 *
 * @param array<int, array{product_id: int, date_added: string}> $items Saved items (for category whitelist).
 * @return array{search: string, category: string, date: string, sort: string}
 */
function happywood_get_account_wishlist_filters( array $items = array() ): array {
	$keys    = happywood_get_account_wishlist_filter_keys();
	$filters = array(
		'search'   => '',
		'category' => '',
		'date'     => '',
		'sort'     => '',
	);

	if ( isset( $_GET[ $keys['search'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$filters['search'] = sanitize_text_field( wp_unslash( $_GET[ $keys['search'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	if ( isset( $_GET[ $keys['category'] ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$category = sanitize_title( wp_unslash( $_GET[ $keys['category'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( happywood_get_account_wishlist_category_choices( $items )[ $category ] ) ) {
			$filters['category'] = $category;
		}
	}

	if ( isset( $_GET[ $keys['date'] ] ) && function_exists( 'happywood_get_account_orders_date_choices' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$date = sanitize_key( wp_unslash( $_GET[ $keys['date'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( happywood_get_account_orders_date_choices()[ $date ] ) ) {
			$filters['date'] = $date;
		}
	}

	if ( isset( $_GET[ $keys['sort'] ] ) && function_exists( 'happywood_get_account_orders_sort_choices' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- public GET filter.
		$sort = sanitize_key( wp_unslash( $_GET[ $keys['sort'] ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( happywood_get_account_orders_sort_choices()[ $sort ] ) ) {
			$filters['sort'] = $sort;
		}
	}

	return $filters;
}

/**
 * Whether any Wishlist filter is active.
 *
 * @param array{search: string, category: string, date: string, sort: string} $filters Filter values.
 */
function happywood_account_wishlist_filters_are_active( array $filters ): bool {
	return '' !== $filters['search'] || '' !== $filters['category'] || '' !== $filters['date'] || '' !== $filters['sort'];
}

/**
 * Query args to persist on pagination / Clear (defaults omitted).
 *
 * @param array{search: string, category: string, date: string, sort: string} $filters Filter values.
 * @return array<string, string>
 */
function happywood_get_account_wishlist_filter_query_args( array $filters ): array {
	$keys = happywood_get_account_wishlist_filter_keys();
	$args = array();

	foreach ( $keys as $field => $key ) {
		if ( '' !== $filters[ $field ] ) {
			$args[ $key ] = $filters[ $field ];
		}
	}

	return $args;
}

/**
 * Cards per wishlist page. Matches the shop grid density by default.
 */
function happywood_get_account_wishlist_per_page(): int {
	$per_page = 12;

	if ( function_exists( 'happywood_get_loop_shop_per_page' ) ) {
		$per_page = happywood_get_loop_shop_per_page();
	}

	$per_page = (int) apply_filters( 'happywood_account_wishlist_per_page', $per_page );

	return max( 1, $per_page );
}

/**
 * Current wishlist page from the WC endpoint value (`/wishlist/2/`).
 */
function happywood_get_account_wishlist_current_page(): int {
	$page = absint( get_query_var( 'wishlist' ) );

	return max( 1, $page );
}

/**
 * Published, visible rows on this customer's list (newest first).
 *
 * @return array<int, array{product_id: int, date_added: string}>
 */
function happywood_get_account_wishlist_saved_items(): array {
	if ( ! is_user_logged_in() || ! class_exists( Wishlist::class ) ) {
		return array();
	}

	$items = array();

	foreach ( Wishlist::get_items( get_current_user_id() ) as $item ) {
		if ( Wishlist::is_valid_product( $item['product_id'] ) ) {
			$items[] = $item;
		}
	}

	return $items;
}

/**
 * Apply GET filters to saved items. Sort default is newest first.
 *
 * @param array<int, array{product_id: int, date_added: string}>              $items   Saved items.
 * @param array{search: string, category: string, date: string, sort: string} $filters Filter values.
 * @return array<int, array{product_id: int, date_added: string}>
 */
function happywood_filter_account_wishlist_items( array $items, array $filters ): array {
	$after = '';

	if ( '' !== $filters['date'] && function_exists( 'happywood_get_account_orders_date_after' ) ) {
		$after = happywood_get_account_orders_date_after( $filters['date'] );
	}

	$matched = array();

	foreach ( $items as $item ) {
		if ( '' !== $after && $item['date_added'] <= $after ) {
			continue;
		}

		if ( '' !== $filters['category'] && ! happywood_account_wishlist_product_in_category( $item['product_id'], $filters['category'] ) ) {
			continue;
		}

		if ( '' !== $filters['search'] ) {
			$product = wc_get_product( $item['product_id'] );
			$name    = $product instanceof WC_Product ? $product->get_name() : get_the_title( $item['product_id'] );

			if ( false === stripos( (string) $name, $filters['search'] ) ) {
				continue;
			}
		}

		$matched[] = $item;
	}

	if ( 'oldest' === $filters['sort'] ) {
		$matched = array_reverse( $matched );
	}

	return $matched;
}

/**
 * Published, visible product IDs on this customer's list (newest first).
 *
 * @return int[]
 */
function happywood_get_account_wishlist_product_ids(): array {
	$ids = array();

	foreach ( happywood_get_account_wishlist_saved_items() as $item ) {
		$ids[] = $item['product_id'];
	}

	return $ids;
}

/**
 * Render one product through the product-card block (and its shared markup function).
 *
 * Injects collection-loop context so wishlist, quick view, and add-to-cart
 * hooks see the same postId they get inside a Product Collection.
 */
function happywood_get_account_wishlist_product_card_markup( int $product_id ): string {
	$post = get_post( $product_id );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$previous_post = isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof WP_Post
		? $GLOBALS['post']
		: null;

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['post'] = $post;
	setup_postdata( $post );

	$inject_context = static function ( array $context ) use ( $product_id ): array {
		$context['postId']                 = $product_id;
		$context['postType']               = 'product';
		$query                             = isset( $context['query'] ) && is_array( $context['query'] )
			? $context['query']
			: array();
		$query['isProductCollectionBlock'] = true;
		$context['query']                  = $query;

		return $context;
	};

	add_filter( 'render_block_context', $inject_context, 1 );

	$html = do_blocks( '<!-- wp:happywood/product-card {"titleTag":"h3"} /-->' );

	remove_filter( 'render_block_context', $inject_context, 1 );

	wp_reset_postdata();

	if ( $previous_post instanceof WP_Post ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	} else {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = null;
	}

	return $html;
}

/**
 * Filter bar — GET form so a later pagination pass can repeat the same args.
 *
 * @param array{search: string, category: string, date: string, sort: string} $filters Filter values.
 * @param array<int, array{product_id: int, date_added: string}>              $items   Saved items.
 */
function happywood_render_account_wishlist_filters( array $filters, array $items ): void {
	get_template_part(
		'partials/account-wishlist',
		'filters',
		array(
			'filters' => $filters,
			'items'   => $items,
		)
	);
}

/**
 * Wishlist endpoint: heading, count, filters, product-card grid or empty.
 */
function happywood_render_account_wishlist(): void {
	$saved     = happywood_get_account_wishlist_saved_items();
	$filters   = happywood_get_account_wishlist_filters( $saved );
	$active    = happywood_account_wishlist_filters_are_active( $filters );
	$items     = happywood_filter_account_wishlist_items( $saved, $filters );
	$total     = count( $items );
	$per_page  = happywood_get_account_wishlist_per_page();
	$max_pages = (int) ceil( $total / $per_page );
	$current   = happywood_get_account_wishlist_current_page();

	if ( $max_pages > 0 ) {
		$current = min( $current, $max_pages );
	}

	$offset     = ( $current - 1 ) * $per_page;
	$page_items = array_slice( $items, $offset, $per_page );

	echo '<div class="hw-account-wishlist" data-filters-active="' . ( $active ? '1' : '0' ) . '" data-item-total="' . esc_attr( (string) $total ) . '" data-item-singular="' . esc_attr__( '%s item', 'happywood' ) . '" data-item-plural="' . esc_attr__( '%s items', 'happywood' ) . '">';
	echo '<header class="hw-account-wishlist__head">';
	echo '<div class="hw-account-wishlist__head-intro">';
	echo '<h2 class="wp-block-heading">' . esc_html__( 'My Wishlist', 'happywood' ) . '</h2>';
	echo '<p class="is-style-text-lead has-small-font-size">' . esc_html__( 'Products you have saved for later', 'happywood' ) . '</p>';
	echo '</div>';
	echo '<div class="hw-account-wishlist__head-count">';
	printf(
		/* translators: %s: number of matching saved products */
		esc_html( _n( '%s item', '%s items', $total, 'happywood' ) ),
		esc_html( number_format_i18n( $total ) )
	);
	echo '</div>';
	echo '</header>';

	if ( array() !== $saved || $active ) {
		happywood_render_account_wishlist_filters( $filters, $saved );
	}

	echo happywood_get_account_wishlist_empty_markup( 0 === $total && ! $active ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
	echo happywood_get_account_wishlist_empty_filtered_markup( 0 === $total && $active ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.

	if ( array() !== $page_items ) {
		echo '<ul class="hw-account-wishlist__grid">';

		foreach ( $page_items as $item ) {
			$product_id = $item['product_id'];
			$product    = wc_get_product( $product_id );

			if ( ! $product instanceof WC_Product ) {
				continue;
			}

			$classes = function_exists( 'wc_get_product_class' )
				? wc_get_product_class( 'wc-block-product', $product )
				: array( 'wc-block-product' );

			echo '<li class="' . esc_attr( implode( ' ', $classes ) ) . '" data-product-id="' . esc_attr( (string) $product_id ) . '">';
			echo happywood_get_account_wishlist_product_card_markup( $product_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- do_blocks().
			echo '</li>';
		}

		echo '</ul>';
	}

	echo happywood_get_account_wishlist_pagination_markup( $current, $max_pages, $filters ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links().

	echo '</div>';
}

/**
 * Numbered pager — WP `paginate_links()`, WC endpoint URLs, filters preserved.
 *
 * @param int                                                                 $current_page Current page number.
 * @param int                                                                 $max_pages    Total page count.
 * @param array{search: string, category: string, date: string, sort: string} $filters      Filter values.
 */
function happywood_get_account_wishlist_pagination_markup( int $current_page, int $max_pages, array $filters = array() ): string {
	if ( $max_pages < 2 || ! function_exists( 'wc_get_account_endpoint_url' ) ) {
		return '';
	}

	$endpoint = wc_get_account_endpoint_url( 'wishlist' );
	$query    = happywood_get_account_wishlist_filter_query_args( $filters );
	$links    = paginate_links(
		array(
			'base'      => trailingslashit( $endpoint ) . '%_%',
			'format'    => user_trailingslashit( '%#%', 'single' ),
			'current'   => max( 1, $current_page ),
			'total'     => $max_pages,
			'add_args'  => $query,
			'type'      => 'list',
			'prev_text' => is_rtl() ? '&rarr;' : '&larr;',
			'next_text' => is_rtl() ? '&larr;' : '&rarr;',
		)
	);

	if ( ! is_string( $links ) || '' === $links ) {
		return '';
	}

	return '<nav class="woocommerce-pagination hw-account-wishlist__pagination" aria-label="' . esc_attr__( 'Wishlist pagination', 'happywood' ) . '">' . $links . '</nav>';
}

/**
 * Empty wishlist — never saved, or the last heart was removed.
 *
 * @param bool $visible Whether the empty state is shown on first paint.
 */
function happywood_get_account_wishlist_empty_markup( bool $visible ): string {
	$hidden = $visible ? '' : ' hidden';
	$html   = '<div class="hw-account-wishlist__empty hw-account-wishlist__empty--none is-style-bordered"' . $hidden . '>';
	$html  .= '<p>' . esc_html__( 'Your wishlist is empty.', 'happywood' ) . '</p>';

	if ( function_exists( 'wc_get_page_permalink' ) && function_exists( 'happywood_get_buttons_markup' ) ) {
		$html .= happywood_get_buttons_markup(
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
 * Empty wishlist — filters matched nothing.
 *
 * @param bool $visible Whether the empty state is shown on first paint.
 */
function happywood_get_account_wishlist_empty_filtered_markup( bool $visible ): string {
	$hidden = $visible ? '' : ' hidden';
	$html   = '<div class="hw-account-wishlist__empty hw-account-wishlist__empty--filtered is-style-bordered"' . $hidden . '>';
	$html  .= '<p>' . esc_html__( 'No products match these filters.', 'happywood' ) . '</p>';

	if ( function_exists( 'wc_get_account_endpoint_url' ) && function_exists( 'happywood_get_buttons_markup' ) ) {
		$html .= happywood_get_buttons_markup(
			array(
				array(
					'label' => __( 'Clear filters', 'happywood' ),
					'url'   => wc_get_account_endpoint_url( 'wishlist' ),
					'style' => 'is-style-link',
					'class' => 'has-small-font-size',
				),
			)
		);
	}

	$html .= '</div>';

	return $html;
}
