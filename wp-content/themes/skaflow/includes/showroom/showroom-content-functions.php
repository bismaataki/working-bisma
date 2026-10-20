<?php
/**
 * Showroom gallery pattern and empty post-content fallback.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build core/gallery block markup from showroom_gallery attachment IDs.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return string
 */
function skaflow_get_showroom_gallery_pattern_markup( int $post_id ): string {
	if ( $post_id <= 0 ) {
		return '';
	}

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( 'showroom_gallery', $post_id );
	} else {
		$value = get_post_meta( $post_id, 'showroom_gallery', true );
	}

	if ( is_array( $value ) ) {
		$attachment_ids = array_values( array_filter( array_map( 'absint', $value ) ) );
	} elseif ( is_numeric( $value ) ) {
		$attachment_ids = absint( $value ) > 0 ? array( absint( $value ) ) : array();
	} else {
		$attachment_ids = array();
	}

	$image_blocks = array();

	foreach ( $attachment_ids as $attachment_id ) {
		$url = wp_get_attachment_image_url( $attachment_id, 'large' );

		if ( ! $url ) {
			continue;
		}

		$alt         = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		$block_attrs = wp_json_encode(
			array(
				'lightbox'        => array(
					'enabled' => true,
				),
				'id'              => $attachment_id,
				'sizeSlug'        => 'large',
				'linkDestination' => 'none',
			),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		);

		$image_blocks[] = sprintf(
			"<!-- wp:image %1\$s -->\n<figure class=\"wp-block-image size-large\"><img src=\"%2\$s\" alt=\"%3\$s\" class=\"wp-image-%4\$d\"/></figure>\n<!-- /wp:image -->",
			$block_attrs,
			esc_url( $url ),
			esc_attr( $alt ),
			$attachment_id
		);
	}

	$gallery_attrs = wp_json_encode(
		array(
			'linkTo' => 'lightbox',
			'align'  => 'wide',
		),
		JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
	);

	$inner_markup = implode( "\n\n", $image_blocks );

	return sprintf(
		"<!-- wp:gallery %1\$s -->\n<figure class=\"wp-block-gallery alignwide has-nested-images columns-default is-cropped\">%2\$s</figure>\n<!-- /wp:gallery -->",
		$gallery_attrs,
		$inner_markup ? "\n" . $inner_markup . "\n" : ''
	);
}

/**
 * Run a callback with a specific post set as the current loop post.
 *
 * @param WP_Post  $post     Post to expose to get_the_ID(), get_field(), etc.
 * @param callable $callback Callback to run while post context is active.
 *
 * @return mixed Callback return value.
 */
function skaflow_with_post_context( WP_Post $post, callable $callback ) {
	$previous_post = $GLOBALS['post'] ?? null;

    // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['post'] = $post;
	setup_postdata( $post );

	try {
		return $callback();
	} finally {
		if ( $previous_post instanceof WP_Post ) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$GLOBALS['post'] = $previous_post;
			setup_postdata( $previous_post );
		} else {
			wp_reset_postdata();
		}
	}
}

/**
 * Include a theme pattern PHP file and capture its output.
 *
 * Caller must set post context first when the pattern uses get_the_ID().
 *
 * @param string $pattern_file Absolute path to a pattern PHP file.
 *
 * @return string
 */
function skaflow_get_pattern_file_markup( string $pattern_file ): string {
	if ( ! is_readable( $pattern_file ) ) {
		return '';
	}

	ob_start();
	include $pattern_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingCustomFunction

	return trim( (string) ob_get_clean() );
}

/**
 * Replace nested wp:pattern blocks that need the current showroom post.
 *
 * @param string  $markup Block markup from single-showrooms-default.
 * @param WP_Post $post   Current showroom post.
 *
 * @return string
 */
function skaflow_expand_showroom_default_pattern_blocks( string $markup, WP_Post $post ): string {
	$pattern_block = '<!-- wp:pattern {"slug":"skaflow/showroom-gallery"} /-->';

	if ( ! str_contains( $markup, $pattern_block ) ) {
		return $markup;
	}

	$gallery_markup = skaflow_get_pattern_file_markup(
		get_theme_file_path( 'patterns/showroom-gallery.php' )
	);

	if ( '' === trim( $gallery_markup ) ) {
		$gallery_markup = skaflow_get_showroom_gallery_pattern_markup( (int) $post->ID );
	}

	return str_replace( $pattern_block, $gallery_markup, $markup );
}

/**
 * Load a theme pattern PHP file with the correct post context.
 *
 * @param string  $pattern_file Absolute path to a pattern PHP file.
 * @param WP_Post $post         Post to set as the current post while loading.
 *
 * @return string
 */
function skaflow_get_pattern_markup_for_post( string $pattern_file, WP_Post $post ): string {
	return (string) skaflow_with_post_context(
		$post,
		static function () use ( $pattern_file ): string {
			return skaflow_get_pattern_file_markup( $pattern_file );
		}
	);
}

/**
 * Default post-content markup for showroom singles (used by BatchPress populate job).
 *
 * @param int $post_id Showroom post ID.
 *
 * @return string
 */
function skaflow_get_showroom_single_default_post_content( int $post_id ): string {
	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post ) {
		return '';
	}

	$build_markup = static function () use ( $post, $post_id ): string {
		$markup = skaflow_get_pattern_file_markup(
			get_theme_file_path( 'patterns/single-showrooms-default.php' )
		);

		$markup = skaflow_expand_showroom_default_pattern_blocks( $markup, $post );

		return $markup;
	};

	if ( get_the_ID() === $post_id ) {
		return $build_markup();
	}

	return (string) skaflow_with_post_context( $post, $build_markup );
}

/**
 * Whether a showroom post has no editor-authored content yet.
 *
 * @param string $post_content Raw post_content value.
 *
 * @return bool
 */
function skaflow_is_showroom_post_content_empty( string $post_content ): bool {
	$post_content = trim( $post_content );

	if ( '' === $post_content ) {
		return true;
	}

	if ( ! has_blocks( $post_content ) ) {
		return '' === trim( wp_strip_all_tags( $post_content ) );
	}

	foreach ( parse_blocks( $post_content ) as $block ) {
		if ( ! empty( $block['blockName'] ) ) {
			return false;
		}

		if ( '' !== trim( (string) ( $block['innerHTML'] ?? '' ) ) ) {
			return false;
		}
	}

	return true;
}

/**
 * Render the default showroom pattern when post-content is empty.
 *
 * @param string   $block_content Rendered block HTML.
 * @param array    $block         Parsed block data.
 * @param WP_Block $instance      Block instance.
 *
 * @return string
 */
function skaflow_render_showroom_post_content_fallback( string $block_content, array $block, WP_Block $instance ): string {
	if ( 'core/post-content' !== ( $block['blockName'] ?? '' ) ) {
		return $block_content;
	}

	$post_id = (int) ( $instance->context['postId'] ?? get_the_ID() );

	if ( $post_id <= 0 || 'showrooms' !== get_post_type( $post_id ) ) {
		return $block_content;
	}

	$post = get_post( $post_id );

	if ( ! $post instanceof WP_Post || ! skaflow_is_showroom_post_content_empty( (string) $post->post_content ) ) {
		return $block_content;
	}

	if ( '' !== trim( $block_content ) ) {
		return $block_content;
	}

	$default_markup = skaflow_get_showroom_single_default_post_content( $post_id );

	if ( '' === trim( $default_markup ) ) {
		return $block_content;
	}

	return do_shortcode( do_blocks( $default_markup ) );
}

add_filter( 'render_block', 'skaflow_render_showroom_post_content_fallback', 10, 3 );
