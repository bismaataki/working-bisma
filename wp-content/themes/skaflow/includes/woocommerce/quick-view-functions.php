<?php
/**
 * Quick View summary rendering.
 *
 * The quick-view popup reuses WooCommerce's single-product summary hook sequence,
 * so these are plain functions rather than methods: they are attached to and
 * detached from `woocommerce_single_product_summary` by name, and core's own
 * callbacks are removed the same way.
 *
 * The endpoint that assembles them into a response is
 * `public/class-api-quick-view.php`.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Quick view summary content mode from theme options.
 *
 * @return 'dimensions'|'short_description'
 */
function skaflow_get_quick_view_content_mode(): string {

	$mode = 'dimensions';

	if ( function_exists( 'get_field' ) ) {
		$mode = get_field( 'quick_view_content', 'option' ) ?: 'dimensions';
	}

	return in_array( $mode, array( 'dimensions', 'short_description' ), true )
		? $mode
		: 'dimensions';
}

/**
 * Output Dimensions block in quick-view summary (Figma "Product Details").
 */
function skaflow_quick_view_render_product_dimensions(): void {

	$html = skaflow_get_product_dimensions_html( get_the_ID() );

	if ( '' === $html ) {
		return;
	}
	?>
	<div class="sk-quick-view-details sk-quick-view-details--dimensions">
		<p class="sk-quick-view-details__label"><?php esc_html_e( 'Product Details', 'skaflow' ); ?></p>
		<div class="sk-quick-view-details__content"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in skaflow_get_product_dimensions_html(). ?></div>
	</div>
	<?php
}

/**
 * Output WooCommerce short description in quick-view summary.
 */
function skaflow_quick_view_render_product_short_description(): void {

	$product = wc_get_product( get_the_ID() );

	if ( ! $product ) {
		return;
	}

	$short_description = $product->get_short_description();

	if ( '' === trim( $short_description ) ) {
		return;
	}

	$html = wp_kses_post( wpautop( $short_description ) );
	?>
	<div class="sk-quick-view-details sk-quick-view-details--description">
		<div class="sk-quick-view-details__content"><?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?></div>
	</div>
	<?php
}

/**
 * Output the configured quick-view summary text block.
 */
function skaflow_quick_view_render_product_details(): void {

	if ( 'short_description' === skaflow_get_quick_view_content_mode() ) {
		skaflow_quick_view_render_product_short_description();
		return;
	}

	skaflow_quick_view_render_product_dimensions();
}

/**
 * Product-price block — same comment as the card and `woocommerce/loop/price.php`.
 */
function skaflow_quick_view_render_product_price(): void {

	if ( ! function_exists( 'do_blocks' ) ) {
		return;
	}

	$product_id = get_the_ID();

	if ( ! $product_id ) {
		return;
	}

	$with_product_context = static function ( array $context ) use ( $product_id ): array {
		$context['postId']   = $product_id;
		$context['postType'] = 'product';

		return $context;
	};

	add_filter( 'render_block_context', $with_product_context, 1 );

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted block render.
	echo do_blocks(
		'<!-- wp:woocommerce/product-price {"textAlign":"left","isDescendentOfQueryLoop":true,"fontSize":"small","className":"is-style-text-price"} /-->'
	);

	remove_filter( 'render_block_context', $with_product_context, 1 );
}

/**
 * Use loop add-to-cart markup in quick view for simple products.
 *
 * WC core ajax add-to-cart handles `.add_to_cart_button.ajax_add_to_cart` links
 * globally via `wc-add-to-cart.js`. Single-product cart forms POST to the product
 * URL instead, which is a poor fit inside the drawer.
 */
function skaflow_quick_view_prepare_simple_add_to_cart(): void {

	global $product;

	if ( ! $product instanceof \WC_Product || ! $product->is_type( 'simple' ) ) {
		return;
	}

	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary', 'skaflow_quick_view_render_simple_add_to_cart', 30 );
}

/**
 * Loop-style add to cart for simple products in quick view.
 */
function skaflow_quick_view_render_simple_add_to_cart(): void {

	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	echo skaflow_get_product_card_add_to_cart_html( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted WC loop markup from helper.
}

/**
 * Output YITH Add to Quote below the add-to-cart form in quick view.
 */
function skaflow_quick_view_render_add_to_quote(): void {

	if ( ! skaflow_is_quick_view_request_quote_enabled() ) {
		return;
	}

	if ( ! skaflow_is_ywraq_available() ) {
		return;
	}

	if ( ! skaflow_is_ywraq_quote_button_enabled( 'single_product' ) ) {
		return;
	}

	if ( function_exists( 'yith_ywraq_show_button_in_single_page' ) && ! yith_ywraq_show_button_in_single_page() ) {
		return;
	}

	$product_id = get_the_ID();

	if ( ! $product_id ) {
		return;
	}

	$markup = skaflow_render_ywraq_button_quote_markup( $product_id );

	if ( '' === trim( $markup ) ) {
		return;
	}
	?>
	<div class="sk-quick-view-quote">
		<?php echo $markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- YITH shortcode output. ?>
	</div>
	<?php
}
