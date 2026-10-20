<?php
/**
 * Abstract REST base for the happywood/v1 namespace.
 *
 * @package Happywood
 */

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\Api' ) ) {
	return;
}

/**
 * Base class for the theme's REST endpoints.
 *
 * Endpoints live in `public/class-api-{concern}.php`, extend this class, and
 * declare their routes in `register_routes()`. Anything shared across endpoints —
 * the namespace, response shaping, the `rest_api_init` wiring — belongs here so
 * it exists once.
 *
 * Override `is_available()` when an endpoint depends on a plugin that may not be
 * active. A gated endpoint still constructs; it simply never reaches
 * `rest_api_init`, so no route is advertised and no callback can be hit.
 *
 * @since      2.0.0
 * @package    Happywood
 * @subpackage Happywood/public
 */
abstract class Api {

	/**
	 * REST namespace shared by every theme endpoint.
	 */
	const REST_NAMESPACE = 'happywood/v1';

	/**
	 * Initialize the endpoint and register its hooks.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$this->register_hooks();
	}

	/**
	 * Register required hooks.
	 *
	 * The availability gate sits here rather than in `Init` so each endpoint owns
	 * the conditions it needs to run.
	 */
	public function register_hooks(): void {

		if ( ! $this->is_available() ) {
			return;
		}

		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Declare this endpoint's routes. Runs on `rest_api_init`.
	 */
	abstract public function register_routes(): void;

	/**
	 * Whether the dependencies this endpoint needs are present.
	 *
	 * Subclasses override this to gate on a plugin, a post type, or a theme option.
	 */
	protected function is_available(): bool {

		return true;
	}

	/**
	 * Register one route inside the theme's REST namespace.
	 *
	 * @param string $route Route pattern, leading slash included.
	 * @param array  $args  Route arguments as accepted by register_rest_route().
	 */
	protected function route( string $route, array $args ): void {

		register_rest_route( static::REST_NAMESPACE, $route, $args );
	}

	/**
	 * Success response.
	 *
	 * @param mixed $data   Response payload.
	 * @param int   $status HTTP status.
	 */
	protected function respond( $data, int $status = 200 ): \WP_REST_Response {

		return new \WP_REST_Response( $data, $status );
	}

	/**
	 * Error response.
	 *
	 * Codes are prefixed with `happywood_` so callers see a consistent namespace.
	 *
	 * @param string $code    Error code, unprefixed.
	 * @param string $message Human-readable message.
	 * @param int    $status  HTTP status.
	 */
	protected function error( string $code, string $message, int $status = 400 ): \WP_Error {

		return new \WP_Error( 'happywood_' . $code, $message, array( 'status' => $status ) );
	}
}
