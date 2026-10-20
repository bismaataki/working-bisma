<?php
/**
 * Front-end asset enqueue and layout overlay.
 *
 * @package Happywood
 */

namespace Happywood;

use Error;

/**
 * The public-facing functionality of the theme.
 *
 * @link       https://github.com/atakidevelopers/happywood
 * @author     AtakiDev <developers@ataki.dev>
 * @since      0.0.1
 * @package    Happywood
 * @subpackage Happywood/public
 */
class Front {

	/**
	 * @var string $public_style_handle the public style handle id
	 */
	private $public_style_handle;

	/**
	 * @var string $public_script_handle the public style handle id
	 */
	private $public_script_handle;

	public function __construct() {

		$this->public_style_handle  = HAPPYWOOD_IDENTIFIER . '-public-style';
		$this->public_script_handle = HAPPYWOOD_IDENTIFIER . '-public-script';

		$this->register_hooks();
	}

	/**
	 * Register required hooks
	 */
	public function register_hooks() {

		/**
		 * Enqueue Styles — priority 20 so YITH frontend CSS (11) is registered first.
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 20 );

		/**
		 * Enqueue Scripts
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/**
		 * Layout overlay. Currently always on (hardcoded in maybe_enable_grid_guides).
		 * Theme Options has enable_grid_guides but this method does not read it.
		 * CSS: src/sass/components/_index.scss
		 */
		add_action( 'wp_footer', array( $this, 'maybe_enable_grid_guides' ) );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {

		$style_css = 'public.css';
		$deps      = array();

		if ( wp_style_is( 'yith_ywraq_frontend', 'registered' ) ) {
			$deps[] = 'yith_ywraq_frontend';
		}

		// Load after WooCommerce + Select2 so form-field height overrides win.
		if ( wp_style_is( 'woocommerce-general', 'registered' ) ) {
			$deps[] = 'woocommerce-general';
		}

		if ( wp_style_is( 'select2', 'registered' ) ) {
			$deps[] = 'select2';
		}

		wp_enqueue_style(
			$this->public_style_handle,
			happywood_get_build_url( $style_css ),
			$deps,
			filemtime( happywood_get_build_dir( $style_css ) )
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {

		$script_asset_path = happywood_get_build_dir( 'public.asset.php' );

		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				'You need to first run `npm start` or `npm run build` for the blocks offered by this them. Could Not find the index.asset.php file'
			);
		}

		/**
		 * Register Scripts
		 */
		$script_asset = require $script_asset_path;
		wp_enqueue_script(
			$this->public_script_handle,
			happywood_get_build_url( 'public.js' ),
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);

		/**
		 * Localize Scripts
		 * Passes translations to JavaScript.
		 */
		if ( function_exists( 'wp_set_script_translations' ) ) {
			/**
			 * For details see
			 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			 */
			wp_set_script_translations( $this->public_script_handle, 'happywood' );
		}

		wp_localize_script(
			$this->public_script_handle,
			'Happywood_Public',
			$this->get_localize_script_data()
		);
	}

	/**
	 * Localize Public facing scripts
	 */
	public function get_localize_script_data() {

		$quick_view_display = 'drawer';
		$quick_view_content = 'dimensions';

		if ( function_exists( 'get_field' ) ) {
			$quick_view_display = get_field( 'quick_view_display', 'option' ) ?: 'drawer';
			$quick_view_content = get_field( 'quick_view_content', 'option' ) ?: 'dimensions';
		}

		$localize_data = array(
			'site_url'              => get_site_url(),
			'nonce'                 => wp_create_nonce( 'wp_rest' ),
			'storeApiNonce'         => wp_create_nonce( 'wc_store_api' ),
			'storeApiUrl'           => rest_url( 'wc/store/v1/cart/add-item' ),
			'rest_url'              => rest_url( 'happywood/v1/product-search' ),
			'loadMoreViewingStatus' => __( 'Viewing %1$s of %2$s', 'happywood' ),
			'resultsCountSingle'    => __( 'Showing the single result', 'happywood' ),
			'resultsCountAll'       => __( 'Showing all %1$s results', 'happywood' ),
			'resultsCountRange'     => __( 'Showing %1$s–%2$s of %3$s results', 'happywood' ),
			'quickViewRestUrl'      => rest_url( 'happywood/v1/quick-view' ),
			'inCartLabel'           => _x( '%d in cart', 'Product card add-to-cart quantity label', 'woocommerce' ),
			'quickViewDisplay'      => in_array( $quick_view_display, array( 'modal', 'drawer' ), true ) ? $quick_view_display : 'drawer',
			'quickViewContent'      => in_array( $quick_view_content, array( 'dimensions', 'short_description' ), true ) ? $quick_view_content : 'dimensions',
			'wishlistEnabled'       => happywood_is_wishlist_enabled(),
			'wishlistLoopEnabled'   => happywood_is_wishlist_loop_enabled(),
			'wishlistIsLoggedIn'    => is_user_logged_in(),
			'wishlistToggleUrl'     => rest_url( 'happywood/v1/wishlist/toggle' ),
			'wishlistStatusUrl'     => rest_url( 'happywood/v1/wishlist/status' ),
			'wishlistLoginUrl'      => happywood_get_wishlist_login_url(),
			'wishlistAddLabel'      => __( 'Add to wishlist', 'happywood' ),
			'wishlistRemoveLabel'   => __( 'Remove from wishlist', 'happywood' ),
			'filtersPanelDesktop'   => happywood_get_filters_panel_desktop(),
			'filtersPanelMobile'    => happywood_get_filters_panel_mobile(),
		);

		return apply_filters( HAPPYWOOD_PREFIX . 'public_script_localize_data', $localize_data );
	}


	/**
	 * Print the layout overlay. Always on — the ACF field is not read.
	 *
	 * @hooked wp_footer
	 */
	public function maybe_enable_grid_guides() {

		if ( ! function_exists( 'get_field' ) ) {
			return;
		}

		$enable_grid_guides = true;

		if ( $enable_grid_guides ) {
			get_template_part( 'partials/grid', 'overlay' );
		}
	}
}
