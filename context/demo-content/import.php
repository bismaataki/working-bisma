<?php
/**
 * Import the Openwood demo catalog saved beside this file.
 *
 * Source, fetched 2026-10-21:
 *   https://openwood.pk/wp-json/wc/store/v1/products?per_page=100
 *   https://openwood.pk/wp-json/wc/store/v1/products/categories?per_page=100
 *
 *   ddev wp eval-file context/demo-content/import.php --user=admin
 *
 * Safe to re-run. Products match `_openwood_product_id`, categories match
 * slug, and attachments match `_openwood_source_url`.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
	return;
}

if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Simple' ) ) {
	WP_CLI::error( 'WooCommerce is not active.' );
}

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

/**
 * Decode text the Store API returns with HTML entities still escaped.
 *
 * @param mixed $value Raw API value.
 * @return string
 */
function hw_demo_text( $value ): string {
	return trim( html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) );
}

/**
 * Local image cache written before this script runs, keyed by source URL.
 *
 * @return array<string, string>
 */
function hw_demo_image_manifest(): array {
	$path = '/tmp/openwood-demo-images/manifest.json';
	if ( ! is_readable( $path ) ) {
		return array();
	}

	$decoded = json_decode( (string) file_get_contents( $path ), true );

	return is_array( $decoded ) ? $decoded : array();
}

/**
 * Attachment already imported from this source URL.
 *
 * @param string $url Source image URL.
 * @return int
 */
function hw_demo_find_attachment( string $url ): int {
	$ids = get_posts(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_key'       => '_openwood_source_url',
			'meta_value'     => $url,
			'no_found_rows'  => true,
		)
	);

	return $ids ? (int) $ids[0] : 0;
}

/**
 * Sideload one image, preferring the local cache.
 *
 * @param string $url     Source image URL.
 * @param int    $post_id Parent post, or 0.
 * @param string $alt     Alt text.
 * @return int Attachment ID, or 0 on failure.
 */
function hw_demo_attachment_from_url( string $url, int $post_id, string $alt ): int {
	$existing = hw_demo_find_attachment( $url );
	if ( $existing ) {
		return $existing;
	}

	$path     = (string) wp_parse_url( $url, PHP_URL_PATH );
	$filename = sanitize_file_name( rawurldecode( $path ? basename( $path ) : 'image.jpg' ) );
	if ( '' === $filename ) {
		$filename = 'image.jpg';
	}

	$manifest = hw_demo_image_manifest();
	$local    = $manifest[ $url ] ?? '';
	$tmp      = '';

	if ( is_string( $local ) && is_readable( $local ) ) {
		$tmp = wp_tempnam( $filename );
		if ( ! $tmp || ! copy( $local, $tmp ) ) {
			WP_CLI::warning( 'Could not stage cached image ' . $url );
			return 0;
		}
	} else {
		$tmp = download_url( $url, 60 );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( $tmp->get_error_message() . ' ' . $url );
			return 0;
		}
	}

	$attachment_id = media_handle_sideload(
		array(
			'name'     => $filename,
			'tmp_name' => $tmp,
		),
		$post_id,
		$alt
	);

	if ( is_wp_error( $attachment_id ) ) {
		if ( is_string( $tmp ) && file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		WP_CLI::warning( $attachment_id->get_error_message() . ' ' . $url );
		return 0;
	}

	update_post_meta( (int) $attachment_id, '_openwood_source_url', $url );
	if ( '' !== $alt ) {
		update_post_meta( (int) $attachment_id, '_wp_attachment_image_alt', $alt );
	}

	return (int) $attachment_id;
}

/**
 * Create a global attribute and register its taxonomy for this request.
 *
 * @param string $taxonomy Source taxonomy, e.g. `pa_color`.
 * @param string $label    Attribute label.
 * @return array{0: int, 1: string} Attribute ID and taxonomy name.
 */
