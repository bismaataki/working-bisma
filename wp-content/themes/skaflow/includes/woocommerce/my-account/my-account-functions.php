<?php
/**
 * My Account chrome and account-only assets.
 *
 * @package Skaflow
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', 'skaflow_enqueue_my_account_assets' );
add_action( 'woocommerce_before_account_navigation', 'skaflow_render_account_header' );
add_filter( 'woocommerce_account_menu_items', 'skaflow_account_menu_item_labels', 20 );

/**
 * Account webpack entry — CSS/JS only on the My Account page.
 */
function skaflow_enqueue_my_account_assets(): void {
	if ( ! function_exists( 'is_account_page' ) || ! is_account_page() ) {
		return;
	}

	$style  = 'my-account.css';
	$script = 'my-account.js';
	$asset  = skaflow_get_build_dir( 'my-account.asset.php' );

	if ( ! file_exists( $asset ) ) {
		return;
	}

	$meta     = require $asset;
	$deps     = isset( $meta['dependencies'] ) && is_array( $meta['dependencies'] ) ? $meta['dependencies'] : array();
	$version  = isset( $meta['version'] ) ? (string) $meta['version'] : filemtime( skaflow_get_build_dir( $script ) );
	$handle   = 'skaflow-my-account';
	$style_id = $handle . '-style';

	if ( file_exists( skaflow_get_build_dir( $style ) ) ) {
		wp_enqueue_style(
			$style_id,
			skaflow_get_build_url( $style ),
			array(),
			filemtime( skaflow_get_build_dir( $style ) )
		);
	}

	wp_enqueue_script(
		$handle,
		skaflow_get_build_url( $script ),
		$deps,
		$version,
		true
	);
}

/**
 * Figma labels. Slugs stay WooCommerce's.
 *
 * @param array<string, string> $items Endpoint => label.
 * @return array<string, string>
 */
function skaflow_account_menu_item_labels( array $items ): array {
	if ( isset( $items['dashboard'] ) ) {
		$items['dashboard'] = __( 'Overview', 'skaflow' );
	}

	if ( isset( $items['edit-account'] ) ) {
		$items['edit-account'] = __( 'Settings', 'skaflow' );
	}

	return $items;
}

/**
 * Welcome row + Sign Out. Tabs stay in navigation.php.
 */
function skaflow_render_account_header(): void {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user = wp_get_current_user();
	$name = $user instanceof WP_User ? $user->display_name : '';

	$sign_out = '';

	if ( function_exists( 'wc_logout_url' ) && function_exists( 'skaflow_get_buttons_markup' ) ) {
		$sign_out = skaflow_get_buttons_markup(
			array(
				array(
					'label'         => __( 'Sign Out', 'skaflow' ),
					'url'           => wc_logout_url(),
					'style'         => 'is-style-ghost',
					'class'         => 'has-small-font-size is-inline-small-button',
					'icon'          => 'arrow-right',
					'icon_position' => 'right',
				),
			)
		);
	}

	echo '<header class="sk-my-account-header has-global-padding alignwide">';
	echo '<div class="sk-my-account-header__intro">';
	echo '<h1 class="wp-block-heading">' . esc_html__( 'My Account', 'skaflow' ) . '</h1>';

	if ( '' !== $name ) {
		echo '<p>' . esc_html( sprintf( /* translators: %s: customer display name */ __( 'Welcome back, %s', 'skaflow' ), $name ) ) . '</p>';
	}

	echo '</div>';
	echo $sign_out; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- skaflow_get_buttons_markup().
	echo '</header>';
}
