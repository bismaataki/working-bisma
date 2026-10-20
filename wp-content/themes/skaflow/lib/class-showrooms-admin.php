<?php
/**
 * Showroom CPT wp-admin columns and fields.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\Showrooms_Admin' ) ) {
	return;
}

/**
 * Showrooms list-table UX: menu_order column and default sort.
 *
 * Quick Edit order is provided by core (page-attributes support on the CPT).
 */
class Showrooms_Admin {

	private const POST_TYPE = 'showrooms';

	private const COLUMN_KEY = 'menu_order';

	public function __construct() {
		$this->register_hooks();
	}

	private function register_hooks(): void {
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( $this, 'add_menu_order_column' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( $this, 'render_menu_order_column' ), 10, 2 );
		add_filter( 'manage_edit-' . self::POST_TYPE . '_sortable_columns', array( $this, 'make_menu_order_sortable' ) );
		add_action( 'pre_get_posts', array( $this, 'default_admin_order_by_menu_order' ) );
	}

	/**
	 * @param array<string, string> $columns Post list columns.
	 * @return array<string, string>
	 */
	public function add_menu_order_column( array $columns ): array {
		$reordered = array();

		foreach ( $columns as $key => $label ) {
			$reordered[ $key ] = $label;

			if ( 'title' === $key ) {
				$reordered[ self::COLUMN_KEY ] = __( 'Order', 'skaflow' );
			}
		}

		return $reordered;
	}

	/**
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_menu_order_column( string $column, int $post_id ): void {
		if ( self::COLUMN_KEY !== $column ) {
			return;
		}

		$post = get_post( $post_id );

		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		echo (int) $post->menu_order;
	}

	/**
	 * @param array<string, string> $columns Sortable columns.
	 * @return array<string, string>
	 */
	public function make_menu_order_sortable( array $columns ): array {
		$columns[ self::COLUMN_KEY ] = 'menu_order';

		return $columns;
	}

	/**
	 * Default the showrooms admin list to locator tab order.
	 *
	 * @param \WP_Query $query Main query.
	 */
	public function default_admin_order_by_menu_order( \WP_Query $query ): void {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( self::POST_TYPE !== $query->get( 'post_type' ) ) {
			return;
		}

		if ( $query->get( 'orderby' ) ) {
			return;
		}

		$query->set(
			'orderby',
			array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			)
		);
		$query->set( 'order', 'ASC' );
	}
}
