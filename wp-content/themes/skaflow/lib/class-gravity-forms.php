<?php
/**
 * Gravity Forms theme integration.
 *
 * @package Skaflow
 */

namespace Skaflow;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms theme integration.
 */
class Gravity_Forms {

	/**
	 * Lucide icon slug injected before submit button labels.
	 *
	 * @var string
	 */
	private const SUBMIT_BUTTON_ICON = 'mail';

	/**
	 * Gravity_Forms constructor.
	 */
	public function __construct() {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		$this->register_hooks();
	}

	/**
	 * Register hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_filter( 'gform_submit_button', array( $this, 'add_submit_button_icon' ), 10, 2 );
	}

	/**
	 * Prepend a Lucide icon to Gravity Forms submit buttons.
	 *
	 * GF renders a plain <button> with no wrapper, so we inject a
	 * sk-icon-preview span (see assets/css/button-icon-font.css) rather
	 * than relying on ::before on the button element.
	 *
	 * @param string $button Submit button HTML.
	 * @param array  $form   Form object.
	 *
	 * @return string
	 */
	public function add_submit_button_icon( string $button, array $form ): string {
		unset( $form );

		if ( ! preg_match( '/<button\b/i', $button ) ) {
			return $button;
		}

		$original  = $button;
		$icon_slug = sanitize_key( self::SUBMIT_BUTTON_ICON );
		$icon      = sprintf(
			'<span class="sk-icon-preview sk-icon-%1$s" aria-hidden="true"></span>',
			esc_attr( $icon_slug )
		);

		$button = preg_replace_callback(
			'/(<button\b[^>]*\bclass=)(["\'])([^"\']*)(\2)/i',
			static function ( array $matches ): string {
				$classes = trim( $matches[3] . ' sk-has-icon sk-gform-submit' );

				return $matches[1] . $matches[2] . $classes . $matches[4];
			},
			$button,
			1
		);

		if ( null === $button ) {
			return $original;
		}

		$button = preg_replace( '/(<button\b[^>]*>)/i', '$1' . $icon, $button, 1 );

		return null === $button ? $original : $button;
	}
}
