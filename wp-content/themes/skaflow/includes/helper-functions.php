<?php
/**
 * Generic theme helpers — build paths, debug, buttons, and markup.
 *
 * @package Skaflow
 */

/**
 * Get Build URL path
 *
 * @param string $file_name_with_sub_dir Path relative to the theme build/ directory.
 *
 * @return string
 */
function skaflow_get_build_url( $file_name_with_sub_dir ): string {
	return trailingslashit( get_stylesheet_directory_uri() ) . trailingslashit( 'build' ) . $file_name_with_sub_dir;
}


/**
 * Get Build Dir path
 *
 * @param string $file_name_with_sub_dir Path relative to the theme build/ directory.
 *
 * @return string
 */
function skaflow_get_build_dir( $file_name_with_sub_dir ): string {
	return get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . $file_name_with_sub_dir;
}


/**
 * Featured-image MIME subtype for a post (e.g. jpeg, png).
 *
 * @param int $post_id Post ID.
 *
 * @return string
 * @noinspection PhpUnused
 */
function skaflow_get_post_thumbnail_mime_type( $post_id ): string {
	$mime_type = mime_content_type( get_attached_file( get_post_thumbnail_id( $post_id ) ) );

	return sanitize_key( str_replace( 'image', '', $mime_type ) );
}

/**
 * Theme Options default placeholder attachment ID (General tab).
 *
 * @return int Attachment ID, or 0 when unset/disabled.
 */
function skaflow_get_default_placeholder_image_id(): int {
	if ( ! function_exists( 'get_field' ) ) {
		return 0;
	}

	return absint( get_field( 'placeholder_image_default', 'option' ) );
}

/**
 * A Helper function for dump in readable format
 *
 * @param mixed $var Value to print.
 *
 * @return void
 * @noinspection PhpUnused
 */
