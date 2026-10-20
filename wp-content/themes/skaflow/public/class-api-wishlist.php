<?php
/**
 * REST: logged-in customer wishlist.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\Api_Wishlist' ) ) {
	return;
}

/**
 * REST: logged-in customer wishlist.
 *
 * Storage and validation live in `Wishlist`; this class is only the HTTP surface.
 *
 * The "is the wishlist switched on" test sits in the permission callback rather
 * than `is_available()` because it reads an ACF option — a runtime setting an
 * admin can flip, not a question about whether the code exists.
 *
 * @since      2.0.0
 * @package    Skaflow
 * @subpackage Skaflow/public
 */
class Api_Wishlist extends Api {

	/**
	 * Requires WooCommerce — `Wishlist` refuses to operate without it.
	 */
	protected function is_available(): bool {

		return class_exists( 'WooCommerce' );
	}

	/**
	 * Declare the wishlist routes.
	 */
	public function register_routes(): void {

		$this->route(
			'/wishlist/toggle',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'toggle' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'productId' => array(
						'required'          => true,
						'type'              => 'integer',
						'sanitize_callback' => 'absint',
					),
				),
			)
		);

		$this->route(
			'/wishlist',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_items' ),
				'permission_callback' => array( $this, 'check_permission' ),
			)
		);

		$this->route(
			'/wishlist/status',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'status' ),
				'permission_callback' => array( $this, 'check_permission' ),
				'args'                => array(
					'ids' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => array( $this, 'sanitize_ids_param' ),
						'validate_callback' => array( $this, 'validate_ids_param' ),
					),
				),
			)
		);
	}

	/**
	 * Wishlist enabled and a user is signed in.
	 *
	 * @return true|\WP_Error
	 */
	public function check_permission() {

		if ( ! skaflow_is_wishlist_enabled() ) {
			return $this->error(
				'wishlist_disabled',
				__( 'Wishlist is disabled.', 'skaflow' ),
				403
			);
		}

		if ( ! is_user_logged_in() ) {
			return $this->error(
				'wishlist_login_required',
				__( 'You must be logged in to use the wishlist.', 'skaflow' ),
				401
			);
		}

		return true;
	}

	/**
	 * @param mixed $value Raw ids param.
	 */
	public function sanitize_ids_param( $value ): string {

		if ( ! is_string( $value ) && ! is_numeric( $value ) ) {
			return '';
		}

		return sanitize_text_field( (string) $value );
	}

	/**
	 * @param mixed            $value   Param value.
	 * @param \WP_REST_Request $request Request.
	 * @param string           $param   Param name.
	 */
	public function validate_ids_param( $value, \WP_REST_Request $request, string $param ): bool {

		$ids = $this->parse_ids_param( (string) $value );

		return array() !== $ids && count( $ids ) <= Wishlist::STATUS_IDS_MAX;
	}

	/**
	 * Toggle a product in the current user's wishlist.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function toggle( \WP_REST_Request $request ) {

		$user_id    = get_current_user_id();
		$product_id = absint( $request->get_param( 'productId' ) );

		if ( $product_id <= 0 ) {
			return $this->invalid_product_error();
		}

		if ( Wishlist::is_in_wishlist( $user_id, $product_id ) ) {
			Wishlist::remove_item( $user_id, $product_id );

			return $this->respond(
				array(
					'productId'  => $product_id,
					'inWishlist' => false,
					'count'      => Wishlist::get_count( $user_id ),
				)
			);
		}

		if ( ! Wishlist::is_valid_product( $product_id ) ) {
			return $this->invalid_product_error();
		}

		$result = Wishlist::toggle_item( $user_id, $product_id );

		return $this->respond(
			array(
				'productId'  => $product_id,
				'inWishlist' => (bool) $result['in_wishlist'],
				'count'      => (int) $result['count'],
			)
		);
	}

	/**
	 * List product IDs in the current user's wishlist.
	 *
	 * @param \WP_REST_Request $request Request object.
	 */
	public function list_items( \WP_REST_Request $request ): \WP_REST_Response {

		$product_ids = Wishlist::get_product_ids( get_current_user_id() );

		return $this->respond(
			array(
				'productIds' => $product_ids,
				'count'      => count( $product_ids ),
			)
		);
	}

	/**
	 * Batch wishlist membership for the product IDs on the current page.
	 *
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function status( \WP_REST_Request $request ) {

		$ids = $this->parse_ids_param( (string) $request->get_param( 'ids' ) );

		if ( array() === $ids ) {
			return $this->error(
				'wishlist_invalid_ids',
				__( 'No valid product IDs were provided.', 'skaflow' )
			);
		}

		if ( count( $ids ) > Wishlist::STATUS_IDS_MAX ) {
			return $this->error(
				'wishlist_too_many_ids',
				__( 'Too many product IDs in one request.', 'skaflow' )
			);
		}

		$status_map = Wishlist::get_status_map( get_current_user_id(), $ids );
		$response   = array();

		foreach ( $status_map as $product_id => $in_wishlist ) {
			$response[ (string) $product_id ] = (bool) $in_wishlist;
		}

		return $this->respond( $response );
	}

	/**
	 * @param string $raw Comma-separated product IDs.
	 *
	 * @return int[]
	 */
	private function parse_ids_param( string $raw ): array {

		if ( '' === trim( $raw ) ) {
			return array();
		}

		$ids = array();

		foreach ( array_map( 'trim', explode( ',', $raw ) ) as $part ) {
			if ( '' === $part || ! ctype_digit( $part ) ) {
				continue;
			}

			$id = absint( $part );

			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}

		return array_values( array_unique( $ids ) );
	}

	/**
	 * @return \WP_Error
	 */
	private function invalid_product_error(): \WP_Error {

		return $this->error(
			'wishlist_invalid_product',
			__( 'Invalid product.', 'skaflow' )
		);
	}
}
