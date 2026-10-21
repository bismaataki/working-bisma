<?php
/**
 * Turn priced Size choices from options.json into WooCommerce variations.
 *
 * Color and base color stay visible attributes. They do not get variations,
 * so choosing them does not change the price. A select group counts as Size
 * when its id or label contains "size" (including mattress size and shelf
 * size) and does not contain "color".
 *
 *   ddev wp eval-file context/demo-content/import-variations.php --user=admin
 *
 * Safe to re-run. Variations match the Size term slug.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	return;
}

if ( ! class_exists( 'WC_Product_Variable' ) || ! class_exists( 'WC_Product_Variation' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

/**
 * Decode text stored with HTML entities.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function hw_demo_var_text( $value ): string {
	return trim( html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Whole-rupee amount, or an empty string when the source has no positive price.
 *
 * @param mixed $value Raw price.
 * @return string
 */
function hw_demo_var_money( $value ): string {
	if ( ! is_numeric( $value ) ) {
		return '';
	}

	$amount = (float) $value;
	if ( $amount <= 0 ) {
		return '';
	}

	return (string) (int) round( $amount );
}

/**
 * Whether this option group is the Size selector.
 *
 * @param array<string, mixed> $group Option group.
 * @return bool
 */
function hw_demo_var_is_size_group( array $group ): bool {
	if ( 'select' !== ( $group['type'] ?? '' ) ) {
		return false;
	}

	$haystack = strtolower( (string) ( $group['id'] ?? '' ) . ' ' . (string) ( $group['label'] ?? '' ) );
	if ( ! str_contains( $haystack, 'size' ) ) {
		return false;
	}

	return ! str_contains( $haystack, 'color' ) && ! str_contains( $haystack, 'colour' );
}

/**
 * Priced size choices. One choice cannot change the price, so it stays simple.
 *
 * @param array<string, mixed> $product Option payload for one product.
 * @return array<int, array<string, mixed>>
 */
function hw_demo_var_size_values( array $product ): array {
	$values = array();

	foreach ( (array) ( $product['groups'] ?? array() ) as $group ) {
		if ( ! is_array( $group ) || ! hw_demo_var_is_size_group( $group ) ) {
			continue;
		}

		foreach ( (array) ( $group['values'] ?? array() ) as $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}
			$regular = hw_demo_var_money( $value['price'] ?? '' );
			$sale    = hw_demo_var_money( $value['sale_price'] ?? '' );
			if ( '' === $regular && '' === $sale ) {
				continue;
			}
			$value['regular'] = '' !== $regular ? $regular : $sale;
			$value['sale']    = ( '' !== $sale && '' !== $regular && (float) $sale < (float) $regular ) ? $sale : '';
			$values[]         = $value;
		}
	}

	return count( $values ) >= 2 ? $values : array();
}

/**
 * Global Size attribute id. Created when the catalog import did not add it.
 *
 * @return int
 */
function hw_demo_var_size_attribute_id(): int {
	$attribute_id = (int) wc_attribute_taxonomy_id_by_name( 'size' );
	if ( $attribute_id ) {
		return $attribute_id;
	}

	$created = wc_create_attribute(
		array(
			'name'         => 'Size',
			'slug'         => 'size',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		)
	);

	if ( is_wp_error( $created ) ) {
		WP_CLI::warning( $created->get_error_message() );
		return 0;
	}

	$taxonomy = wc_attribute_taxonomy_name( 'size' );
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy(
			$taxonomy,
			array( 'product' ),
			array(
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => false,
			)
		);
	}

	return (int) $created;
}

/**
 * Term for one size label.
 *
 * @param string $label     Size label, e.g. `22" x 36"`.
 * @param string $option_id Source option id, used only when the label slug is taken.
 * @return array{id: int, slug: string}
 */
function hw_demo_var_size_term( string $label, string $option_id ): array {
	$label   = hw_demo_var_text( $label );
	$primary = sanitize_title( $label );
	if ( '' === $primary ) {
		$primary = sanitize_title( $option_id );
	}

	$slugs = array( $primary );
	if ( '' !== $option_id ) {
		$slugs[] = sanitize_title( $primary . '-' . $option_id );
	}

	foreach ( $slugs as $slug ) {
		$term = get_term_by( 'slug', $slug, 'pa_size' );
		if ( $term instanceof WP_Term && $term->name === $label ) {
			return array(
				'id'   => (int) $term->term_id,
				'slug' => $term->slug,
			);
		}
	}

	foreach ( $slugs as $slug ) {
		if ( get_term_by( 'slug', $slug, 'pa_size' ) ) {
			continue;
		}

		$inserted = wp_insert_term(
			$label,
			'pa_size',
			array(
				'slug' => $slug,
			)
		);
		if ( is_wp_error( $inserted ) ) {
			WP_CLI::warning( $inserted->get_error_message() . ' ' . $label );
			return array(
				'id'   => 0,
				'slug' => '',
			);
		}

		$term = get_term( (int) $inserted['term_id'], 'pa_size' );
		return array(
			'id'   => (int) $inserted['term_id'],
			'slug' => $term instanceof WP_Term ? $term->slug : $slug,
		);
	}

	return array(
		'id'   => 0,
		'slug' => '',
	);
}

