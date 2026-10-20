<?php
/**
 * My Account Wishlist filter bar.
 *
 * @package Happywood
 *
 * @var array $args {
 *     @type array{search: string, category: string, date: string, sort: string} $filters
 *     @type array<int, array{product_id: int, date_added: string}>               $items
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filters = ( isset( $args ) && is_array( $args ) && isset( $args['filters'] ) && is_array( $args['filters'] ) )
	? $args['filters']
	: happywood_get_account_wishlist_filters();

$items = ( isset( $args ) && is_array( $args ) && isset( $args['items'] ) && is_array( $args['items'] ) )
	? $args['items']
	: array();

$keys       = happywood_get_account_wishlist_filter_keys();
$action     = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'wishlist' ) : '';
$categories = happywood_get_account_wishlist_category_choices( $items );
$dates      = function_exists( 'happywood_get_account_orders_date_choices' ) ? happywood_get_account_orders_date_choices() : array();
$sorts      = function_exists( 'happywood_get_account_orders_sort_choices' ) ? happywood_get_account_orders_sort_choices() : array();
$active     = happywood_account_wishlist_filters_are_active( $filters );
?>
<form class="hw-account-wishlist-filters is-style-bordered" method="get" action="<?php echo esc_url( $action ); ?>">
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Search', 'happywood' ); ?></span>
		<input type="search" name="<?php echo esc_attr( $keys['search'] ); ?>" value="<?php echo esc_attr( $filters['search'] ); ?>" placeholder="<?php esc_attr_e( 'Product name', 'happywood' ); ?>" />
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Category', 'happywood' ); ?></span>
		<select name="<?php echo esc_attr( $keys['category'] ); ?>">
			<?php foreach ( $categories as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['category'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Date added', 'happywood' ); ?></span>
		<select name="<?php echo esc_attr( $keys['date'] ); ?>">
			<?php foreach ( $dates as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['date'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Sort', 'happywood' ); ?></span>
		<select name="<?php echo esc_attr( $keys['sort'] ); ?>">
			<?php foreach ( $sorts as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<div class="hw-account-wishlist-filters__actions">
		<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
			<div class="wp-block-button is-style-light has-small-font-size is-inline-small-button">
				<button type="submit" class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Apply', 'happywood' ); ?></button>
			</div>
			<?php
			if ( function_exists( 'happywood_get_buttons_markup' ) ) {
				$clear = array(
					'label' => __( 'Clear', 'happywood' ),
					'url'   => $action ? $action : '#',
					'style' => 'is-style-link',
					'class' => 'has-small-font-size is-inline-small-button',
				);

				if ( ! $active ) {
					$clear['html_attributes'] = array(
						'aria-disabled' => 'true',
						'tabindex'      => '-1',
					);
					$clear['element_class']   = 'is-disabled';
				}

				echo happywood_get_buttons_markup( array( $clear ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helper.
			}
			?>
		</div>
	</div>
</form>
