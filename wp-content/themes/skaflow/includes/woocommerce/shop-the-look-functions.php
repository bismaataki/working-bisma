<?php
/**
 * Shop the Look sidebar widget helpers.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Normalize product IDs from ACF or block fallback attributes.
 *
 * @param mixed $value Raw ID list.
 * @return int[]
 */
function skaflow_normalize_shop_the_look_product_ids( $value ): array {
	if ( ! is_array( $value ) ) {
		return array();
	}

	$ids = array();

	foreach ( $value as $item ) {
		if ( is_numeric( $item ) ) {
			$id = absint( $item );

			if ( $id > 0 ) {
				$ids[] = $id;
			}

			continue;
		}

		if ( is_array( $item ) && isset( $item['id'] ) ) {
			$id = absint( $item['id'] );

			if ( $id > 0 ) {
				$ids[] = $id;
			}
		}
	}

	return array_values( array_unique( $ids ) );
}

/**
 * Resolve publishable WooCommerce products for the widget.
 *
 * @param int[] $product_ids Product post IDs in display order.
 * @return array<int, array{id: int, title: string, url: string, price_html: string, image_html: string}>
 */
function skaflow_get_shop_the_look_products( array $product_ids ): array {
	if ( empty( $product_ids ) || ! function_exists( 'wc_get_product' ) ) {
		return array();
	}

	$products = array();

	foreach ( $product_ids as $product_id ) {
		$product_id = absint( $product_id );

		if ( $product_id <= 0 || 'publish' !== get_post_status( $product_id ) ) {
			continue;
		}

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			continue;
		}

		$image_html = $product->get_image(
			'woocommerce_thumbnail',
			array(
				'class' => 'sk-shop-the-look__image',
				'alt'   => esc_attr( $product->get_name() ),
			)
		);

		$products[] = array(
			'id'         => $product_id,
			'title'      => $product->get_name(),
			'url'        => get_permalink( $product_id ),
			'price_html' => $product->get_price_html(),
			'image_html' => $image_html,
		);
	}

	return $products;
}

/**
 * Resolve Shop the Look data for a blog post and block configuration.
 *
 * @param int                  $post_id      Blog post ID.
 * @param array<string, mixed> $attributes   Block attributes.
 * @return array<string, mixed>|null Null when the widget should not render.
 */
function skaflow_get_shop_the_look_data( int $post_id, array $attributes = array() ): ?array {
	if ( $post_id <= 0 || 'post' !== get_post_type( $post_id ) ) {
		return null;
	}

	$attributes = wp_parse_args(
		$attributes,
		array(
			'fallbackHeading'  => __( 'Shop the Look', 'skaflow' ),
			'fallbackMessage'  => __( 'Transform your space with these handpicked pieces.', 'skaflow' ),
			'fallbackProducts' => array(),
			'showViewAll'      => true,
			'viewAllLabel'     => __( 'View All', 'skaflow' ),
			'viewAllUrl'       => '',
		)
	);

	$heading     = '';
	$message     = '';
	$product_ids = array();

	if ( function_exists( 'get_field' ) ) {
		$heading = trim( (string) get_field( 'shop_the_look_heading', $post_id ) );
		$message = trim( (string) get_field( 'shop_the_look_message', $post_id ) );

		$acf_products = get_field( 'shop_the_look_products', $post_id );
		$product_ids  = skaflow_normalize_shop_the_look_product_ids( $acf_products );
	}

	if ( '' === $heading ) {
		$heading = trim( (string) $attributes['fallbackHeading'] );
	}

	if ( '' === $message ) {
		$message = trim( (string) $attributes['fallbackMessage'] );
	}

	if ( empty( $product_ids ) ) {
		$product_ids = skaflow_normalize_shop_the_look_product_ids( $attributes['fallbackProducts'] );
	}

	$products = skaflow_get_shop_the_look_products( $product_ids );

	if ( empty( $products ) ) {
		return null;
	}

	$view_all_url = trim( (string) $attributes['viewAllUrl'] );

	if ( '' === $view_all_url && function_exists( 'wc_get_page_permalink' ) ) {
		$view_all_url = (string) wc_get_page_permalink( 'shop' );
	}

	return array(
		'heading'        => $heading,
		'message'        => $message,
		'products'       => $products,
		'show_view_all'  => ! empty( $attributes['showViewAll'] ) && '' !== $view_all_url,
		'view_all_label' => trim( (string) $attributes['viewAllLabel'] ) ?: __( 'View All', 'skaflow' ),
		'view_all_url'   => $view_all_url,
	);
}

/**
 * Render the Shop the Look sidebar widget markup.
 *
 * @param int                  $post_id    Blog post ID.
 * @param array<string, mixed> $attributes Block attributes.
 * @return string
 */
function skaflow_get_shop_the_look_html( int $post_id, array $attributes = array() ): string {
	$data = skaflow_get_shop_the_look_data( $post_id, $attributes );

	if ( null === $data ) {
		return '';
	}

	ob_start();
	?>
	<div class="sk-shop-the-look__inner">
		<?php if ( '' !== $data['heading'] || '' !== $data['message'] ) : ?>
			<div class="sk-shop-the-look__header">
				<?php if ( '' !== $data['heading'] ) : ?>
					<span class="sk-shop-the-look__heading sk-widget-title"><?php echo esc_html( $data['heading'] ); ?></span>
				<?php endif; ?>

				<?php if ( '' !== $data['message'] ) : ?>
					<p class="sk-shop-the-look__message sk-widget-description"><?php echo esc_html( $data['message'] ); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<ul class="sk-shop-the-look__list">
			<?php foreach ( $data['products'] as $product ) : ?>
				<li class="sk-shop-the-look__item">
					<a class="sk-shop-the-look__link" href="<?php echo esc_url( $product['url'] ); ?>">
						<span class="sk-shop-the-look__media has-background-color has-background-background-color">
							<?php
							if ( ! empty( $product['image_html'] ) ) {
								echo wp_kses_post( $product['image_html'] );
							}
							?>
						</span>
						<span class="sk-shop-the-look__content">
							<p class="sk-shop-the-look__title"><?php echo esc_html( $product['title'] ); ?></p>
							<?php if ( ! empty( $product['price_html'] ) ) : ?>
								<p class="sk-shop-the-look__price is-style-text-price"><?php echo wp_kses_post( $product['price_html'] ); ?></p>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $data['show_view_all'] ) : ?>
			<div class="sk-shop-the-look__footer">
				<?php
				echo skaflow_get_buttons_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in helper.
					array(
						array(
							'label'         => $data['view_all_label'],
							'url'           => $data['view_all_url'],
							'style'         => 'is-style-ghost',
							'icon'          => 'arrow-right',
							'icon_position' => 'right',
						),
					),
					array(
						'wrapper_class' => 'sk-shop-the-look__view-all',
						'layout'        => array(
							'type'           => 'flex',
							'justifyContent' => 'center',
						),
					)
				);
				?>
			</div>
		<?php endif; ?>
	</div>
	<?php

	return (string) ob_get_clean();
}
