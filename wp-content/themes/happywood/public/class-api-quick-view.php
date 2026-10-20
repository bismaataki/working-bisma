<?php
/**
 * REST: Quick View modal payload.
 *
 * @package Happywood
 */

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\Api_Quick_View' ) ) {
	return;
}

/**
 * REST: single-product summary markup for the quick-view popup.
 *
 * The response is WooCommerce's own single-product summary, rendered against a
 * swapped hook set so the drawer shows the dimensions/short-description block and
 * loop-style add to cart instead of the full product page furniture. Those
 * callbacks live in `includes/woocommerce/quick-view-functions.php` because they
 * are attached by name.
 *
 * @since      2.0.0
 * @package    Happywood
 * @subpackage Happywood/public
 */
class Api_Quick_View extends Api {

	/**
	 * Requires WooCommerce templates and product hydration.
	 */
	protected function is_available(): bool {

		return class_exists( 'WooCommerce' );
	}

	/**
	 * Declare the quick-view route.
	 */
	public function register_routes(): void {

		$this->route(
			'/quick-view/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'render' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'id' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);
	}

	/**
	 * Render the summary markup for one product.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function render( \WP_REST_Request $request ): \WP_REST_Response {

		$product_id = absint( $request->get_param( 'id' ) );

		if (
			! $product_id
			|| 'product' !== get_post_type( $product_id )
			|| 'publish' !== get_post_status( $product_id )
		) {
			return $this->respond( array( 'html' => '' ), 404 );
		}

		// WooCommerce's summary templates read the globals, so populate both.
		global $post, $product;

		$post    = get_post( $product_id );
		$product = wc_get_product( $product_id );

		if ( ! $post || ! $product ) {
			return $this->respond( array( 'html' => '' ), 404 );
		}

		setup_postdata( $post );

		$this->swap_summary_hooks();

		return $this->respond( array( 'html' => $this->capture_summary( $product_id, $product ) ) );
	}

	/**
	 * Replace the single-product summary hooks with the quick-view set.
	 *
	 * Quick view shows the admin-selected dimensions or short description rather
	 * than the excerpt and SKU/category meta.
	 */
	private function swap_summary_hooks(): void {

		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
		remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
		add_action( 'woocommerce_single_product_summary', 'happywood_quick_view_render_product_price', 10 );
		add_action( 'woocommerce_single_product_summary', 'happywood_quick_view_render_product_details', 15 );

		if ( happywood_is_quick_view_request_quote_enabled() ) {
			add_action( 'woocommerce_single_product_summary', 'happywood_quick_view_render_add_to_quote', 35 );
		}

		happywood_quick_view_prepare_simple_add_to_cart();
	}

	/**
	 * Buffer the summary markup, mirroring content-single-product.php.
	 *
	 * @see woocommerce/templates/content-single-product.php
	 *
	 * @param int         $product_id Product post ID.
	 * @param \WC_Product $product    Hydrated product.
	 */
	private function capture_summary( int $product_id, \WC_Product $product ): string {

		$align_sale_flash = static function ( string $html ): string {
			return str_replace(
				esc_html__( 'Sale!', 'woocommerce' ),
				esc_html__( 'Sale', 'woocommerce' ),
				$html
			);
		};

		add_filter( 'woocommerce_sale_flash', $align_sale_flash );

		ob_start();
		?>
		<div id="product-<?php echo esc_attr( (string) $product_id ); ?>" <?php wc_product_class( '', $product ); ?>>
			<?php
			// Gallery + sale flash — same hook sequence as content-single-product.php.
			do_action( 'woocommerce_before_single_product_summary' );
			?>
			<div class="summary entry-summary">
				<?php do_action( 'woocommerce_single_product_summary' ); ?>
			</div>
		</div>
		<?php
		$html = ob_get_clean();

		remove_filter( 'woocommerce_sale_flash', $align_sale_flash );

		wp_reset_postdata();

		return $html;
	}
}