/**
 * Local product imported from this Openwood id.
 *
 * @param int $source_id Openwood product id.
 * @return int
 */
function hw_demo_var_find_product_id( int $source_id ): int {
	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_openwood_product_id',
			'meta_value'     => (string) $source_id,
			'no_found_rows'  => true,
		)
	);

	return $ids ? (int) $ids[0] : 0;
}

$payload = json_decode( (string) file_get_contents( __DIR__ . '/options.json' ), true );
$catalog = is_array( $payload ) ? ( $payload['products'] ?? null ) : null;
if ( ! is_array( $catalog ) ) {
	WP_CLI::error( 'options.json is missing or has no products.' );
}

$attribute_id = hw_demo_var_size_attribute_id();
if ( ! $attribute_id ) {
	WP_CLI::error( 'Could not register the Size attribute.' );
}

wp_defer_term_counting( true );

$converted = 0;
$skipped   = 0;

foreach ( $catalog as $source_id => $raw ) {
	if ( ! is_array( $raw ) ) {
		continue;
	}

	$values = hw_demo_var_size_values( $raw );
	if ( ! $values ) {
		++$skipped;
		continue;
	}

	$product_id = hw_demo_var_find_product_id( (int) $source_id );
	if ( ! $product_id ) {
		WP_CLI::warning( 'No local product for Openwood id ' . $source_id );
		continue;
	}

	$terms = array();
	foreach ( $values as $value ) {
		$term = hw_demo_var_size_term(
			(string) ( $value['label'] ?? '' ),
			(string) ( $value['id'] ?? '' )
		);
		if ( ! $term['id'] || '' === $term['slug'] ) {
			continue;
		}
		$value['term_id'] = $term['id'];
		$value['slug']    = $term['slug'];
		$terms[]          = $value;
	}

	if ( count( $terms ) < 2 ) {
		WP_CLI::warning( hw_demo_var_text( $raw['name'] ?? $source_id ) . ' has fewer than two Size terms.' );
		continue;
	}

	$product = new WC_Product_Variable( $product_id );
	$kept    = array();
	foreach ( $product->get_attributes() as $attribute ) {
		if ( ! $attribute instanceof WC_Product_Attribute || 'pa_size' === $attribute->get_name() ) {
			continue;
		}
		$attribute->set_variation( false );
		$attribute->set_visible( true );
		$kept[] = $attribute;
	}

	$size_attribute = new WC_Product_Attribute();
	$size_attribute->set_id( $attribute_id );
	$size_attribute->set_name( 'pa_size' );
	$size_attribute->set_options( array_map( 'intval', wp_list_pluck( $terms, 'term_id' ) ) );
	$size_attribute->set_position( 0 );
	$size_attribute->set_visible( true );
	$size_attribute->set_variation( true );

	$position = 1;
	foreach ( $kept as $attribute ) {
		$attribute->set_position( $position );
		++$position;
	}

	$base    = hw_demo_var_money( $raw['basePrice'] ?? '' );
	$default = $terms[0]['slug'];
	foreach ( $terms as $term ) {
		$shown = '' !== $term['sale'] ? $term['sale'] : $term['regular'];
		if ( '' !== $base && $shown === $base ) {
			$default = $term['slug'];
			break;
		}
	}

	$product->set_attributes( array_merge( array( $size_attribute ), $kept ) );
	$product->set_default_attributes( array( 'pa_size' => $default ) );
	$product->update_meta_data( '_openwood_size_variations', 'yes' );
	$product->save();

	$existing = array();
	foreach ( $product->get_children() as $child_id ) {
		$child = wc_get_product( (int) $child_id );
		if ( ! $child instanceof WC_Product_Variation ) {
			continue;
		}
		$child_attributes = $child->get_attributes();
		$slug             = (string) ( $child_attributes['pa_size'] ?? '' );
		if ( '' !== $slug ) {
			$existing[ $slug ] = $child;
		}
	}

	$wanted = array();
	foreach ( $terms as $index => $term ) {
		$wanted[ $term['slug'] ] = true;
		$variation               = $existing[ $term['slug'] ] ?? new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_attributes( array( 'pa_size' => $term['slug'] ) );
		$variation->set_regular_price( $term['regular'] );
		$variation->set_sale_price( $term['sale'] );
		$variation->set_status( 'publish' );
		$variation->set_manage_stock( false );
		$variation->set_stock_status( ! empty( $term['oos'] ) ? 'outofstock' : 'instock' );
		$variation->set_menu_order( (int) $index );
		$variation->update_meta_data( '_openwood_size_option', (string) ( $term['id'] ?? '' ) );
		$variation->save();
	}

	foreach ( $existing as $slug => $child ) {
		if ( ! isset( $wanted[ $slug ] ) ) {
			$child->delete( true );
		}
	}

	WC_Product_Variable::sync( $product_id );
	wc_delete_product_transients( $product_id );
	++$converted;

	WP_CLI::log(
		sprintf(
			'%s: %d sizes, default %s',
			hw_demo_var_text( $raw['name'] ?? '' ),
			count( $terms ),
			$default
		)
	);
}

wp_defer_term_counting( false );

if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
	wc_update_product_lookup_tables();
}

WP_CLI::success( sprintf( 'Size variations on %d products. %d left simple.', $converted, $skipped ) );