function hw_demo_ensure_attribute( string $taxonomy, string $label ): array {
	global $wc_product_attributes;

	$slug          = (string) preg_replace( '/^pa_/', '', $taxonomy );
	$taxonomy_name = wc_attribute_taxonomy_name( $slug );
	$attribute_id  = (int) wc_attribute_taxonomy_id_by_name( $slug );

	if ( ! $attribute_id ) {
		$created = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);

		if ( is_wp_error( $created ) ) {
			WP_CLI::warning( $created->get_error_message() );
			return array( 0, '' );
		}

		$attribute_id = (int) $created;
	}

	if ( ! taxonomy_exists( $taxonomy_name ) ) {
		register_taxonomy(
			$taxonomy_name,
			apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy_name, array( 'product' ) ),
			apply_filters(
				'woocommerce_taxonomy_args_' . $taxonomy_name,
				array(
					'labels'       => array(
						'name' => $label,
					),
					'hierarchical' => true,
					'show_ui'      => false,
					'query_var'    => true,
					'rewrite'      => false,
				)
			)
		);
	}

	$wc_product_attributes = array();
	foreach ( wc_get_attribute_taxonomies() as $tax ) {
		$wc_product_attributes[ wc_attribute_taxonomy_name( $tax->attribute_name ) ] = $tax;
	}

	return array( $attribute_id, $taxonomy_name );
}

/**
 * Term ID for an attribute value, created when missing.
 *
 * @param string $taxonomy Taxonomy name.
 * @param string $name     Term name.
 * @param string $slug     Term slug.
 * @return int
 */
function hw_demo_ensure_term( string $taxonomy, string $name, string $slug ): int {
	$term = get_term_by( 'slug', $slug, $taxonomy );
	if ( $term instanceof WP_Term ) {
		if ( $term->name !== $name ) {
			wp_update_term( (int) $term->term_id, $taxonomy, array( 'name' => $name ) );
		}
		return (int) $term->term_id;
	}

	$inserted = wp_insert_term(
		$name,
		$taxonomy,
		array(
			'slug' => $slug,
		)
	);

	if ( is_wp_error( $inserted ) ) {
		WP_CLI::warning( $inserted->get_error_message() . " {$taxonomy}/{$slug}" );
		return 0;
	}

	return (int) $inserted['term_id'];
}

/**
 * Product already imported from this source ID, or one with the same slug.
 *
 * @param int    $source_id Openwood product ID.
 * @param string $slug      Product slug.
 * @return int
 */
function hw_demo_find_product_id( int $source_id, string $slug ): int {
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

	if ( $ids ) {
		return (int) $ids[0];
	}

	$post = get_page_by_path( $slug, OBJECT, 'product' );

	return $post instanceof WP_Post ? (int) $post->ID : 0;
}

$directory = __DIR__;
$products  = json_decode( (string) file_get_contents( $directory . '/products.json' ), true );
$categories = json_decode( (string) file_get_contents( $directory . '/categories.json' ), true );

if ( ! is_array( $products ) || ! is_array( $categories ) ) {
	WP_CLI::error( 'products.json or categories.json is missing or invalid.' );
}

update_option( 'woocommerce_currency', 'PKR' );
update_option( 'woocommerce_currency_pos', 'left_space' );
update_option( 'woocommerce_price_thousand_sep', ',' );
update_option( 'woocommerce_price_decimal_sep', '.' );
update_option( 'woocommerce_price_num_decimals', '0' );

wp_raise_memory_limit( 'admin' );
wp_defer_term_counting( true );
wp_suspend_cache_invalidation( true );

$category_map = array();
$pending      = $categories;
$guard        = 0;

