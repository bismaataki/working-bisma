<?php
/**
 * Small wp-admin CSS/JS fixes.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Skaflow\Admin_Style_Fixes' ) ) {
	return;
}

/**
 * Small, permanent wp-admin CSS overrides for third-party plugin conflicts.
 *
 * Not part of the Sass/webpack build — `src/sass-admin/` only loads on
 * block-editor screens (`enqueue_block_editor_assets`), but these fixes
 * need to apply on every wp-admin screen, so they're a tiny inline style
 * attached to WordPress's own always-enqueued `common` admin stylesheet.
 */
class Admin_Style_Fixes {

	/** @var string Heroic TOC editor stylesheet handle. */
	private const HEROIC_TOC_EDITOR_STYLE = 'ht_toc-block-editor-css';

	public function __construct() {
		$this->register_hooks();
	}

	private function register_hooks(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_style_overrides' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'dequeue_conflicting_block_editor_styles' ), 100 );
		add_action( 'admin_print_styles', array( $this, 'dequeue_conflicting_block_editor_styles' ), 100 );
		add_filter( 'style_loader_src', array( $this, 'block_heroic_toc_editor_style_src' ), 10, 2 );
	}

	/**
	 * @hooked admin_enqueue_scripts
	 */
	public function enqueue_admin_style_overrides(): void {
		wp_add_inline_style( 'common', $this->get_inline_css() );
	}

	/**
	 * Heroic TOC `blocks.editor.build.css` hides global Gutenberg classes
	 * under `body:not(.wp-admin)`. Dequeue/deregister it in the block editor;
	 * frontend + shared styling still comes from `blocks.style.build.css`
	 * and theme `_heroic-toc.scss`.
	 *
	 * @hooked enqueue_block_editor_assets
	 * @hooked admin_print_styles
	 */
	public function dequeue_conflicting_block_editor_styles(): void {
		wp_dequeue_style( self::HEROIC_TOC_EDITOR_STYLE );
		wp_deregister_style( self::HEROIC_TOC_EDITOR_STYLE );
	}

	/**
	 * Hard block the stylesheet URL if another callback re-enqueues it.
	 *
	 * @param string|false $src    Style source.
	 * @param string       $handle Style handle.
	 * @return string|false
	 *
	 * @hooked style_loader_src
	 */
	public function block_heroic_toc_editor_style_src( $src, $handle ) {
		if ( self::HEROIC_TOC_EDITOR_STYLE === $handle ) {
			return false;
		}

		return $src;
	}

	/**
	 * @return string
	 */
	private function get_inline_css(): string {
		return '
			/*
			 * Verifone Hosted (assets/css/auto_setup.css) ships an
			 * unscoped `button { border-radius: 32px !important; ... }`
			 * rule meant only for its own gateway setup screen. Every
			 * other declaration in it loses to more specific admin CSS,
			 * but the !important border-radius wins everywhere and
			 * rounds every wp-admin button. Restoring native square
			 * corners globally.
			 */
			.wp-core-ui p .button {
				border-radius: unset !important;
			}
		';
	}
}
