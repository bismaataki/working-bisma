<?php
/**
 * Shared My Account order card — Overview and Orders.
 *
 * @package Skaflow
 *
 * @var array $args {
 *     @type WC_Order $order
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order = ( isset( $args ) && is_array( $args ) && isset( $args['order'] ) && $args['order'] instanceof WC_Order )
	? $args['order']
	: null;

if ( ! $order instanceof WC_Order ) {
	return;
}

$status      = $order->get_status();
$status_name = function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $status ) : $status;
$created     = $order->get_date_created();
$number      = _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number();
$total       = $order->get_formatted_order_total();
$items       = $order->get_items( 'line_item' );
$shipping    = $order->get_items( 'shipping' );
$placeholder = get_theme_file_uri( 'assets/images/placeholder.png' );
?>
<article class="sk-account-order-card is-style-bordered">
	<dl class="sk-account-order-card__header">
		<div>
			<dt class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Order Number', 'skaflow' ); ?></dt>
			<dd>
				<h3 class="wp-block-heading has-large-font-size"><?php echo esc_html( $number ); ?></h3>
			</dd>
		</div>
		<div>
			<dt class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Order Date', 'skaflow' ); ?></dt>
			<dd>
				<?php if ( $created ) : ?>
					<time datetime="<?php echo esc_attr( $created->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $created ) ); ?></time>
				<?php endif; ?>
			</dd>
		</div>
		<div>
			<dt class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Total Amount', 'skaflow' ); ?></dt>
			<dd><?php echo wp_kses_post( $total ); ?></dd>
		</div>
		<div>
			<dt class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Status', 'skaflow' ); ?></dt>
			<dd>
				<span class="sk-account-status sk-account-status--<?php echo esc_attr( sanitize_html_class( $status ) ); ?>">
					<?php echo esc_html( $status_name ); ?>
				</span>
			</dd>
		</div>
	</dl>

	<?php if ( ! empty( $items ) || ! empty( $shipping ) ) : ?>
		<ul class="sk-account-order-card__items">
			<?php
			foreach ( $items as $item ) {
				if ( ! $item instanceof WC_Order_Item_Product ) {
					continue;
				}

				$product = $item->get_product();
				$name    = $item->get_name();
				$qty     = (int) $item->get_quantity();
				$price   = $order->get_formatted_line_subtotal( $item );
				$attrs   = skaflow_get_account_order_item_attrs( $item );
				$meta    = '' !== $attrs
					? sprintf( '%1$s × %2$s', $attrs, $qty )
					: sprintf(
						/* translators: %s: quantity */
						__( 'Qty %s', 'skaflow' ),
						$qty
					);
				?>
				<li class="sk-account-order-card__item">
					<div class="sk-account-order-card__media">
						<?php
						if ( $product instanceof WC_Product ) {
							echo $product->get_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- WC product image.
								'woocommerce_gallery_thumbnail',
								array(
									'class' => 'sk-account-order-card__thumb',
									'alt'   => esc_attr( $name ),
								)
							);
						} else {
							?>
							<img class="sk-account-order-card__thumb" src="<?php echo esc_url( $placeholder ); ?>" alt="" width="72" height="72" />
							<?php
						}
						?>
					</div>
					<div class="sk-account-order-card__copy">
						<p class="sk-account-order-card__name"><?php echo esc_html( $name ); ?></p>
						<p class="sk-account-order-card__meta"><?php echo esc_html( $meta ); ?></p>
					</div>
					<?php if ( '' !== $price ) : ?>
						<p class="sk-account-order-card__price"><?php echo wp_kses_post( $price ); ?></p>
					<?php endif; ?>
				</li>
				<?php
			}

			foreach ( $shipping as $ship_item ) {
				if ( ! $ship_item instanceof WC_Order_Item_Shipping ) {
					continue;
				}

				$ship_name  = $ship_item->get_name();
				$ship_icon  = skaflow_get_account_order_shipping_icon_slug( $ship_item );
				$ship_meta  = str_contains( strtolower( (string) $ship_item->get_method_id() ), 'pickup' )
					? __( 'Pickup', 'skaflow' )
					: __( 'Shipping', 'skaflow' );
				$ship_price = skaflow_get_account_order_shipping_price( $order, $ship_item );
				?>
				<li class="sk-account-order-card__item sk-account-order-card__item--shipping">
					<div class="sk-account-order-card__media sk-account-order-card__media--icon" aria-hidden="true">
						<span class="sk-icon-preview sk-icon-<?php echo esc_attr( $ship_icon ); ?>"></span>
					</div>
					<div class="sk-account-order-card__copy">
						<p class="sk-account-order-card__name"><?php echo esc_html( $ship_name ); ?></p>
						<p class="sk-account-order-card__meta"><?php echo esc_html( $ship_meta ); ?></p>
					</div>
					<?php if ( '' !== $ship_price ) : ?>
						<p class="sk-account-order-card__price"><?php echo wp_kses_post( $ship_price ); ?></p>
					<?php endif; ?>
				</li>
				<?php
			}
			?>
		</ul>
	<?php endif; ?>

	<?php echo skaflow_get_account_order_actions_markup( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helper. ?>
</article>
