<?php
/**
 * Theme bootstrap — constructs feature classes and registers their hooks.
 *
 * @package Skaflow
 */

namespace Skaflow;

/**
 * The core theme class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this theme as well as the current
 * version of the theme.
 *
 * @since      0.0.1
 * @package    Skaflow
 * @subpackage Skaflow/lib
 * @author     AtakiDev <developers@ataki.dev>
 */
class Init {

	/**
	 * The instance of this class
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      object $instance The instance of the current class
	 */
	protected static $instance;
	/**
	 * @var Blocks_Jsx
	 * @noinspection PhpPropertyOnlyWrittenInspection
	 */
	private $blocks;


	/**
	 * Define the core functionality of the theme.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		// Always-on theme shell.
		$this->after_setup_theme();
		$this->define_plugins_manager_hooks();
		$this->define_acf_hooks();
		$this->define_api_hooks();
		$this->define_public_hooks();
		$this->define_shortcodes_hooks();
		$this->define_blocks_jsx_hooks();
		$this->define_editor_curation_hooks();
		$this->define_lucide_icons_hooks();

		// Header / menu — their includes bootstraps load from these classes.
		$this->define_site_header_hooks();
		$this->define_mega_menu_hooks();

		// CPTs and taxonomies.
		$this->define_content_types_hooks();

		// Commerce — WooCommerce() returns early without the plugin.
		$this->define_woocommerce_hooks();

		// Third-party plugin integration.
		$this->define_gravity_forms_hooks();

		// Generic Load More; shop branches hook in from includes/woocommerce/.
		$this->define_load_more_hooks();

		// Always-on admin CSS.
		$this->define_admin_style_fixes_hooks();

		// Commerce admin column on pa_* term lists.
		$this->define_attribute_swatch_preview_column_hooks();

		do_action( 'skaflow_init_construct' );
	}

	/**
	 * Register hooks related to the after_setup_theme
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function after_setup_theme() {

		new After_Setup_Theme();
		new Theme_Style_Switcher();
	}

	/**
	 * Register hooks related to the required/recommended plugin
	 * dependencies of the theme (TGMPA).
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_plugins_manager_hooks() {

		new Plugins_Manager();
	}


	/**
	 * Register hooks related to the ACF Support
	 * of the theme.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_acf_hooks() {

		new Acf();
	}

	/**
	 * Instantiate the theme's REST endpoints.
	 *
	 * Each endpoint extends `Api` and decides for itself whether to register — see
	 * `Api::is_available()`. The only thing this method knows is the list, so the
	 * `class_exists()` test is what lets a fork delete an endpoint file without
	 * touching `Init` first.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_api_hooks() {

		$endpoints = array(
			Api_Product_Search::class, // commerce — delete with WooCommerce.
			Api_Quick_View::class,     // commerce — delete with WooCommerce.
			Api_Wishlist::class,       // commerce — delete with WooCommerce.
		);

		foreach ( $endpoints as $endpoint ) {
			if ( class_exists( $endpoint ) ) {
				new $endpoint();
			}
		}
	}

	/**
	 * Register hooks related to the public-facing functionality
	 * of the theme.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {

		new Front();
	}

	/**
	 * Register theme shortcodes.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_shortcodes_hooks() {

		new Shortcodes();
	}

	/**
	 * Register all the hooks related to Gutenberg
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_blocks_jsx_hooks() {

		if ( ! function_exists( 'register_block_type' ) ) {
			// Gutenberg is not active.
			return;
		}

		$this->blocks = new Blocks_Jsx();
	}

	/**
	 * Register all the hooks related to Editing Experience Enhancements
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_editor_curation_hooks() {

		new Editor_Curation();
	}

	/**
	 * Register all the hooks related to the Lucide icon font
	 * (Button block icon picker).
	 *
	 * @since    2.0.0
	 * @access   private
	 */
	private function define_lucide_icons_hooks() {

		new Lucide_Icons();
	}

	/**
	 * Register site header render pipeline and header-specific hooks.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_site_header_hooks() {

		new Site_Header();
	}

	/**
	 * Register mega menu walker and menu item hooks.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_mega_menu_hooks() {

		new Mega_Menu();
	}

	/**
	 * Register theme custom post types and taxonomies.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_content_types_hooks() {

		new Content_Types();
		new Showrooms_Admin();
	}

	/**
	 * Register WooCommerce integration (admin, archive, single product).
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_woocommerce_hooks() {

		new WooCommerce();
	}

	/**
	 * Register Gravity Forms integration hooks.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_gravity_forms_hooks() {

		new Gravity_Forms();
	}

	/**
	 * Register page-1 Load More for Product Collection pagination.
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_load_more_hooks() {

		new Load_More();
	}

	/**
	 * Register small, permanent wp-admin CSS overrides for third-party
	 * plugin conflicts (e.g. Verifone Hosted's unscoped button styling).
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_admin_style_fixes_hooks() {

		new Admin_Style_Fixes();
	}

	/**
	 * Register the "Preview" swatch column on attribute taxonomy term-list
	 * screens (edit-tags.php?taxonomy=pa_*).
	 *
	 * @since 2.0.0
	 * @access private
	 */
	private function define_attribute_swatch_preview_column_hooks() {

		new Attribute_Swatch_Preview_Column();
	}

	/**
	 * get the instance of the main theme class
	 */
	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
