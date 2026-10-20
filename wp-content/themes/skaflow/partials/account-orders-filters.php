<?php
/**
 * My Account Orders filter bar.
 *
 * @package Skaflow
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
	: skaflow_get_account_orders_filters();

$keys     = skaflow_get_account_orders_filter_keys();
$action   = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '';
$statuses = skaflow_get_account_orders_status_choices();
$dates    = skaflow_get_account_orders_date_choices();
$sorts    = skaflow_get_account_orders_sort_choices();
$active   = skaflow_account_orders_filters_are_active( $filters );
?>
<form class="sk-account-orders-filters is-style-bordered" method="get" action="<?php echo esc_url( $action ); ?>">
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Order number', 'skaflow' ); ?></span>
		<input type="search" name="<?php echo esc_attr( $keys['number'] ); ?>" value="<?php echo esc_attr( $filters['number'] ); ?>" placeholder="<?php esc_attr_e( 'Order number', 'skaflow' ); ?>" />
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Status', 'skaflow' ); ?></span>
		<select name="<?php echo esc_attr( $keys['status'] ); ?>">
			<?php foreach ( $statuses as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['status'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Date', 'skaflow' ); ?></span>
		<select name="<?php echo esc_attr( $keys['date'] ); ?>">
			<?php foreach ( $dates as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['date'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label>
		<span class="is-style-text-lead has-small-font-size"><?php esc_html_e( 'Sort', 'skaflow' ); ?></span>
		<select name="<?php echo esc_attr( $keys['sort'] ); ?>">
			<?php foreach ( $sorts as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $filters['sort'], $value ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<div class="sk-account-orders-filters__actions">
		<div class="wp-block-buttons is-layout-flex wp-block-buttons-is-layout-flex">
			<div class="wp-block-button is-style-light has-small-font-size is-inline-small-button">
				<button type="submit" class="wp-block-button__link wp-element-button"><?php esc_html_e( 'Apply', 'skaflow' ); ?></button>
			</div>
			<?php
			if ( function_exists( 'skaflow_get_buttons_markup' ) ) {
				$clear = array(
					'label' => __( 'Clear', 'skaflow' ),
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

				echo skaflow_get_buttons_markup( array( $clear ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- assembled helper.
			}
			?>
		</div>
	</div>
</form>
