<?php
/**
 * REST: product search for admin/link fields.
 *
 * @package Happywood
 */

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\Api_Product_Search' ) ) {
	return;
}

/**
 * REST: product search for the header search field.
 *
 * Read-only and public: the dropdown has to work for logged-out visitors, and it
 * returns nothing that is not already on the shop archive.
 *
 * @since      2.0.0
 * @package    Happywood
 * @subpackage Happywood/public
 */
class Api_Product_Search extends Api {

	/**
	 * Maximum products returned per query.
	 */
	const RESULT_LIMIT = 8;

	/**
	 * Shortest search term worth querying for.
	 */
	const MIN_TERM_LENGTH = 2;

	/**
	 * Requires WooCommerce for price markup and product hydration.
	 */
	protected function is_available(): bool {

		return class_exists( 'WooCommerce' );
	}

	/**
	 * Declare the search route.
	 */
	public function register_routes(): void {

		$this->route(
			'/product-search',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'search' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					's' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	/**
	 * Search products for the header dropdown.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function search( \WP_REST_Request $request ): \WP_REST_Response {

		$term = trim( (string) $request->get_param( 's' ) );

		if ( strlen( $term ) < self::MIN_TERM_LENGTH || ! post_type_exists( 'product' ) ) {
			return $this->respond( array( 'items' => array() ) );
		}

		$query = new \WP_Query(
			array(
				'post_type'      => 'product',
				'post_status'    => 'publish',
				's'              => $term,
				'posts_per_page' => self::RESULT_LIMIT,
				'no_found_rows'  => true,
			)
		);

		$items = array();

		foreach ( $query->posts as $post ) {
			$product = wc_get_product( $post->ID );

			if ( ! $product ) {
				continue;
			}

			$thumbnail = get_the_post_thumbnail_url( $post->ID, 'woocommerce_thumbnail' );

			$items[] = array(
				'id'        => $post->ID,
				'title'     => get_the_title( $post ),
				'url'       => get_permalink( $post ),
				'thumbnail' => $thumbnail ? $thumbnail : '',
				'price'     => $product->get_price_html(),
			);
		}

		return $this->respond( array( 'items' => $items ) );
	}
}
