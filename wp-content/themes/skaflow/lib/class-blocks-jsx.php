<?php
/**
 * Auto-register JSX blocks from build-jsx-blocks/.
 *
 * @package Skaflow
 */

namespace Skaflow;

// exit if file is called directly
use Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if class already defined, bail out
if ( class_exists( 'Skaflow\Blocks_Jsx' ) ) {
	return;
}


/**
 * This class will set up the JSX Blocks and enqueue required assets
 *
 * @package    Skaflow
 * @subpackage Skaflow/lib
 */
class Blocks_Jsx {

	/**
	 * @var string $editor_script_handle the editor script handle id
	 */
	private $editor_script_handle;

	/**
	 * @var string $editor_style_handle the editor style handle id
	 */
	private $editor_style_handle;

	/**
	 * @var array a list of blocks directories
	 */
	private $blocks_directories = array();

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		$this->editor_script_handle = SKAFLOW_IDENTIFIER . '-block-editor-script';
		$this->editor_style_handle  = SKAFLOW_IDENTIFIER . '-block-editor-style';

		$this->register_hooks();
	}

	/**
	 * Register required hooks
	 */
	public function register_hooks() {

		/**
		 * Only for Editor (admin)
		 */
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );

		/**
		 * Fires on both Editor (incl. the iframed canvas) and front end.
		 * Used here for the editor block/content styles so Gutenberg can
		 * correctly copy them into the iframed editor canvas. Guarded with
		 * is_admin() below since these styles are editor-only.
		 */
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );

		/**
		 * Register JSX Block Types
		 */
		add_action( 'init', array( $this, 'register_block_type' ) );
	}


	/**
	 * Enqueue CSS and JS assets for Editor
	 *
	 * @hooked  enqueue_block_editor_assets
	 */
	public function enqueue_block_editor_assets() {

		$script_asset_path = skaflow_get_build_dir( 'index.asset.php' );

		if ( ! file_exists( $script_asset_path ) ) {
			throw new Error(
				esc_html( $script_asset_path ) . ' file missing in build directory. Please execute `npm run build` to create theme build directory'
			);
		}

		/**
		 * Register Scripts
		 */
		$script_asset = require $script_asset_path;
		$dependencies = array_unique(
			array_merge(
				$script_asset['dependencies'],
				array(
					'wp-plugins',
					'wp-edit-post',
					'wp-data',
					'wp-element',
					'wp-components',
					'wp-i18n',
					'wp-blocks',
					'wp-dom-ready',
					'wp-hooks',
					'wp-compose',
				)
			)
		);

		wp_enqueue_script(
			$this->editor_script_handle,
			skaflow_get_build_url( 'index.js' ),
			$dependencies,
			$script_asset['version']
		);

		/**
		 * Localize Scripts
		 * Passes translations to JavaScript.
		 */
		if ( function_exists( 'wp_set_script_translations' ) ) {
			/**
			 * May be extended to
			 * wp_set_script_translations( 'my-handle', 'my-domain', plugin_dir_path( MY_PLUGIN . 'languages' )
			 * For details see
			 * https://make.wordpress.org/core/2018/11/09/new-javascript-i18n-support-in-wordpress/
			 */
			wp_set_script_translations( $this->editor_script_handle, 'skaflow' );
		}

		wp_localize_script( $this->editor_script_handle, 'Skaflow', $this->get_localize_script_data() );
	}

	/**
	 * Enqueue CSS Style for the Editor's block content (incl. the iframed canvas)
	 *
	 * @hooked enqueue_block_assets
	 *
	 * @see https://developer.wordpress.org/block-editor/how-to-guides/enqueueing-assets-in-the-editor/
	 */
	public function enqueue_block_assets() {

		/**
		 * These styles are editor-only, bail out on the front end.
		 */
		if ( ! is_admin() ) {
			return;
		}

		$editor_css = 'index.css';

		wp_enqueue_style(
			$this->editor_style_handle,
			skaflow_get_build_url( $editor_css ),
			array(),
			filemtime( skaflow_get_build_dir( $editor_css ) )
		);
	}

	/**
	 *
	 */
	public function get_localize_script_data() {

		$localize_data = array(
			'siteUrl' => get_site_url(),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		);

		return apply_filters( SKAFLOW_PREFIX . 'editor_script_localize_data', $localize_data );
	}

	/**
	 * Register JSX blocks from build-jsx-blocks/.
	 *
	 * Prefers the metadata collection APIs (WP 6.7 / 6.8) when
	 * `blocks-manifest.php` exists. Falls back to one `register_block_type()`
	 * per folder so a zip dropped onto an older WordPress still boots.
	 *
	 * @hooked init
	 */
	public function register_block_type() {

		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$blocks_dir = get_stylesheet_directory() . '/build-jsx-blocks';
		$manifest   = $blocks_dir . '/blocks-manifest.php';

		if (
			file_exists( $manifest ) &&
			function_exists( 'wp_register_block_types_from_metadata_collection' )
		) {
			wp_register_block_types_from_metadata_collection( $blocks_dir, $manifest );
			return;
		}

		if (
			file_exists( $manifest ) &&
			function_exists( 'wp_register_block_metadata_collection' )
		) {
			wp_register_block_metadata_collection( $blocks_dir, $manifest );
		}

		$blocks_directories = $this->get_blocks_directories();

		if ( empty( $blocks_directories ) ) {
			return;
		}

		foreach ( $blocks_directories as $block_directory ) {
			register_block_type( $block_directory );
		}
	}

	/**
	 * @return array block directories paths
	 */
	public function get_blocks_directories(): array {

		if ( ! $this->blocks_directories ) {
			$blocks_dir = get_stylesheet_directory() . '/build-jsx-blocks/';
			// Loop over each block folder in the blocks directory
			$dirs = glob( $blocks_dir . '*', GLOB_ONLYDIR );

			if ( ! empty( $dirs ) ) {
				$this->blocks_directories = $dirs;
			}
		}

		return apply_filters( SKAFLOW_PREFIX . 'jsx_blocks_directories', $this->blocks_directories );
	}
}