while ( $pending && $guard < 10 ) {
	++$guard;
	$next = array();

	foreach ( $pending as $category ) {
		if ( ! is_array( $category ) ) {
			continue;
		}

		$source_id = (int) ( $category['id'] ?? 0 );
		$parent    = (int) ( $category['parent'] ?? 0 );
		if ( $parent && ! isset( $category_map[ $parent ] ) ) {
			$next[] = $category;
			continue;
		}

		$name   = hw_demo_text( $category['name'] ?? '' );
		$slug   = sanitize_title( (string) ( $category['slug'] ?? $name ) );
		$parent_id = $parent ? (int) $category_map[ $parent ] : 0;
		$existing  = get_term_by( 'slug', $slug, 'product_cat' );

		if ( $existing instanceof WP_Term ) {
			wp_update_term(
				(int) $existing->term_id,
				'product_cat',
				array(
					'name'        => $name,
					'description' => hw_demo_text( $category['description'] ?? '' ),
					'parent'      => $parent_id,
				)
			);
			$term_id = (int) $existing->term_id;
		} else {
			$inserted = wp_insert_term(
				$name,
				'product_cat',
				array(
					'slug'        => $slug,
					'description' => hw_demo_text( $category['description'] ?? '' ),
					'parent'      => $parent_id,
				)
			);

			if ( is_wp_error( $inserted ) ) {
				WP_CLI::warning( $inserted->get_error_message() . ' category ' . $slug );
				continue;
			}

			$term_id = (int) $inserted['term_id'];
		}

		update_term_meta( $term_id, '_openwood_category_id', $source_id );

		$image = $category['image'] ?? null;
		if ( is_array( $image ) && ! empty( $image['src'] ) ) {
			$image_id = hw_demo_attachment_from_url(
				(string) $image['src'],
				0,
				hw_demo_text( $image['alt'] ?? $image['name'] ?? $name )
			);
			if ( $image_id ) {
				update_term_meta( $term_id, 'thumbnail_id', $image_id );
			}
		}

		$category_map[ $source_id ] = $term_id;
	}

	if ( count( $next ) === count( $pending ) ) {
		WP_CLI::warning( 'Could not resolve parent for ' . count( $next ) . ' categories.' );
		break;
	}

	$pending = $next;
}

WP_CLI::log( 'Categories: ' . count( $category_map ) );

$created        = 0;
$updated        = 0;
$image_failures = 0;
$total          = count( $products );

