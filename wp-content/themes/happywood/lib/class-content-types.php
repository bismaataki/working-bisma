<?php
/**
 * Register all theme CPTs and taxonomies.
 *
 * @package Happywood
 */

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\Content_Types' ) ) {
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
		$this->register_testimonial_post_type();
	}

	/**
	 * Register custom taxonomies.
	 *
	 * @hooked init, priority 11 — after post types.
	 */
	public function register_taxonomies(): void {
		$this->register_faq_category_taxonomy();
	}

	/**
	 * Register the FAQ post type. Archive at /faq/; singles 404 via FAQ archive helpers.
	 */
	private function register_faq_post_type(): void {
		$labels = array(
			'name'                     => __( 'FAQs', 'happywood' ),
			'singular_name'            => __( 'FAQ', 'happywood' ),
			'menu_name'                => __( 'FAQs', 'happywood' ),
			'all_items'                => __( 'All FAQs', 'happywood' ),
			'add_new'                  => __( 'Add new', 'happywood' ),
			'add_new_item'             => __( 'Add new FAQ', 'happywood' ),
			'edit_item'                => __( 'Edit FAQ', 'happywood' ),
			'new_item'                 => __( 'New FAQ', 'happywood' ),
			'view_item'                => __( 'View FAQ', 'happywood' ),
			'view_items'               => __( 'View FAQs', 'happywood' ),
			'search_items'             => __( 'Search FAQs', 'happywood' ),
			'not_found'                => __( 'No FAQs found', 'happywood' ),
			'not_found_in_trash'       => __( 'No FAQs found in trash', 'happywood' ),
			'parent'                   => __( 'Parent FAQ:', 'happywood' ),
			'featured_image'           => __( 'Featured image for this FAQ', 'happywood' ),
			'set_featured_image'       => __( 'Set featured image for this FAQ', 'happywood' ),
			'remove_featured_image'    => __( 'Remove featured image for this FAQ', 'happywood' ),
			'use_featured_image'       => __( 'Use as featured image for this FAQ', 'happywood' ),
			'archives'                 => __( 'FAQ', 'happywood' ),
			'insert_into_item'         => __( /** @lang text */ 'Insert into FAQ', 'happywood' ),
			'uploaded_to_this_item'    => __( 'Upload to this FAQ', 'happywood' ),
			'filter_items_list'        => __( 'Filter FAQs list', 'happywood' ),
			'items_list_navigation'    => __( 'FAQs list navigation', 'happywood' ),
			'items_list'               => __( 'FAQs list', 'happywood' ),
			'attributes'               => __( 'FAQs attributes', 'happywood' ),
			'name_admin_bar'           => __( 'FAQ', 'happywood' ),
			'item_published'           => __( 'FAQ published', 'happywood' ),
			'item_published_privately' => __( 'FAQ published privately.', 'happywood' ),
			'item_reverted_to_draft'   => __( 'FAQ reverted to draft.', 'happywood' ),
			'item_scheduled'           => __( 'FAQ scheduled', 'happywood' ),
			'item_updated'             => __( 'FAQ updated.', 'happywood' ),
			'parent_item_colon'        => __( 'Parent FAQ:', 'happywood' ),
		);

		register_post_type(
			'happywood_faq',
			array(
				'label'                 => __( 'FAQs', 'happywood' ),
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
	 * Register the Testimonials post type.
	 */
	private function register_testimonial_post_type(): void {
		$labels = array(
			'name'               => __( 'Testimonials', 'happywood' ),
			'singular_name'      => __( 'Testimonial', 'happywood' ),
			'menu_name'          => __( 'Testimonials', 'happywood' ),
			'all_items'          => __( 'All Testimonials', 'happywood' ),
			'add_new'            => __( 'Add New', 'happywood' ),
			'add_new_item'       => __( 'Add New Testimonial', 'happywood' ),
			'edit_item'          => __( 'Edit Testimonial', 'happywood' ),
			'new_item'           => __( 'New Testimonial', 'happywood' ),
			'view_item'          => __( 'View Testimonial', 'happywood' ),
			'search_items'       => __( 'Search Testimonials', 'happywood' ),
			'not_found'          => __( 'No testimonials found', 'happywood' ),
			'not_found_in_trash' => __( 'No testimonials found in Trash', 'happywood' ),
			'item_published'     => __( 'Testimonial published.', 'happywood' ),
			'item_updated'       => __( 'Testimonial updated.', 'happywood' ),
		);

		register_post_type(
			'happywood_review',
			array(
				'label'               => __( 'Testimonials', 'happywood' ),
				'labels'              => $labels,
				'public'              => true,
				'publicly_queryable'  => false,
				'show_ui'             => true,
				'show_in_rest'        => true,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
				'delete_with_user'    => false,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'menu_icon'           => 'dashicons-format-quote',
				'menu_position'       => 23,
				'supports'            => array( 'title', 'revisions' ),
			)
		);
	}

	/**
	 * Register the Banner post type. Admin-only — no public archive or singles.
	 */
	private function register_banner_post_type(): void {
		$labels = array(
			'name'                     => __( 'Banners', 'happywood' ),
			'singular_name'            => __( 'Banner', 'happywood' ),
			'menu_name'                => __( 'Banners', 'happywood' ),
			'all_items'                => __( 'All Banners', 'happywood' ),
			'add_new'                  => __( 'Add new', 'happywood' ),
			'add_new_item'             => __( 'Add new Banner', 'happywood' ),
			'edit_item'                => __( 'Edit Banner', 'happywood' ),
			'new_item'                 => __( 'New Banner', 'happywood' ),
			'view_item'                => __( 'View Banner', 'happywood' ),
			'view_items'               => __( 'View Banners', 'happywood' ),
			'search_items'             => __( 'Search Banners', 'happywood' ),
			'not_found'                => __( 'No Banners found', 'happywood' ),
			'not_found_in_trash'       => __( 'No Banners found in trash', 'happywood' ),
			'item_published'           => __( 'Banner published', 'happywood' ),
			'item_published_privately' => __( 'Banner published privately.', 'happywood' ),
			'item_reverted_to_draft'   => __( 'Banner reverted to draft.', 'happywood' ),
			'item_scheduled'           => __( 'Banner scheduled', 'happywood' ),
			'item_updated'             => __( 'Banner updated.', 'happywood' ),
		);

		register_post_type(
			'happywood_banner',
			array(
				'label'               => __( 'Banners', 'happywood' ),
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
	 * Register the FAQ category taxonomy. Admin + REST only; no public archives.
	 */
	private function register_faq_category_taxonomy(): void {
		$labels = array(
			'name'                       => __( 'FAQ Categories', 'happywood' ),
			'singular_name'              => __( 'FAQ Category', 'happywood' ),
			'menu_name'                  => __( 'FAQ Categories', 'happywood' ),
			'all_items'                  => __( 'All FAQ Categories', 'happywood' ),
			'edit_item'                  => __( 'Edit FAQ Category', 'happywood' ),
			'view_item'                  => __( 'View FAQ Category', 'happywood' ),
			'update_item'                => __( 'Update FAQ Category name', 'happywood' ),
			'add_new_item'               => __( 'Add new FAQ Category', 'happywood' ),
			'new_item_name'              => __( 'New FAQ Category name', 'happywood' ),
			'parent_item'                => __( 'Parent FAQ Category', 'happywood' ),
			'parent_item_colon'          => __( 'Parent FAQ Category:', 'happywood' ),
			'search_items'               => __( 'Search FAQ Categories', 'happywood' ),
			'popular_items'              => __( 'Popular FAQ Categories', 'happywood' ),
			'separate_items_with_commas' => __( 'Separate FAQ Categories with commas', 'happywood' ),
			'add_or_remove_items'        => __( 'Add or remove FAQ Categories', 'happywood' ),
			'choose_from_most_used'      => __( 'Choose from the most used FAQ Categories', 'happywood' ),
			'not_found'                  => __( 'No FAQ Categories found', 'happywood' ),
			'no_terms'                   => __( 'No FAQ Categories', 'happywood' ),
			'items_list_navigation'      => __( 'FAQ Categories list navigation', 'happywood' ),
			'items_list'                 => __( 'FAQ Categories list', 'happywood' ),
			'back_to_items'              => __( 'Back to FAQ Categories', 'happywood' ),
		);

		register_taxonomy(
			'happywood_faq_category',
			array( 'happywood_faq' ),
			array(
				'label'                 => __( 'FAQ Categories', 'happywood' ),
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
}
