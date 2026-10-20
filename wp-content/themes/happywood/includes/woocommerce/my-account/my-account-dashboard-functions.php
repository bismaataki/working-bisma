<?php
/**
 * My Account Overview (dashboard) data and markup.
 *
 * @package Happywood
 */

defined( 'ABSPATH' ) || exit;

/**
 * How many recent orders the Overview lists.
 */
function happywood_get_account_dashboard_order_limit(): int {
	$limit = (int) apply_filters( 'happywood_account_dashboard_order_limit', 3 );

	return max( 1, $limit );
}

/**
 * Page-1 of the same query WooCommerce uses on the Orders endpoint.
 *
 * @return object{orders: array<int, WC_Order>, total: int}
 */
function happywood_get_account_dashboard_orders() {
	$empty = (object) array(
		'orders' => array(),
		'total'  => 0,
	);

	if ( ! function_exists( 'wc_get_orders' ) ) {
		return $empty;
	}

	$limit = happywood_get_account_dashboard_order_limit();
	$query = apply_filters(
		'woocommerce_my_account_my_orders_query',
		array(
			'customer' => get_current_user_id(),
			'page'     => 1,
			'limit'    => $limit,
			'paginate' => true,
		)
	);

	$result = wc_get_orders( $query );
	$raw    = array();
	$total  = 0;

	if ( is_object( $result ) && isset( $result->orders ) && is_array( $result->orders ) ) {
		$raw   = $result->orders;
		$total = isset( $result->total ) ? (int) $result->total : count( $raw );
	} elseif ( is_array( $result ) ) {
		$raw   = $result;
		$total = count( $raw );
	}

	$orders = array();

	foreach ( $raw as $customer_order ) {
		$order = $customer_order instanceof WC_Order ? $customer_order : wc_get_order( $customer_order );

		if ( $order instanceof WC_Order ) {
			$orders[] = $order;
		}
	}

	return (object) array(
		'orders' => array_slice( $orders, 0, $limit ),
		'total'  => $total,
	);
}

/**
 * Overview two-column layout: recent orders + profile.
 */
function happywood_render_account_dashboard( WP_User $current_user ): void {
	echo '<div class="hw-account-dashboard wp-block-columns is-layout-flex is-style-has-sidebar-right">';
	echo '<div class="wp-block-column hw-sidebar-main">';
	echo happywood_get_account_dashboard_orders_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
	echo '</div>';
	echo '<div class="wp-block-column hw-sidebar-aside">';
	echo happywood_get_account_profile_card_markup( $current_user ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helpers.
	echo '</div>';
	echo '</div>';
}

/**
 * Recent Orders heading, cards, or empty state.
 */
function happywood_get_account_dashboard_orders_markup(): string {
	$result = happywood_get_account_dashboard_orders();
	$orders = $result->orders;
	$html   = '<section class="hw-account-orders is-style-bordered">';
	$html  .= '<header class="hw-account-orders__head">';
	$html  .= '<h2 class="wp-block-heading">' . esc_html__( 'Recent Orders', 'happywood' ) . '</h2>';

	if ( ! empty( $orders ) && function_exists( 'wc_get_account_endpoint_url' ) ) {
		$html .= happywood_get_buttons_markup(
			array(
				array(
					'label' => __( 'View all', 'happywood' ),
					'url'   => wc_get_account_endpoint_url( 'orders' ),
					'style' => 'is-style-link',
					'class' => 'has-small-font-size',
				),
			),
			array(
				'wrapper_class' => 'hw-account-orders__view-all',
			)
		);
	}

	$html .= '</header>';

	if ( empty( $orders ) ) {
		$html .= '<div class="hw-account-orders__empty is-style-bordered">';
		$html .= '<p>' . esc_html__( 'No order has been made yet.', 'woocommerce' ) . '</p>';

		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$html .= happywood_get_buttons_markup(
				array(
					array(
						'label' => __( 'Browse products', 'woocommerce' ),
						'url'   => apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ),
						'style' => 'is-style-primary',
						'class' => 'has-small-font-size',
						'icon'  => 'shopping-cart',
					),
				)
			);
		}

		$html .= '</div>';
		$html .= '</section>';

		return $html;
	}

	$html .= '<div class="hw-account-orders__list">';

	ob_start();
	foreach ( $orders as $order ) {
		happywood_render_account_order_card( $order );
	}
	$html .= ob_get_clean();

	$html .= '</div></section>';

	return $html;
}

/**
 * Profile card: name, member-since, billing email/phone, Edit Profile.
 */
function happywood_get_account_profile_card_markup( WP_User $user ): string {
	$name     = $user->display_name;
	$email    = $user->user_email;
	$phone    = '';
	$since    = '';
	$edit_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'edit-account' ) : '';

	if ( class_exists( 'WC_Customer' ) ) {
		$customer = new WC_Customer( $user->ID );
		$billing  = $customer->get_billing_email();
		$email    = '' !== $billing ? $billing : $customer->get_email();
		$phone    = $customer->get_billing_phone();
	}

	if ( ! empty( $user->user_registered ) ) {
		$since = sprintf(
			/* translators: %s: month and year the customer registered */
			__( 'Member since %s', 'happywood' ),
			date_i18n( 'F Y', strtotime( $user->user_registered ) )
		);
	}

	$html  = '<aside class="hw-account-profile is-style-bordered">';
	$html .= '<div class="hw-account-profile__identity">';
	$html .= '<h2 class="wp-block-heading has-xx-large-font-size">' . esc_html( $name ) . '</h2>';

	if ( '' !== $since ) {
		$html .= '<p class="is-style-text-lead has-small-font-size">' . esc_html( $since ) . '</p>';
	}

	$html .= '</div>';

	if ( '' !== $email || '' !== $phone ) {
		$html .= '<dl class="hw-account-profile__details">';

		if ( '' !== $email ) {
			$html .= '<div><dt>' . esc_html__( 'Email', 'happywood' ) . '</dt>';
			$html .= '<dd><a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a></dd></div>';
		}

		if ( '' !== $phone ) {
			$html .= '<div><dt>' . esc_html__( 'Phone', 'happywood' ) . '</dt>';
			$html .= '<dd><a href="' . esc_url( 'tel:' . $phone ) . '">' . esc_html( $phone ) . '</a></dd></div>';
		}

		$html .= '</dl>';
	}

	if ( '' !== $edit_url && function_exists( 'happywood_get_buttons_markup' ) ) {
		$html .= happywood_get_buttons_markup(
			array(
				array(
					'label' => __( 'Edit Profile', 'happywood' ),
					'url'   => $edit_url,
					'style' => 'is-style-light',
					'class' => 'has-small-font-size',
				),
			),
			array(
				'wrapper_class' => 'hw-account-profile__edit',
			)
		);
	}

	$html .= '</aside>';

	return $html;
}