foreach ( $products as $index => $raw ) {
	if ( ! is_array( $raw ) ) {
		continue;
	}

	$source_id = (int) ( $raw['id'] ?? 0 );
	$name      = hw_demo_text( $raw['name'] ?? '' );
	$slug      = sanitize_title( (string) ( $raw['slug'] ?? $name ) );
	$existing  = hw_demo_find_product_id( $source_id, $slug );
	$product   = $existing ? wc_get_product( $existing ) : new WC_Product_Simple();

	if ( ! $product instanceof WC_Product ) {
		WP_CLI::warning( 'Could not load product ' . $source_id );
		continue;
	}

	$prices  = is_array( $raw['prices'] ?? null ) ? $raw['prices'] : array();
	$regular = (string) ( $prices['regular_price'] ?? '' );
	$sale    = (string) ( $prices['sale_price'] ?? '' );
	if ( '' === $regular ) {
		$regular = (string) ( $prices['price'] ?? '' );
	}

	$product->set_name( $name );
	$product->set_slug( $slug );
	$product->set_status( 'publish' );
	$product->set_catalog_visibility( 'visible' );
	$product->set_description( (string) ( $raw['description'] ?? '' ) );
	$product->set_short_description( (string) ( $raw['short_description'] ?? '' ) );

	// Size variations own the price. A later catalog re-import must not flatten them.
	$preserve_variations = $existing && 'yes' === (string) get_post_meta( $existing, '_openwood_size_variations', true );
	if ( ! $preserve_variations ) {
		$product->set_regular_price( $regular );
		if ( '' !== $sale && is_numeric( $sale ) && is_numeric( $regular ) && (float) $sale < (float) $regular ) {
			$product->set_sale_price( $sale );
		} else {
			$product->set_sale_price( '' );
		}
	}
	$product->set_stock_status( ! empty( $raw['is_in_stock'] ) ? 'instock' : 'outofstock' );
	$product->set_menu_order( (int) $index );

	$sku = trim( (string) ( $raw['sku'] ?? '' ) );
	if ( '' !== $sku ) {
		try {
			$product->set_sku( $sku );
		} catch ( Exception $exception ) {
			WP_CLI::warning( $exception->getMessage() . ' sku ' . $sku );
		}
	}

	$category_ids = array();
	foreach ( (array) ( $raw['categories'] ?? array() ) as $category ) {
		$source_category = (int) ( is_array( $category ) ? ( $category['id'] ?? 0 ) : 0 );
		if ( $source_category && isset( $category_map[ $source_category ] ) ) {
			$category_ids[] = $category_map[ $source_category ];
		}
	}
	$product->set_category_ids( $category_ids );

	$attributes = array();
	foreach ( array_values( (array) ( $raw['attributes'] ?? array() ) ) as $position => $attribute_raw ) {
		if ( ! is_array( $attribute_raw ) ) {
			continue;
		}

		$taxonomy = (string) ( $attribute_raw['taxonomy'] ?? '' );
		$label    = hw_demo_text( $attribute_raw['name'] ?? $taxonomy );
		if ( '' === $taxonomy || '' === $label ) {
			continue;
		}

		list( $attribute_id, $taxonomy_name ) = hw_demo_ensure_attribute( $taxonomy, $label );
		if ( ! $attribute_id || '' === $taxonomy_name ) {
			continue;
		}

		$term_ids = array();
		foreach ( (array) ( $attribute_raw['terms'] ?? array() ) as $term_raw ) {
			if ( ! is_array( $term_raw ) ) {
				continue;
			}
			$term_id = hw_demo_ensure_term(
				$taxonomy_name,
				hw_demo_text( $term_raw['name'] ?? '' ),
				sanitize_title( (string) ( $term_raw['slug'] ?? $term_raw['name'] ?? '' ) )
			);
			if ( $term_id ) {
				$term_ids[] = $term_id;
			}
		}

		if ( ! $term_ids ) {
			continue;
		}

		$attribute = new WC_Product_Attribute();
		$attribute->set_id( $attribute_id );
		$attribute->set_name( $taxonomy_name );
		$attribute->set_options( $term_ids );
		$attribute->set_position( (int) $position );
		$attribute->set_visible( true );
		$attribute->set_variation( false );
		$attributes[] = $attribute;
	}
	if ( ! $preserve_variations ) {
		$product->set_attributes( $attributes );
	}

	$product->update_meta_data( '_openwood_product_id', $source_id );
	$product->update_meta_data( '_openwood_permalink', (string) ( $raw['permalink'] ?? '' ) );
	$product_id = $product->save();

	$image_ids = array();
	foreach ( (array) ( $raw['images'] ?? array() ) as $image ) {
		if ( ! is_array( $image ) || empty( $image['src'] ) ) {
			continue;
		}
		$image_id = hw_demo_attachment_from_url(
			(string) $image['src'],
			$product_id,
			hw_demo_text( $image['alt'] ?? $image['name'] ?? $name )
		);
		if ( $image_id ) {
			$image_ids[] = $image_id;
		} else {
			++$image_failures;
		}
	}

	if ( $image_ids ) {
		$product = wc_get_product( $product_id );
		if ( $product instanceof WC_Product ) {
			$product->set_image_id( $image_ids[0] );
			$product->set_gallery_image_ids( array_slice( $image_ids, 1 ) );
			$product->save();
		}
	}

	if ( $existing ) {
		++$updated;
	} else {
		++$created;
	}

	WP_CLI::log(
		sprintf(
			'%d/%d %s (%d images)',
			$index + 1,
			$total,
			$name,
			count( $image_ids )
		)
	);
}

wp_suspend_cache_invalidation( false );
wp_defer_term_counting( false );
wc_delete_product_transients();

if ( function_exists( 'wc_update_product_lookup_tables' ) ) {
	wc_update_product_lookup_tables();
}

WP_CLI::success(
	sprintf(
		'Imported %d new, updated %d, image failures %d.',
		$created,
		$updated,
		$image_failures
	)
);
