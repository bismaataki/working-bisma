<?php
/**
 * Product archive filter sidebar (panel host).
 *
 * @package Skaflow
 *
 * @var array<int, array<string, mixed>> $filter_groups Filter groups from skaflow_get_archive_filter_groups().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_groups = ( isset( $args ) && is_array( $args ) && ! empty( $args['filter_groups'] ) && is_array( $args['filter_groups'] ) )
	? $args['filter_groups']
	: skaflow_get_archive_filter_groups();

if ( empty( $filter_groups ) ) {
	return;
}
?>
<aside class="sk-shop-archive-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'skaflow' ); ?>">
	<?php
	get_template_part(
		'partials/product-filters',
		'panel',
		array(
			'filter_groups' => $filter_groups,
		)
	);
	?>
</aside>
