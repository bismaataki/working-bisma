<?php
/**
 * Product archive filters shell (bar + chips + panel) — full markup for legacy callers.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_groups = ( isset( $args ) && is_array( $args ) && ! empty( $args['filter_groups'] ) && is_array( $args['filter_groups'] ) )
	? $args['filter_groups']
	: happywood_get_archive_filter_groups();

if ( empty( $filter_groups ) ) {
	return;
}

$desktop_orientation = happywood_get_filters_panel_desktop();
$mobile_orientation  = happywood_get_filters_panel_mobile();
$clear_url           = happywood_get_clear_catalog_filters_url();
?>
<div
	class="hw-product-filters"
	data-panel-desktop="<?php echo esc_attr( $desktop_orientation ); ?>"
	data-panel-mobile="<?php echo esc_attr( $mobile_orientation ); ?>"
	data-clear-url="<?php echo esc_url( $clear_url ); ?>"
>
	<?php
	get_template_part(
		'partials/product-filters',
		'chrome',
		array(
			'filter_groups' => $filter_groups,
		)
	);

	get_template_part(
		'partials/product-filters',
		'sidebar',
		array(
			'filter_groups' => $filter_groups,
		)
	);
	?>
</div>
