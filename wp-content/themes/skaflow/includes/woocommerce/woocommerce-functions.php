<?php
/**
 * WooCommerce and YITH capability helpers.
 *
 * Moved out of `helper-functions.php`, which is always loaded and can never be
 * deleted. Keeping commerce here means a project without WooCommerce removes
 * this file with `includes/woocommerce/` and that directory's bootstrap.
 *
 * Every WooCommerce and YITH call in this file is behind a `function_exists()`
 * guard, so the file is inert rather than fatal when the plugins are absent.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether the wishlist feature is enabled (master theme option).
 *
 * Gated on an ACF option, not on a wishlist plugin — the wishlist is
 * theme-native.
 */
function skaflow_is_wishlist_enabled(): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	$value = get_field( 'wishlist_enabled', 'option' );

	if ( null === $value || '' === $value ) {
		return true;
	}

	return (bool) $value;
}

/**
 * Whether the wishlist heart should render on product loop cards.
 */
function skaflow_is_wishlist_loop_enabled(): bool {
	if ( ! skaflow_is_wishlist_enabled() ) {
		return false;
	}

	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	$value = get_field( 'wishlist_loop_enabled', 'option' );

	if ( null === $value || '' === $value ) {
		return true;
	}

	return (bool) $value;
}

/**
 * Whether YITH Request a Quote is active and its button API is available.
 */
function skaflow_is_ywraq_available(): bool {
	return function_exists( 'yith_ywraq_render_button' )
		|| shortcode_exists( 'yith_ywraq_button_quote' );
}

/**
 * Whether a YITH "Add to quote" visibility option is enabled.
 *
 * @param string $context `woocommerce_blocks` (product cards / WC blocks),
 *                        `single_product` (single product + quick view),
 *                        `other_pages` (classic shop loop / taxonomy archives).
 */
function skaflow_is_ywraq_quote_button_enabled( string $context = 'woocommerce_blocks' ): bool {
	if ( ! skaflow_is_ywraq_available() ) {
		return false;
	}

	$option_map = array(
		'woocommerce_blocks' => 'ywraq_show_btn_woocommerce_blocks',
		'single_product'     => 'ywraq_show_btn_single_page',
		'other_pages'        => 'ywraq_show_btn_other_pages',
	);

	$option_key = $option_map[ $context ] ?? $option_map['woocommerce_blocks'];

	return 'yes' === get_option( $option_key, 'no' );
}

/**
 * Whether Request a Quote should render in the quick view drawer.
 */
function skaflow_is_quick_view_request_quote_enabled(): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	$value = get_field( 'quick_view_request_quote_enabled', 'option' );

	if ( null === $value || '' === $value ) {
		return true;
	}

	return (bool) $value;
}

/**
 * Render YITH Add to Quote button markup for a product.
 *
 * Mirrors the `yith/yith-ywraq-button-quote` block output.
 *
 * @param int $product_id Product post ID; defaults to the current post in loop.
 * @return string Button HTML or empty string when unavailable.
 */
function skaflow_render_ywraq_button_quote_markup( int $product_id = 0 ): string {
	if ( ! skaflow_is_ywraq_available() ) {
		return '';
	}

	if ( ! $product_id ) {
		$product_id = get_the_ID();
	}

	$product_id = absint( $product_id );

	if ( ! $product_id ) {
		return '';
	}

	if ( is_callable( 'apply_shortcodes' ) ) {
		return (string) apply_shortcodes(
			'[yith_ywraq_button_quote product="' . $product_id . '"]'
		);
	}

	return (string) do_shortcode(
		'[yith_ywraq_button_quote product="' . $product_id . '"]'
	);
}

/**
 * Whether a block is rendering inside a WooCommerce product collection loop card.
 *
 * @param array<string, mixed> $block    Parsed block.
 * @param \WP_Block|null       $instance Block instance.
 */
