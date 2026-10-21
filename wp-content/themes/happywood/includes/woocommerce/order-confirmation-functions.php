<?php
/**
 * Order confirmation (Figma Step 4) markup.
 *
 * Shared order lookup and the fulfillment card. Answers stay in
 * order-questions-functions.php.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'render_block_core/breadcrumbs', 'happywood_filter_confirmation_breadcrumbs', 10, 2 );
add_filter( 'body_class', 'happywood_confirmation_body_class' );
add_filter( 'woocommerce_order_email_verification_required', 'happywood_confirmation_skip_email_verification', 10, 3 );

/**
 * The received-page order, or null when the request is not allowed to see it.
 *
 * Key check matches WooCommerce's guest-safe received URL. Customer orders
 * also require the viewer to be that customer when WC asks for it.
 *
 * @return \WC_Order|null
 */
function happywood_get_confirmation_order() {
	if ( ! function_exists( 'wc_get_order' ) ) {
		return null;
	}

	$order_id = absint( get_query_var( 'order-received' ) );

	if ( ! $order_id ) {
		return null;
	}

	$order = wc_get_order( $order_id );

	if ( ! $order ) {
		return null;
	}

	return happywood_can_view_confirmation_order( $order ) ? $order : null;
}

/**
 * Whether this request may show confirmation details for an order.
 *
 * @param \WC_Order $order Order on the received page.
 *
 * @return bool
 */
function happywood_can_view_confirmation_order( $order ) {
	if ( ! $order instanceof WC_Order ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';

	if ( '' === $key || ! $order->key_is_valid( $key ) ) {
		return false;
	}

	if ( $order->get_user_id() > 0 ) {
		$verify = apply_filters( 'woocommerce_order_received_verify_known_shoppers', true );

		if ( $verify && (int) get_current_user_id() !== (int) $order->get_user_id() ) {
			return false;
		}
	}

	return true;
}

/**
 * A valid received-page key is enough to show WC totals on this template.
 *
 * Without this, WooCommerce hides the Totals block after the guest grace
 * period and asks for the billing email. Our cards already honour the key,
 * so the sidebar would be an empty "Order Summary" heading.
 *
 * @param bool      $required Whether WC wants an email check.
 * @param \WC_Order $order   Order on the received page.
 * @param string    $context  `order-received` or `order-pay`.
 *
 * @return bool
 */
function happywood_confirmation_skip_email_verification( $required, $order, $context ) {
	if ( 'order-received' !== $context ) {
		return $required;
	}

	return happywood_can_view_confirmation_order( $order ) ? false : $required;
}

/**
 * Whether the order-confirmation template includes the fulfillment card.
 *
 * Used so the answers hook stays a fallback: if someone removes the card,
 * Totals still prints the list.
 *
 * @return bool
 */
function happywood_confirmation_template_has_fulfillment_block() {
	static $has_block = null;

	if ( null !== $has_block ) {
		return $has_block;
	}

	$has_block = false;

	if ( ! function_exists( 'get_block_template' ) ) {
		return $has_block;
	}

	$template = get_block_template( get_stylesheet() . '//order-confirmation', 'wp_template' );

	if ( $template && ! empty( $template->content ) ) {
		$has_block = has_block( 'happywood/order-confirmation-fulfillment', $template->content );
	}

	return $has_block;
}

/**
 * Add a page class so confirmation chrome can target the received page.
 *
 * @param string[] $classes Body classes.
 *
 * @return string[]
 */
function happywood_confirmation_body_class( array $classes ) {
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		$classes[] = 'hw-order-confirmation-page';
	}

	return $classes;
}

/**
 * Home › Cart › Checkout › Order Confirmation on the received page.
 *
 * @param string $content Rendered breadcrumbs markup.
 *
 * @return string
 */
function happywood_filter_confirmation_breadcrumbs( $content ) {
	if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
		return $content;
	}

	if ( ! function_exists( 'happywood_render_breadcrumb_list_html' ) ) {
		return $content;
	}

	$crumbs = array(
		array( __( 'Home', 'happywood' ), home_url( '/' ) ),
		array( __( 'Cart', 'happywood' ), function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' ) ),
		array( __( 'Checkout', 'happywood' ), function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : home_url( '/checkout/' ) ),
		array( __( 'Order Confirmation', 'happywood' ), '' ),
	);

	$list = happywood_render_breadcrumb_list_html(
		$crumbs,
		array(
			'nav_class' => 'woocommerce-breadcrumb',
			'label'     => __( 'Breadcrumb', 'woocommerce' ),
		)
	);

	if ( '' === $list ) {
		return $content;
	}

	if ( preg_match( '/^(<nav\b[^>]*>)/i', $content, $matches ) ) {
		return $matches[1] . preg_replace( '/^<nav\b[^>]*>|<\/nav>$/i', '', $list ) . '</nav>';
	}

	return $list;
}

/**
 * Lucide icon span used on confirmation cards.
 *
 * @param string $slug Curated Lucide slug.
 *
 * @return string
 */
function happywood_get_confirmation_icon_html( $slug ) {
	return sprintf(
		'<span class="hw-order-confirmation__icon hw-icon-preview hw-icon-%s" aria-hidden="true"></span>',
		esc_attr( sanitize_html_class( $slug ) )
	);
}

/**
 * WooCommerce pickup warehouse snapshot from the shipping line.
 *
 * Location name, address, and details are what WC stored at checkout.
 *
 * @param \WC_Order $order Order being confirmed.
 *
 * @return string HTML, empty when the order has no pickup snapshot.
 */
