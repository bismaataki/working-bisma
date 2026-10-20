<?php
/**
 * Register all theme CPTs and taxonomies.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\Content_Types' ) ) {
	return;
}

/**
 * Registers every theme custom post type and taxonomy.
 *
 * ACF field groups are registered separately, and auto-load from `acf-json/`.
 *
 * Register a project's own types here. A type registered without a matching
 * field group, template, and Sass partial is a half-feature; a field group in
 * `acf-json/` whose location targets a type this class does not register is dead
 * weight that still loads on every admin request.
 */
class Content_Types {

	/**
	 * Content_Types constructor.
	 */
	public function __construct() {
		$this->register_hooks();
	}

	/**
	 * Register required hooks.
	 */
	private function register_hooks(): void {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ), 11 );
	}

	/**
	 * Register custom post types.
	 *
	 * @hooked init
	 */
	public function register_post_types(): void {
		$this->register_faq_post_type();
		$this->register_banner_post_type();
		$this->register_showrooms_post_type();
	}

	/**
	 * Register custom taxonomies.
	 *
	 * @hooked init, priority 11 — after post types.
	 */
	public function register_taxonomies(): void {
		$this->register_faq_category_taxonomy();
		$this->register_showroom_locations_taxonomy();
	}

	/**
	 * Register the FAQ post type. Archive at /faq/; singles 404 via FAQ archive helpers.
	 */
	private function register_faq_post_type(): void {
		$labels = array(
			'name'                     => __( 'FAQs', 'skaflow' ),
			'singular_name'            => __( 'FAQ', 'skaflow' ),
			'menu_name'                => __( 'FAQs', 'skaflow' ),
			'all_items'                => __( 'All FAQs', 'skaflow' ),
			'add_new'                  => __( 'Add new', 'skaflow' ),
			'add_new_item'             => __( 'Add new FAQ', 'skaflow' ),
			'edit_item'                => __( 'Edit FAQ', 'skaflow' ),
			'new_item'                 => __( 'New FAQ', 'skaflow' ),
			'view_item'                => __( 'View FAQ', 'skaflow' ),
			'view_items'               => __( 'View FAQs', 'skaflow' ),
			'search_items'             => __( 'Search FAQs', 'skaflow' ),
			'not_found'                => __( 'No FAQs found', 'skaflow' ),
			'not_found_in_trash'       => __( 'No FAQs found in trash', 'skaflow' ),
			'parent'                   => __( 'Parent FAQ:', 'skaflow' ),
			'featured_image'           => __( 'Featured image for this FAQ', 'skaflow' ),
			'set_featured_image'       => __( 'Set featured image for this FAQ', 'skaflow' ),
			'remove_featured_image'    => __( 'Remove featured image for this FAQ', 'skaflow' ),
			'use_featured_image'       => __( 'Use as featured image for this FAQ', 'skaflow' ),
			'archives'                 => __( 'FAQ', 'skaflow' ),
			'insert_into_item'         => __( /** @lang text */ 'Insert into FAQ', 'skaflow' ),
			'uploaded_to_this_item'    => __( 'Upload to this FAQ', 'skaflow' ),
			'filter_items_list'        => __( 'Filter FAQs list', 'skaflow' ),
			'items_list_navigation'    => __( 'FAQs list navigation', 'skaflow' ),
			'items_list'               => __( 'FAQs list', 'skaflow' ),
			'attributes'               => __( 'FAQs attributes', 'skaflow' ),
			'name_admin_bar'           => __( 'FAQ', 'skaflow' ),
			'item_published'           => __( 'FAQ published', 'skaflow' ),
			'item_published_privately' => __( 'FAQ published privately.', 'skaflow' ),
			'item_reverted_to_draft'   => __( 'FAQ reverted to draft.', 'skaflow' ),
			'item_scheduled'           => __( 'FAQ scheduled', 'skaflow' ),
			'item_updated'             => __( 'FAQ updated.', 'skaflow' ),
			'parent_item_colon'        => __( 'Parent FAQ:', 'skaflow' ),
		);

		register_post_type(
			'skaflow_faq',
			array(
				'label'                 => __( 'FAQs', 'skaflow' ),
				'labels'                => $labels,
				'description'           => '',
				'public'                => true,
				'publicly_queryable'    => true,
				'show_ui'               => true,
				'show_in_rest'          => true,
				'rest_base'             => 'faqs',
				'rest_controller_class' => 'WP_REST_Posts_Controller',
				'has_archive'           => true,
				'show_in_menu'          => true,
				'show_in_nav_menus'     => true,
				'delete_with_user'      => false,
				'exclude_from_search'   => true,
				'capability_type'       => 'post',
				'map_meta_cap'          => true,
				'hierarchical'          => false,
				'rewrite'               => array(
					'slug'       => 'faq',
					'with_front' => false,
				),
				'query_var'             => true,
				'menu_icon'             => 'dashicons-info-outline',
				'menu_position'         => 21,
				'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author', 'revisions' ),
			)
		);
	}

	/**
	 * Register the Banner post type. Admin-only — no public archive or singles.
	 */
	private function register_banner_post_type(): void {
		$labels = array(
			'name'                     => __( 'Banners', 'skaflow' ),
			'singular_name'            => __( 'Banner', 'skaflow' ),
			'menu_name'                => __( 'Banners', 'skaflow' ),
			'all_items'                => __( 'All Banners', 'skaflow' ),
			'add_new'                  => __( 'Add new', 'skaflow' ),
			'add_new_item'             => __( 'Add new Banner', 'skaflow' ),
			'edit_item'                => __( 'Edit Banner', 'skaflow' ),
			'new_item'                 => __( 'New Banner', 'skaflow' ),
			'view_item'                => __( 'View Banner', 'skaflow' ),
			'view_items'               => __( 'View Banners', 'skaflow' ),
			'search_items'             => __( 'Search Banners', 'skaflow' ),
			'not_found'                => __( 'No Banners found', 'skaflow' ),
			'not_found_in_trash'       => __( 'No Banners found in trash', 'skaflow' ),
			'item_published'           => __( 'Banner published', 'skaflow' ),
			'item_published_privately' => __( 'Banner published privately.', 'skaflow' ),
			'item_reverted_to_draft'   => __( 'Banner reverted to draft.', 'skaflow' ),
			'item_scheduled'           => __( 'Banner scheduled', 'skaflow' ),
			'item_updated'             => __( 'Banner updated.', 'skaflow' ),
		);

		register_post_type(
			'skaflow_banner',
			array(
				'label'               => __( 'Banners', 'skaflow' ),
				'labels'              => $labels,
				'description'         => '',
				'public'              => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'rest_base'           => 'banners',
				'has_archive'         => false,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => false,
				'delete_with_user'    => false,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_icon'           => 'dashicons-cover-image',
				'menu_position'       => 22,
				'supports'            => array( 'title', 'editor', 'revisions' ),
			)
		);
	}

	/**
	 * Register the showrooms post type.
	 */
	private function register_showrooms_post_type(): void {
		$labels = array(
			'name'          => __( 'Showrooms', 'skaflow' ),
			'singular_name' => __( 'Showroom', 'skaflow' ),
			'menu_name'     => __( 'Showrooms', 'skaflow' ),
			'all_items'     => __( 'All Showrooms', 'skaflow' ),
			'add_new_item'  => __( 'Add New Showroom', 'skaflow' ),
			'edit_item'     => __( 'Edit Showroom', 'skaflow' ),
			'view_item'     => __( 'View Showroom', 'skaflow' ),
			'search_items'  => __( 'Search Showrooms', 'skaflow' ),
			'not_found'     => __( 'No showrooms found', 'skaflow' ),
			'archives'      => __( 'Showrooms', 'skaflow' ),
		);

		register_post_type(
			'showrooms',
			array(
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_rest'        => true,
				'query_var'           => true,
				'rewrite'             => array(
					'slug'       => 'showrooms',
					'with_front' => false,
				),
				'has_archive'         => true,
				'hierarchical'        => false,
				'exclude_from_search' => false,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'menu_icon'           => 'dashicons-admin-site',
				'menu_position'       => 25,
				'supports'            => array( 'title', 'thumbnail', 'revisions', 'editor', 'page-attributes' ),
			)
		);
	}

	/**
	 * Register the FAQ category taxonomy. Admin + REST only; no public archives.
	 */
	private function register_faq_category_taxonomy(): void {
		$labels = array(
			'name'                       => __( 'FAQ Categories', 'skaflow' ),
			'singular_name'              => __( 'FAQ Category', 'skaflow' ),
			'menu_name'                  => __( 'FAQ Categories', 'skaflow' ),
			'all_items'                  => __( 'All FAQ Categories', 'skaflow' ),
			'edit_item'                  => __( 'Edit FAQ Category', 'skaflow' ),
			'view_item'                  => __( 'View FAQ Category', 'skaflow' ),
			'update_item'                => __( 'Update FAQ Category name', 'skaflow' ),
			'add_new_item'               => __( 'Add new FAQ Category', 'skaflow' ),
			'new_item_name'              => __( 'New FAQ Category name', 'skaflow' ),
			'parent_item'                => __( 'Parent FAQ Category', 'skaflow' ),
			'parent_item_colon'          => __( 'Parent FAQ Category:', 'skaflow' ),
			'search_items'               => __( 'Search FAQ Categories', 'skaflow' ),
			'popular_items'              => __( 'Popular FAQ Categories', 'skaflow' ),
			'separate_items_with_commas' => __( 'Separate FAQ Categories with commas', 'skaflow' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ Categories', 'skaflow' ),
			'choose_from_most_used'      => __( 'Choose from the most used FAQ Categories', 'skaflow' ),
			'not_found'                  => __( 'No FAQ Categories found', 'skaflow' ),
			'no_terms'                   => __( 'No FAQ Categories', 'skaflow' ),
			'items_list_navigation'      => __( 'FAQ Categories list navigation', 'skaflow' ),
			'items_list'                 => __( 'FAQ Categories list', 'skaflow' ),
			'back_to_items'              => __( 'Back to FAQ Categories', 'skaflow' ),
		);

		register_taxonomy(
			'skaflow_faq_category',
			array( 'skaflow_faq' ),
			array(
				'label'                 => __( 'FAQ Categories', 'skaflow' ),
				'labels'                => $labels,
				'public'                => false,
				'publicly_queryable'    => false,
				'hierarchical'          => true,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'show_in_nav_menus'     => false,
				'show_admin_column'     => true,
				'show_in_rest'          => true,
				'show_in_quick_edit'    => true,
				'rest_base'             => 'faq-categories',
				'rewrite'               => false,
				'rest_controller_class' => 'WP_REST_Terms_Controller',
				'query_var'             => false,
				'show_in_graphql'       => false,
			)
		);
	}

	/**
	 * Register the showroom-locations taxonomy.
	 */
	private function register_showroom_locations_taxonomy(): void {
		$labels = array(
			'name'          => __( 'Showroom Locations', 'skaflow' ),
			'singular_name' => __( 'Showroom Location', 'skaflow' ),
			'menu_name'     => __( 'Showroom Locations', 'skaflow' ),
			'all_items'     => __( 'All Showroom Locations', 'skaflow' ),
			'edit_item'     => __( 'Edit Showroom Location', 'skaflow' ),
			'view_item'     => __( 'View Showroom Location', 'skaflow' ),
			'update_item'   => __( 'Update Showroom Location', 'skaflow' ),
			'add_new_item'  => __( 'Add New Showroom Location', 'skaflow' ),
			'new_item_name' => __( 'New Showroom Location Name', 'skaflow' ),
			'search_items'  => __( 'Search Showroom Locations', 'skaflow' ),
			'not_found'     => __( 'No showroom locations found', 'skaflow' ),
		);

		register_taxonomy(
			'showroom-locations',
			array( 'showrooms' ),
			array(
				'labels'             => $labels,
				'public'             => false,
				'publicly_queryable' => false,
				'hierarchical'       => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_nav_menus'  => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
				'rewrite'            => false,
				'query_var'          => false,
			)
		);
	}
}