function skaflow_dump( $var ): void {
	echo '<pre>';
	echo preg_replace( '(\d+\s=>)', '', var_export( $var, true ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- debug dump helper.
	echo '</pre>';
}


if ( ! function_exists( 'skaflow_write_log' ) ) {
	/**
	 * A Helper function added to log anything for debugging
	 *
	 * @param mixed $log Value to append to the theme debug.log.
	 *
	 * @return void
	 */
	function skaflow_write_log( $log ): void {

		if ( is_array( $log ) || is_object( $log ) ) {
			$log = print_r( $log, true );
		}
		error_log( basename( __FILE__ ) . ' : ' . __LINE__ . ' : ' . $log . PHP_EOL, 3, trailingslashit( get_stylesheet_directory() ) . 'debug.log' );
	}
}

/** @noinspection PhpUnused */
/**
 * Insert items into an array at a numeric index.
 *
 * @param array $array    Existing array.
 * @param array $insert   Items to insert.
 * @param int   $position Zero-based index.
 *
 * @return array
 */
function skaflow_insert_array_at_position( $array, $insert, $position ): array {
	/*
	$array : The initial array I want to modify
	$insert : the new array I want to add, e.g. array('key' => 'value') or array('value')
	$position : the position where the new array will be inserted into. Please mind that arrays start at 0
	*/
	return array_slice( $array, 0, $position, true ) + $insert + array_slice( $array, $position, null, true );
}

/**
 * Get the count of posts that have the given terms and post types
 * in the given taxonomy.
 *
 * @param array  $term_ids
 * @param array  $post_types
 * @param string $taxonomy
 *
 * @return int
 */
function skaflow_get_post_count_for_terms_and_post_types( array $term_ids, array $post_types, string $taxonomy ): int {
	global $wpdb;

	$term_ids   = array_map( 'absint', array_filter( $term_ids ) );
	$post_types = array_filter( $post_types, 'sanitize_key' );

	if ( empty( $term_ids ) || empty( $post_types ) ) {
		return 0;
	}

	$term_placeholders = implode( ', ', array_fill( 0, count( $term_ids ), '%d' ) );
	$pt_placeholders   = implode( ', ', array_fill( 0, count( $post_types ), '%s' ) );

	$sql = "
        SELECT COUNT(*)
        FROM $wpdb->posts p
        INNER JOIN $wpdb->term_relationships tr ON p.ID = tr.object_id
        INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
        WHERE tt.term_id IN ($term_placeholders)
        AND tt.taxonomy = %s
        AND p.post_type IN ($pt_placeholders)
        AND p.post_status = 'publish'
    ";

	$params = array_merge( $term_ids, array( $taxonomy ), $post_types );

	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- $sql is a prepare() template; placeholders and $params come from absint/sanitize_key.
	$count = (int) $wpdb->get_var( $wpdb->prepare( $sql, $params ) );

	return $count;
}


/**
 * Sanitize a space-separated HTML class list.
 *
 * @param string $classes Class names.
 * @return string
 */
function skaflow_sanitize_html_classes( string $classes ): string {
	$out = array();

	foreach ( preg_split( '/\s+/', trim( $classes ) ) as $class_name ) {
		if ( '' !== $class_name ) {
			$out[] = sanitize_html_class( $class_name );
		}
	}

	return implode( ' ', array_unique( $out ) );
}

/**
 * Lucide icon class names for programmatic core/button blocks.
 *
 * Mirrors src/blocks-extension/extend-core-button/button-icons.js getIconClassNames().
 *
 * @param string $icon_slug     Lucide slug (e.g. shopping-cart).
 * @param string $icon_position left|right.
 * @return string
 */
function skaflow_get_button_icon_class_names( string $icon_slug, string $icon_position = 'left' ): string {

	if ( '' === trim( $icon_slug ) ) {
		return '';
	}

	$icon_slug      = sanitize_html_class( $icon_slug );
	$position_class = 'right' === $icon_position ? 'sk-icon-right' : 'sk-icon-left';

	return "sk-has-icon {$position_class} sk-icon-{$icon_slug}";
}

/**
 * Build an HTML attribute string from an associative array.
 *
 * @param array<string, scalar|null> $attributes Attribute names and values.
 * @return string
 */
function skaflow_build_html_attributes_string( array $attributes ): string {
	$parts = array();

	foreach ( $attributes as $name => $value ) {
		if ( null === $value || false === $value ) {
			continue;
		}

		$name = preg_replace( '/[^a-z0-9_\-]/i', '', (string) $name );

		if ( '' === $name ) {
			continue;
		}

		$parts[] = sprintf( '%s="%s"', esc_attr( $name ), esc_attr( (string) $value ) );
	}

	return implode( ' ', $parts );
}

/**
 * Render a buttons group via core/buttons + core/button (do_blocks).
 *
 * @param array<int, array<string, mixed>> $buttons Each item: [
 *   'label'           => (string) Button text (required),
 *   'url'             => (string) HREF (required when tag is "a"),
 *   'external'        => (bool)   Open in new tab (adds target and rel),
 *   'class'           => (string) Extra classes for the .wp-block-button wrapper,
 *   'element_class'   => (string) Extra classes for the a/button element,
 *   'style'           => (string) Block style class (e.g. is-style-ghost),
 *   'icon'            => (string) Lucide icon slug (optional),
 *   'icon_position'   => (string) left|right (optional, default left),
 *   'tag'             => (string) a|button (optional, default a),
 *   'html_attributes' => (array) Extra attributes for the a/button element,
 * ]
 * @param array<string, mixed>             $args Optional: [
 *   'wrapper_class' => (string) Extra classes for the buttons container,
 *   'default_style' => (string) Default style class when button style omitted,
 *   'default_icon'  => (string) Default Lucide icon slug,
 *   'layout'        => (array)  core/buttons layout attribute (e.g. flex + justifyContent),
 * ]
 *
 * @return string Rendered buttons markup.
 */
function skaflow_get_buttons_markup( array $buttons, array $args = array() ): string {

	if ( empty( $buttons ) || ! function_exists( 'do_blocks' ) ) {
		return '';
	}

	$wrapper_extra = skaflow_sanitize_html_classes( (string) ( $args['wrapper_class'] ?? '' ) );
	$default_style = skaflow_sanitize_html_classes( (string) ( $args['default_style'] ?? 'is-style-primary' ) );
	$layout        = isset( $args['layout'] ) && is_array( $args['layout'] ) ? $args['layout'] : null;

	$buttons_attrs = array();

	if ( null !== $layout ) {
		$buttons_attrs['layout'] = $layout;
	}

	if ( '' !== $wrapper_extra ) {
		$buttons_attrs['className'] = $wrapper_extra;
	}

	$buttons_json = ! empty( $buttons_attrs )
		? wp_json_encode( $buttons_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		: '';

	ob_start();

	if ( '' !== $buttons_json ) {
		printf( '<!-- wp:buttons %s -->', $buttons_json ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_json_encode() block comment.
	} else {
		echo '<!-- wp:buttons -->';
	}

	$buttons_wrapper_classes = skaflow_sanitize_html_classes(
		trim( 'wp-block-buttons ' . $wrapper_extra )
	);

	printf(
		'<div class="%s">',
		esc_attr( $buttons_wrapper_classes )
	);

	foreach ( $buttons as $btn ) {
		$label    = isset( $btn['label'] ) ? (string) $btn['label'] : '';
		$url      = isset( $btn['url'] ) ? (string) $btn['url'] : '';
		$tag_name = isset( $btn['tag'] ) && 'button' === $btn['tag'] ? 'button' : 'a';

		if ( '' === $label ) {
			continue;
		}

		if ( 'a' === $tag_name && '' === $url ) {
			continue;
		}

		$external        = ! empty( $btn['external'] );
		$wrapper_class   = skaflow_sanitize_html_classes( (string) ( $btn['class'] ?? '' ) );
		$style_class     = skaflow_sanitize_html_classes( (string) ( $btn['style'] ?? $default_style ) );
		$element_class   = skaflow_sanitize_html_classes( (string) ( $btn['element_class'] ?? '' ) );
		$icon_slug       = isset( $btn['icon'] ) ? sanitize_key( (string) $btn['icon'] ) : sanitize_key( (string) ( $args['default_icon'] ?? '' ) );
		$icon_position   = isset( $btn['icon_position'] ) && 'right' === $btn['icon_position'] ? 'right' : 'left';
		$icon_classes    = skaflow_get_button_icon_class_names( $icon_slug, $icon_position );
		$wrapper_classes = skaflow_sanitize_html_classes(
			trim( implode( ' ', array_filter( array( $style_class, $wrapper_class, $icon_classes ) ) ) )
		);

		$block_attrs = array(
			'tagName' => $tag_name,
		);

		if ( '' !== $wrapper_classes ) {
			$block_attrs['className'] = $wrapper_classes;
		}

		if ( '' !== $icon_slug ) {
			$block_attrs['skaflowIcon'] = $icon_slug;

			if ( 'right' === $icon_position ) {
				$block_attrs['skaflowIconPosition'] = 'right';
			}
		}

		$element_attrs = array(
			'class' => skaflow_sanitize_html_classes(
				trim( 'wp-block-button__link wp-element-button ' . $element_class )
			),
		);

		if ( 'button' === $tag_name ) {
			$element_attrs['type'] = 'button';
		} else {
			$element_attrs['href'] = $url;

			if ( $external ) {
				$element_attrs['target'] = '_blank';
				$element_attrs['rel']    = 'noopener noreferrer';
			}
		}

		if ( ! empty( $btn['html_attributes'] ) && is_array( $btn['html_attributes'] ) ) {
			foreach ( $btn['html_attributes'] as $attr_name => $attr_value ) {
				if ( null === $attr_value || false === $attr_value ) {
					continue;
				}

				if ( 'class' === $attr_name ) {
					$element_attrs['class'] = skaflow_sanitize_html_classes(
						trim( $element_attrs['class'] . ' ' . (string) $attr_value )
					);
					continue;
				}

				$element_attrs[ (string) $attr_name ] = $attr_value;
			}
		}

		$block_json = wp_json_encode( $block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- block comment is wp_json_encode(); tag is a|button; attributes escaped in helper.
		printf(
			'<!-- wp:button %1$s -->
<div class="wp-block-button %2$s">
	<%3$s %4$s>%5$s</%3$s>
</div>
<!-- /wp:button -->',
			$block_json,
			esc_attr( $wrapper_classes ),
			$tag_name,
			skaflow_build_html_attributes_string( $element_attrs ),
			wp_kses_post( $label )
		);
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	echo '</div><!-- /wp:buttons -->';

	return do_blocks( ob_get_clean() );
}

/**
 * Persist the print-page toggle onto a saved core/button.
 *
 * The editor writes `data-sk-print-order` via extraProps; this covers
 * markup that has the attribute in JSON but not yet on the <a>.
 *
 * @param string $content Rendered button HTML.
 * @param array  $block   Parsed block.
 * @return string
 */
function skaflow_render_print_button( string $content, array $block ): string {
	if ( '' === $content || empty( $block['attrs']['skaflowPrint'] ) ) {
		return $content;
	}

	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $content;
	}

	$processor = new WP_HTML_Tag_Processor( $content );

	if ( ! $processor->next_tag( 'a' ) ) {
		return $content;
	}

	$processor->set_attribute( 'data-sk-print-order', '1' );

	if ( ! $processor->get_attribute( 'href' ) ) {
		$processor->set_attribute( 'href', '#' );
	}

	return $processor->get_updated_html();
}

add_filter( 'render_block_core/button', 'skaflow_render_print_button', 10, 2 );


/**
 * Trim a file‑name to keep the list tidy.
 *
 * @param string $name Original file name.
 * @param int    $limit Max visible characters (inclusive of the ellipsis).
 *
 * @return string
 */
function skaflow_trim_filename( string $name, int $limit = 40 ): string {
	if ( mb_strlen( $name ) <= $limit ) {
		return $name;
	}

	return mb_substr( $name, 0, $limit - 1 ) . '…';
}

/**
 * Render the PHP site header (announcement, search, nav shell).
 *
 * Frontend: skaflow/site-header block render.php (parts/header.html).
 *
 * @return void
 */
function skaflow_render_site_header(): void {

	Skaflow\Site_Header::render();
}