function happywood_get_confirmation_pickup_snapshot_html( $order ) {
	foreach ( $order->get_shipping_methods() as $item ) {
		$name    = (string) $item->get_meta( 'pickup_location' );
		$address = (string) $item->get_meta( 'pickup_address' );
		$details = (string) $item->get_meta( 'pickup_details' );

		if ( '' === $name && '' === $address ) {
			continue;
		}

		$lines = array_filter( array( $name, $address, $details ) );

		return nl2br( esc_html( implode( "\n", $lines ) ), false );
	}

	return '';
}

/**
 * Delivery or pickup card, including our eight answers.
 *
 * @param \WC_Order $order Order being confirmed.
 *
 * @return string
 */
function happywood_get_confirmation_fulfillment_html( $order ) {
	$answers   = function_exists( 'happywood_get_order_answers' ) ? happywood_get_order_answers( $order ) : array();
	$group     = function_exists( 'happywood_get_order_answers_group' ) ? happywood_get_order_answers_group( $answers ) : '';
	$is_pickup = 'collection' === $group;

	$address = $is_pickup
		? happywood_get_confirmation_pickup_snapshot_html( $order )
		: $order->get_formatted_shipping_address();

	if ( ! $address ) {
		$address = $order->get_formatted_billing_address();
	}

	$method        = $order->get_shipping_method();
	$payment       = $order->get_payment_method_title();
	$contact_lines = array_filter(
		array(
			$order->get_billing_email(),
			$order->get_billing_phone(),
		)
	);

	$instruction_key = $is_pickup ? 'carrier_note' : 'delivery_instructions';
	$instructions    = isset( $answers[ $instruction_key ]['value'] ) ? $answers[ $instruction_key ]['value'] : '';
	$list_answers    = $answers;
	unset( $list_answers[ $instruction_key ] );

	$address_label = $is_pickup
		? __( 'Pickup location', 'happywood' )
		: __( 'Shipping address', 'happywood' );

	$method_label = $is_pickup
		? __( 'Collection method', 'happywood' )
		: __( 'Delivery method', 'happywood' );

	$method_icon = $is_pickup ? 'package' : 'truck';

	$facts = array(
		array(
			'icon'  => 'map-pin',
			'label' => $address_label,
			'value' => $address ? wp_kses_post( $address ) : '',
		),
		array(
			'icon'  => 'file-text',
			'label' => __( 'Payment method', 'happywood' ),
			'value' => $payment ? esc_html( $payment ) : '',
			'extra' => $order->is_paid() ? '<span class="hw-order-confirmation-fulfillment__paid">' . esc_html__( 'Payment successful', 'happywood' ) . '</span>' : '',
		),
		array(
			'icon'  => $method_icon,
			'label' => $method_label,
			'value' => $method ? esc_html( $method ) : '',
		),
		array(
			'icon'  => 'phone',
			'label' => __( 'Contact details', 'happywood' ),
			'value' => $contact_lines ? nl2br( esc_html( implode( "\n", $contact_lines ) ), false ) : '',
		),
	);

	ob_start();
	?>
	<div class="hw-order-confirmation-fulfillment">
	<h3 class="hw-order-confirmation-fulfillment__heading">
		<?php
		echo esc_html(
			$is_pickup
				? __( 'Pickup information', 'happywood' )
				: __( 'Shipment information', 'happywood' )
		);
		?>
	</h3>
	<section class="hw-order-confirmation-card">
		<div class="hw-order-confirmation-fulfillment__grid">
			<?php foreach ( $facts as $fact ) : ?>
				<?php if ( '' === $fact['value'] ) : ?>
					<?php continue; ?>
				<?php endif; ?>
				<div class="hw-order-confirmation-fulfillment__fact">
					<h3 class="hw-order-confirmation-fulfillment__fact-label">
						<?php echo happywood_get_confirmation_icon_html( $fact['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php echo esc_html( $fact['label'] ); ?>
					</h3>
					<div class="hw-order-confirmation-fulfillment__fact-value"><?php echo $fact['value']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above or wp_kses_post for address ?></div>
					<?php
					if ( ! empty( $fact['extra'] ) ) {
						echo $fact['extra']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php if ( $instructions ) : ?>
			<div class="hw-order-confirmation-fulfillment__notes">
				<h3 class="hw-order-confirmation-fulfillment__notes-label">
					<?php echo esc_html( $answers[ $instruction_key ]['label'] ); ?>
				</h3>
				<p><?php echo esc_html( $instructions ); ?></p>
			</div>
		<?php endif; ?>
		<?php if ( $list_answers ) : ?>
			<dl class="hw-order-confirmation-fulfillment__answers">
				<?php foreach ( $list_answers as $answer ) : ?>
					<dt><?php echo esc_html( $answer['label'] ); ?></dt>
					<dd><?php echo esc_html( $answer['value'] ); ?></dd>
				<?php endforeach; ?>
			</dl>
		<?php endif; ?>
	</section>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Editor notice when there is no received-page order.
 *
 * @param string $title       Region title.
 * @param string $description What the block prints on the frontend.
 *
 * @return string
 */
function happywood_get_confirmation_editor_placeholder_html( $title, $description ) {
	return sprintf(
		'<div class="hw-order-confirmation-placeholder"><p><strong>%s</strong> — %s</p></div>',
		esc_html( $title ),
		esc_html( $description )
	);
}