function skaflow_is_product_collection_loop_block( array $block, ?\WP_Block $instance ): bool {

	if ( ! empty( $block['attrs']['isDescendentOfQueryLoop'] ) ) {
		return true;
	}

	if ( $instance instanceof \WP_Block && ! empty( $instance->context['query']['isProductCollectionBlock'] ) ) {
		return true;
	}

	return false;
}

/**
 * Product ID from a product collection block render context.
 *
 * @param \WP_Block|null $instance Block instance.
 */
function skaflow_get_product_collection_block_post_id( ?\WP_Block $instance ): int {

	if ( $instance instanceof \WP_Block && ! empty( $instance->context['postId'] ) ) {
		return absint( $instance->context['postId'] );
	}

	return 0;
}

/**
 * Products per page for Product Collection block queries (Customizer columns × rows).
 *
 * @return int Positive integer.
 */
function skaflow_get_shop_products_per_page(): int {

	if ( function_exists( 'skaflow_get_loop_shop_per_page' ) ) {
		return max( 1, skaflow_get_loop_shop_per_page() );
	}

	if ( function_exists( 'wc_get_default_products_per_row' ) && function_exists( 'wc_get_default_product_rows_per_page' ) ) {
		return max(
			1,
			(int) apply_filters(
				'loop_shop_per_page',
				wc_get_default_products_per_row() * wc_get_default_product_rows_per_page()
			)
		);
	}

	$columns = max( 1, (int) get_option( 'woocommerce_catalog_columns', 4 ) );
	$rows    = max( 1, (int) get_option( 'woocommerce_catalog_rows', 4 ) );

	return max( 1, (int) apply_filters( 'loop_shop_per_page', $columns * $rows ) );
}

/**
 * Serialized hand-picked Product Collection block markup for do_blocks().
 *
 * Uses the shop "Products per page" setting for query pagination. When hand-picked
 * IDs exceed that count, the collection block renders query-pagination automatically.
 *
 * @param int[]               $product_ids Hand-picked product post IDs.
 * @param int                 $columns     Product Collection display columns.
 * @param array<string,mixed> $args {
 *     Optional overrides.
 *
 *     @type string   $class_name         Block `className` + wrapper class. Default empty.
 *     @type string   $align              Block align. Default `wide`.
 *     @type int|null $query_id           Block `queryId`; omitted from JSON when null. Default null.
 *     @type int|null $per_page           Query `perPage`; shop setting when null. Default null.
 *     @type bool     $include_pagination Include `query-pagination` inner block. Default true.
 * }
 * @return string Block comments + inner HTML, or empty string when no product IDs.
 */
