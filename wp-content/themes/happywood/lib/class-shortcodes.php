<?php
/**
 * Theme shortcodes (ACF field tokens, etc.).
 *
 * @package Happywood
 */

namespace Happywood;

/**
 * Theme shortcodes.
 *
 * @since      2.0.0
 * @package    Happywood
 * @subpackage Happywood/lib
 */
class Shortcodes {

	/**
	 * Shortcodes constructor.
	 */
	public function __construct() {

		$this->register_hooks();
	}

	/**
	 * Register shortcode hooks.
	 */
	private function register_hooks(): void {

		add_shortcode( 'year', array( $this, 'render_year' ) );
		add_shortcode( 'happywood', array( $this, 'render_acf_field' ) );
	}

	/**
	 * Output the current year, e.g. for copyright footers.
	 *
	 * @param array<string, mixed> $atts    Shortcode attributes.
	 * @param string|null          $content Enclosed content.
	 * @param string               $tag     Shortcode tag name.
	 *
	 * @return string
	 */
	public function render_year( $atts = array(), $content = null, $tag = '' ): string {

		unset( $content, $tag, $atts );

		return (string) gmdate( 'Y' );
	}

	/**
	 * Display an ACF field for the current post or term.
	 *
	 * Usage: [happywood field="hero_title" tag="h1"]
	 *
	 * @param array<string, mixed> $atts    Shortcode attributes.
	 * @param string|null          $content Enclosed content.
	 * @param string               $tag     Shortcode tag name.
	 *
	 * @return string
	 */
	public function render_acf_field( $atts = array(), $content = null, $tag = '' ): string {

		unset( $content, $tag );

		if ( ! function_exists( 'get_field' ) ) {
			return '';
		}

		$atts = array_change_key_case( (array) $atts, CASE_LOWER );
		$atts = shortcode_atts(
			array(
				'field'       => '',
				'tag'         => '',
				'meta'        => 'post',
				'default'     => '',
				'fallback'    => '',
				'fallback_to' => 'title',
			),
			$atts,
			'happywood'
		);

		if ( empty( $atts['field'] ) ) {
			return '';
		}

		$wrapper_tag = sanitize_key( $atts['tag'] );
		$before      = $wrapper_tag !== '' ? '<' . $wrapper_tag . '>' : '';
		$after       = $wrapper_tag !== '' ? '</' . $wrapper_tag . '>' : '';
		$output      = '';

		switch ( $atts['meta'] ) {
			case 'term':
				$output = $this->get_term_field_value( $atts );
				break;

			case 'post':
			default:
				$output = $this->get_post_field_value( $atts );
				break;
		}

		if ( $output === '' || $output === null ) {
			return '';
		}

		return $before . $this->format_field_value( $output ) . $after;
	}

	/**
	 * Resolve an ACF field value for the current post.
	 *
	 * @param array<string, string> $atts Normalized shortcode attributes.
	 *
	 * @return mixed
	 */
	private function get_post_field_value( array $atts ) {

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return '';
		}

		$field_value = get_field( $atts['field'], $post_id );

		if ( $this->is_empty_field_value( $field_value ) && $atts['fallback'] !== '' ) {
			$field_value = get_field( $atts['fallback'], $post_id );
		}

		if ( $this->is_empty_field_value( $field_value ) ) {
			$field_value = $this->get_post_fallback_value( $post_id, $atts['fallback_to'] );
		}

		if ( $this->is_empty_field_value( $field_value ) ) {
			$field_value = $atts['default'];
		}

		return $field_value;
	}

	/**
	 * Resolve an ACF field value for the current term.
	 *
	 * @param array<string, string> $atts Normalized shortcode attributes.
	 *
	 * @return mixed
	 */
	private function get_term_field_value( array $atts ) {

		$term = get_queried_object();
		if ( ! $term || is_wp_error( $term ) ) {
			return '';
		}

		$field_value = get_field( $atts['field'], $term );

		if ( $this->is_empty_field_value( $field_value ) && $atts['fallback'] !== '' ) {
			$field_value = get_field( $atts['fallback'], $term );
		}

		if ( $this->is_empty_field_value( $field_value ) ) {
			$field_value = $this->get_term_fallback_value( $term, $atts['fallback_to'] );
		}

		if ( $this->is_empty_field_value( $field_value ) ) {
			$field_value = $atts['default'];
		}

		return $field_value;
	}

	/**
	 * Fallback to a native post property when ACF values are empty.
	 *
	 * @param int    $post_id     Current post ID.
	 * @param string $fallback_to Property name or alias.
	 *
	 * @return mixed
	 */
	private function get_post_fallback_value( int $post_id, string $fallback_to ) {

		if ( $fallback_to === 'post_type' ) {
			$post_type = get_post_type( $post_id );
			$object    = $post_type ? get_post_type_object( $post_type ) : null;

			return $object ? $object->labels->name : '';
		}

		$post = get_post( $post_id );
		if ( ! $post ) {
			return '';
		}

		$property = $this->resolve_post_property( $fallback_to );

		return isset( $post->{$property} ) ? $post->{$property} : '';
	}

	/**
	 * Fallback to a native term property when ACF values are empty.
	 *
	 * @param object $term        Queried term object.
	 * @param string $fallback_to Property name or alias.
	 *
	 * @return mixed
	 */
	private function get_term_fallback_value( $term, string $fallback_to ) {

		$property = $this->resolve_term_property( $fallback_to );

		return isset( $term->{$property} ) ? $term->{$property} : '';
	}

	/**
	 * Map common fallback aliases to WP_Post properties.
	 *
	 * @param string $fallback_to Requested fallback key.
	 */
	private function resolve_post_property( string $fallback_to ): string {

		if ( $fallback_to === 'title' ) {
			return 'post_title';
		}

		return $fallback_to;
	}

	/**
	 * Map common fallback aliases to WP_Term properties.
	 *
	 * @param string $fallback_to Requested fallback key.
	 */
	private function resolve_term_property( string $fallback_to ): string {

		if ( $fallback_to === 'title' ) {
			return 'name';
		}

		return $fallback_to;
	}

	/**
	 * Whether an ACF value should be treated as empty for fallback purposes.
	 *
	 * @param mixed $value Field value.
	 */
	private function is_empty_field_value( $value ): bool {

		return $value === null || $value === '' || $value === false;
	}

	/**
	 * Format a resolved field value for front-end output.
	 *
	 * @param mixed $value Field value.
	 */
	private function format_field_value( $value ): string {

		if ( is_array( $value ) || is_object( $value ) ) {
			return esc_html( wp_json_encode( $value ) );
		}

		return wp_kses_post( do_blocks( do_shortcode( (string) $value ) ) );
	}
}
