<?php
/**
 * Taxonomy Terms block helpers.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post ID for the current block render (Query Loop context, main query, or editor preview).
 *
 * @param \WP_Block|null $block Block instance.
 * @return int
 */
function happywood_get_taxonomy_terms_post_id( $block = null ): int {
	if ( $block instanceof \WP_Block && ! empty( $block->context['postId'] ) ) {
		return absint( $block->context['postId'] );
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST && ! empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( wp_unslash( $_GET['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$post_id = get_the_ID();

	return $post_id ? absint( $post_id ) : 0;
}

/**
 * Primary term ID from Yoast SEO or Rank Math, when set.
 *
 * @param string $taxonomy_slug Taxonomy slug.
 * @param int    $post_id       Post ID.
 * @return int
 */
function happywood_get_primary_term_id_for_post( string $taxonomy_slug, int $post_id ): int {
	if ( function_exists( 'yoast_get_primary_term_id' ) ) {
		$term_id = (int) yoast_get_primary_term_id( $taxonomy_slug, $post_id );

		if ( $term_id > 0 ) {
			return $term_id;
		}
	}

	$rank_math_term_id = (int) get_post_meta( $post_id, 'rank_math_primary_' . $taxonomy_slug, true );

	return $rank_math_term_id > 0 ? $rank_math_term_id : 0;
}

/**
 * Assigned terms for a post, with optional limit.
 *
 * No limit (blank / 0): all assigned terms.
 * With limit: Yoast or Rank Math primary first, then remaining assigned terms.
 *
 * @param string         $taxonomy_slug Taxonomy slug.
 * @param int            $limit         Max terms; 0 = all assigned terms.
 * @param \WP_Block|null $block         Block instance.
 * @return \WP_Term[]
 */
function happywood_get_taxonomy_terms_for_post_limited( string $taxonomy_slug, int $limit = 0, $block = null ): array {
	$post_id = happywood_get_taxonomy_terms_post_id( $block );

	if ( ! $post_id || ! taxonomy_exists( $taxonomy_slug ) ) {
		return array();
	}

	$assigned = wp_get_post_terms(
		$post_id,
		$taxonomy_slug,
		array(
			'orderby' => 'term_order',
		)
	);

	if ( empty( $assigned ) || is_wp_error( $assigned ) ) {
		return array();
	}

	$limit = absint( $limit );

	if ( $limit <= 0 ) {
		return $assigned;
	}

	$terms = array();
	$seen  = array();

	$primary_id = happywood_get_primary_term_id_for_post( $taxonomy_slug, $post_id );

	if ( $primary_id > 0 ) {
		$primary = get_term( $primary_id, $taxonomy_slug );

		if ( $primary instanceof \WP_Term && ! is_wp_error( $primary ) ) {
			$terms[]                   = $primary;
			$seen[ $primary->term_id ] = true;
		}
	}

	foreach ( $assigned as $term ) {
		if ( count( $terms ) >= $limit ) {
			break;
		}

		if ( ! $term instanceof \WP_Term || isset( $seen[ $term->term_id ] ) ) {
			continue;
		}

		$terms[] = $term;
	}

	return $terms;
}

/**
 * Markup for one taxonomy term pill.
 *
 * @param \WP_Term $term    Term object.
 * @param bool     $is_link Whether to link publicly viewable terms.
 * @return string
 */
function happywood_get_taxonomy_term_pill_markup( \WP_Term $term, bool $is_link = false ): string {
	$classes = implode(
		' ',
		array(
			'hw-taxonomy-terms__term',
			'taxonomy-' . sanitize_html_class( $term->taxonomy ),
			sanitize_html_class( $term->slug ),
		)
	);
	$label   = esc_html( $term->name );

	if ( $is_link && is_term_publicly_viewable( $term ) ) {
		$term_link = get_term_link( $term );

		if ( ! is_wp_error( $term_link ) ) {
			return sprintf(
				'<a class="%1$s" href="%2$s">%3$s</a>',
				esc_attr( $classes ),
				esc_url( $term_link ),
				$label
			);
		}
	}

	return sprintf(
		'<span class="%1$s">%2$s</span>',
		esc_attr( $classes ),
		$label
	);
}

/**
 * Render the happywood/taxonomy-terms block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param \WP_Block|null       $block      Block instance.
 * @return string
 */
function happywood_render_taxonomy_terms( array $attributes, $block = null ): string {
	$attributes = wp_parse_args(
		$attributes,
		array(
			'taxonomy' => 'category',
			'limit'    => 0,
			'isLink'   => false,
		)
	);

	$taxonomy = sanitize_key( (string) $attributes['taxonomy'] );

	if ( '' === $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
		return '';
	}

	$limit   = isset( $attributes['limit'] ) ? absint( $attributes['limit'] ) : 0;
	$is_link = ! empty( $attributes['isLink'] );
	$terms   = happywood_get_taxonomy_terms_for_post_limited( $taxonomy, $limit, $block );

	if ( empty( $terms ) ) {
		return '';
	}

	$wrapper_classes = 'hw-taxonomy-terms taxonomy-' . sanitize_html_class( $taxonomy );

	if ( $block instanceof \WP_Block ) {
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $wrapper_classes,
			),
			$block
		);
	} else {
		$wrapper_attributes = sprintf(
			'class="%s wp-block-happywood-taxonomy-terms"',
			esc_attr( $wrapper_classes )
		);
	}

	$term_markup = implode(
		'',
		array_filter(
			array_map(
				static function ( $term ) use ( $is_link ) {
					return $term instanceof \WP_Term
						? happywood_get_taxonomy_term_pill_markup( $term, $is_link )
						: '';
				},
				$terms
			)
		)
	);

	if ( '' === $term_markup ) {
		return '';
	}

	return sprintf(
		'<div %1$s>%2$s</div>',
		$wrapper_attributes,
		$term_markup
	);
}