function skaflow_get_hand_picked_product_collection_blocks_markup( array $product_ids, int $columns = 5, array $args = array() ): string {

	$product_ids = array_values(
		array_filter(
			array_map( 'absint', $product_ids )
		)
	);

	if ( empty( $product_ids ) ) {
		return '';
	}

	$class_name         = isset( $args['class_name'] ) ? sanitize_html_class( (string) $args['class_name'] ) : '';
	$align              = isset( $args['align'] ) ? sanitize_key( (string) $args['align'] ) : 'wide';
	$query_id           = array_key_exists( 'query_id', $args ) ? $args['query_id'] : null;
	$per_page           = isset( $args['per_page'] ) ? max( 1, (int) $args['per_page'] ) : skaflow_get_shop_products_per_page();
	$include_pagination = ! array_key_exists( 'include_pagination', $args ) || (bool) $args['include_pagination'];
	$columns            = max( 1, $columns );
	$hand_picked        = array_map( 'strval', $product_ids );

	$block_attrs = array(
		'align'                => $align,
		'query'                => array(
			'perPage'                       => $per_page,
			'pages'                         => 0,
			'offset'                        => 0,
			'postType'                      => 'product',
			'order'                         => 'asc',
			'orderBy'                       => 'post__in',
			'search'                        => '',
			'exclude'                       => array(),
			'inherit'                       => false,
			'taxQuery'                      => (object) array(),
			'isProductCollectionBlock'      => true,
			'featured'                      => false,
			'woocommerceOnSale'             => false,
			'woocommerceStockStatus'        => array( 'instock', 'outofstock', 'onbackorder' ),
			'woocommerceAttributes'         => array(),
			'woocommerceHandPickedProducts' => $hand_picked,
			'filterable'                    => false,
			'relatedBy'                     => array(
				'categories' => true,
				'tags'       => true,
			),
		),
		'tagName'              => 'div',
		'displayLayout'        => array(
			'type'          => 'flex',
			'columns'       => $columns,
			'shrinkColumns' => true,
		),
		'dimensions'           => array(
			'widthType' => 'fill',
		),
		'collection'           => 'woocommerce/product-collection/hand-picked',
		'hideControls'         => array( 'inherit', 'hand-picked', 'filterable', 'order' ),
		'queryContextIncludes' => array( 'collection' ),
	);

	// This is required attribute of block
	$block_attrs['queryId'] = max( 0, (int) $query_id );

	if ( '' !== $class_name ) {
		$block_attrs['className'] = $class_name;
	}

	$json = wp_json_encode( $block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	if ( ! is_string( $json ) || '' === $json ) {
		return '';
	}

	$wrapper_classes = trim( 'wp-block-woocommerce-product-collection alignwide ' . $class_name );

	$inner_blocks = '<!-- wp:woocommerce/product-template -->
<!-- wp:skaflow/product-card /-->
<!-- /wp:woocommerce/product-template -->';

	if ( $include_pagination ) {
		$inner_blocks .= '

<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->

<!-- wp:query-pagination-numbers /-->

<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->';
	}

	return sprintf(
		'<!-- wp:woocommerce/product-collection %1$s -->
<div class="%2$s">%3$s</div>
<!-- /wp:woocommerce/product-collection -->',
		$json,
		esc_attr( $wrapper_classes ),
		$inner_blocks
	);
}

/**
 * SAVE label markup for on-sale products in collection cards (matches Figma price row).
 *
 * @param int $product_id Product post ID.
 * @return string Empty when not on sale or percentage cannot be calculated.
 */
function skaflow_get_product_card_save_label_markup( int $product_id ): string {

	if ( ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof \WC_Product || ! $product->is_on_sale() ) {
		return '';
	}

	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();

	if ( $product->is_type( 'variable' ) ) {
		$regular = (float) $product->get_variation_regular_price( 'min', true );
		$sale    = (float) $product->get_variation_sale_price( 'min', true );
	}

	if ( $regular <= 0 || $sale <= 0 || $sale >= $regular ) {
		return '';
	}

	$percent = (int) round( ( ( $regular - $sale ) / $regular ) * 100 );

	if ( $percent <= 0 ) {
		return '';
	}

	return sprintf(
		'<span class="sk-product-card__save">%s</span>',
		esc_html(
			sprintf(
				/* translators: %d: whole-number discount percentage */
				__( 'SAVE %d%%', 'skaflow' ),
				$percent
			)
		)
	);
}

/**
 * URL for guest wishlist clicks (WooCommerce My Account login).
 *
 * wc_get_page_permalink( 'myaccount' ) can resolve to the site home when the
 * WC page option is missing or misconfigured; /my-account/ is used as fallback.
 */
function skaflow_get_wishlist_login_url(): string {
	$home_url = trailingslashit( home_url( '/' ) );

	if ( function_exists( 'WC' ) ) {
		$page_id = absint( get_option( 'woocommerce_myaccount_page_id' ) );

		if ( $page_id > 0 ) {
			$permalink = get_permalink( $page_id );

			if ( is_string( $permalink ) && $permalink !== '' && trailingslashit( $permalink ) !== $home_url ) {
				return $permalink;
			}
		}
	}

	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$permalink = wc_get_page_permalink( 'myaccount' );

		if ( is_string( $permalink ) && $permalink !== '' && trailingslashit( $permalink ) !== $home_url ) {
			return $permalink;
		}
	}

	$fallback = home_url( '/my-account/' );

	if ( trailingslashit( $fallback ) !== $home_url ) {
		return $fallback;
	}

	return wp_login_url();
}
