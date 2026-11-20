<?php
/**
 * Category Showcase block.
 *
 * @package Happywood
 */

defined( 'ABSPATH' ) || exit;

$category_slug = isset( $attributes['categorySlug'] )
    ? sanitize_title( $attributes['categorySlug'] )
    : '';

$legacy_category_id = isset( $attributes['categoryId'] )
    ? absint( $attributes['categoryId'] )
    : 0;

// Resolve the selected category using its slug.
$category = $category_slug
    ? get_term_by( 'slug', $category_slug, 'product_cat' )
    : false;

// Support previously saved ID-based blocks during migration.
if ( ! $category && $legacy_category_id ) {
    $category = get_term( $legacy_category_id, 'product_cat' );
}

// Default to Computer Table when no valid category is selected.
if ( ! $category || is_wp_error( $category ) ) {
    $category = get_term_by( 'slug', 'computer-table', 'product_cat' );
}

if ( ! $category || is_wp_error( $category ) ) {
    return;
}

$category_id = (int) $category->term_id;

$products_per_page = isset( $attributes['productsPerPage'] )
    ? absint( $attributes['productsPerPage'] )
    : 4;

$products_per_page = max( 1, min( 6, $products_per_page ) );

if ( ! $category_id ) {
    return;
}

$category_url = get_term_link( $category );

if ( is_wp_error( $category_url ) ) {
    return;
}

$product_query = new WP_Query(
    array(
        'post_type'           => 'product',
        'post_status'         => 'publish',
        'posts_per_page'      => $products_per_page,
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'tax_query'           => array(
            array(
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => array( $category_id ),
                'include_children' => true,
            ),
        ),
    )
);

if ( ! $product_query->have_posts() ) {
    wp_reset_postdata();
    return;
}

$product_ids = wp_list_pluck( $product_query->posts, 'ID' );

$thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );

$category_image = $thumbnail_id
    ? wp_get_attachment_image(
        absint( $thumbnail_id ),
        'large',
        false,
        array(
            'class'   => 'hw-category-showcase__image',
            'loading' => 'lazy',
        )
    )
    : wc_placeholder_img(
        'large',
        array( 'class' => 'hw-category-showcase__image' )
    );

$collection_markup = happywood_get_hand_picked_product_collection_blocks_markup(
    $product_ids,
    2,
    array(
        'align'              => 'wide',
        'include_pagination' => false,
        'class_name'         => 'hw-category-showcase__products',
    )
);

$products_markup = $collection_markup ? do_blocks( $collection_markup ) : '';

wp_reset_postdata();

if ( '' === trim( $products_markup ) ) {
    return;
}

$wrapper_attributes = get_block_wrapper_attributes(
    array(
        'class' => 'hw-category-showcase',
    )
);
?>

<section <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

    <div class="hw-category-showcase__heading">
        <h2 class="hw-category-showcase__title">
            <?php echo esc_html( $category->name ); ?>
        </h2>

        <a
            class="hw-category-showcase__view-all"
            href="<?php echo esc_url( $category_url ); ?>"
        >
            <?php esc_html_e( 'View All', 'happywood' ); ?>
        </a>
    </div>

    <div class="hw-category-showcase__category">
        <a
            class="hw-category-showcase__category-link"
            href="<?php echo esc_url( $category_url ); ?>"
        >
            <div class="hw-category-showcase__image-wrap">
                <?php echo $category_image; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

                <div class="hw-category-showcase__overlay">
                    <span class="hw-category-showcase__lifestyle">
                        <?php esc_html_e( 'Choose Your Lifestyle', 'happywood' ); ?>
                    </span>

                    <span class="hw-category-showcase__shop-link">
                        <?php esc_html_e( 'Shop All', 'happywood' ); ?>
                    </span>
                </div>
            </div>
        </a>
    </div>

    <div class="hw-category-showcase__products-wrap">
        <?php echo $products_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>

</section>
