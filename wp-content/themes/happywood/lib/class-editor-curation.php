<?php
/**
 * Block editor curation (allowed blocks, inspector).
 *
 * @package Happywood
 */

namespace Happywood;

// exit if file is called directly
use WP_Block_Editor_Context;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// if class already defined, bail out
if ( class_exists( 'Happywood\Editor_Curation' ) ) {
	return;
}


/**
 * This class deals with editor Curation Experience to provide a better experience to the user
 *
 * @package    Happywood
 * @subpackage Happywood/lib
 */
class Editor_Curation {


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 */
	public function __construct() {

		$this->register_hooks();
	}

	/**
	 * Register required hooks
	 */
	public function register_hooks() {

		add_filter( 'block_editor_settings_all', array( $this, 'disable_openverse' ), 10, 2 );

		add_filter( 'block_editor_settings_all', array( $this, 'set_default_image_size' ), 10, 2 );

		add_filter( 'block_editor_settings_all', array( $this, 'restrict_settings_to_administrator_only' ), 10, 2 );

		/**
		 * Globally disable the Block Directory.
		 */
		remove_action( 'enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets' );

		/**
		 * Globally disable the Pattern Directory.
		 */
		add_filter( 'should_load_remote_block_patterns', '__return_false' );

		add_filter( 'register_block_type_args', array( $this, 'curate_happywood_blocks' ), 10, 2 );

		add_filter( 'allowed_block_types_all', array( $this, 'restrict_banner_editor_blocks' ), 10, 2 );
	}

	/**
	 * Set the default image size when inserted into the Editor.
	 *
	 * @param array $settings The current block editor settings.
	 * @param array $context The current editor context, including post information.
	 *
	 * @return array          The modified block editor settings.
	 */
	public function set_default_image_size( $settings, $context ) {
		if (
			! empty( $context->post ) &&
			'post' === $context->post->post_type
		) {
			// Set the default image size to full.
			$settings['imageDefaultSize'] = 'full';
		}

		return $settings;
	}


	/**
	 * Disable Openverse.
	 *
	 * @param array $settings The current block editor settings.
	 * @param array $context The current editor context, including post information.
	 *
	 * @return array          The modified block editor settings.
	 */
	public function disable_openverse( $settings, $context ) {
		$settings['enableOpenverseMediaCategory'] = false;

		return $settings;
	}

	/**
	 * Allow only Administrators to access the block locking user interface.
	 *
	 * @param array                   $settings Default editor settings.
	 * @param WP_Block_Editor_Context $context The current block editor context.
	 */
	public function restrict_settings_to_administrator_only( $settings, $context ) {
		$is_administrator = current_user_can( 'edit_theme_options' );

		if ( ! $is_administrator ) {
			// Allow only Administrators to access the block locking user interface.
			$settings['canLockBlocks']      = false;
			$settings['codeEditingEnabled'] = false;
		}

		return $settings;
	}

	/**
	 * Theme block registration tweaks (inserter visibility, etc.).
	 *
	 * @param array  $args       Block type registration args.
	 * @param string $block_type Block name.
	 *
	 * @return array
	 */
	public function curate_happywood_blocks( array $args, string $block_type ): array {

		if (
			'happywood/editor-placeholder' === $block_type
			|| 'happywood/site-header' === $block_type
			|| 'happywood/product-filters' === $block_type
			|| 'happywood/product-reviews-summary' === $block_type
			|| 'happywood/product-reviews' === $block_type
			|| 'happywood/product-faqs' === $block_type
		) {
			$args['supports']['inserter'] = false;
		}

		return $args;
	}

	/**
	 * Remove heading blocks from Banner CPT content (use Paragraph + Heading Like style).
	 *
	 * @param bool|string[]           $allowed_block_types Allowed block types.
	 * @param WP_Block_Editor_Context $context             Editor context.
	 * @return bool|string[]
	 */
	public function restrict_banner_editor_blocks( $allowed_block_types, WP_Block_Editor_Context $context ) {
		if ( empty( $context->post ) || 'happywood_banner' !== $context->post->post_type ) {
			return $allowed_block_types;
		}

		if ( true === $allowed_block_types ) {
			$registered          = \WP_Block_Type_Registry::get_instance()->get_all_registered();
			$allowed_block_types = array_keys( $registered );
		}

		if ( ! is_array( $allowed_block_types ) ) {
			return $allowed_block_types;
		}

		return array_values(
			array_filter(
				$allowed_block_types,
				static function ( string $block_name ): bool {
					return 'core/heading' !== $block_name;
				}
			)
		);
	}
}
