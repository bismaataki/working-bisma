<?php
/**
 * My Account Orders filter bar.
 *
 * @package Happywood
 *
 * @var array $args {
 *     @type array{number: string, status: string, date: string, sort: string} $filters
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filters = ( isset( $args ) && is_array( $args ) && isset( $args['filters'] ) && is_array( $args['filters'] ) )
	? $args['filters']
	: happywood_get_account_orders_filters();

$keys     = happywood_get_account_orders_filter_keys();
$action   = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '';
$statuses = happywood_get_account_orders_status_choices();
$dates    = happywood_get_account_orders_date_choices();
$sorts    = happywood_get_account_orders_sort_choices();
$active   = happywood_account_orders_filters_are_active( $filters );
?>
<form class="hw-account-orders-filters is-style-bordered" method="get" action="<?php echo esc_url( $action ); ?>">
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Order number', 'happywood' ); ?></span>
		<input type="search" name="<?php echo esc_attr( $keys['number'] ); ?>" value="<?php echo esc_attr( $filters['number'] ); ?>" placeholder="<?php esc_attr_e( 'Order number', 'happywood' ); ?>" />
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Status', 'happywood' ); ?></span>
		<select name="<?php echo esc_attr( $keys['status'] ); ?>">
			<?php foreach ( $statuses as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['status'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Date', 'happywood' ); ?></span>
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
	<div class="hw-account-orders-filters__actions">
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
