<?php
/**
 * Product archive filter sidebar (panel host).
 *
 * @package Happywood
 *
 * @var array<int, array<string, mixed>> $filter_groups Filter groups from happywood_get_archive_filter_groups().
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
?>
<aside class="hw-shop-archive-sidebar" aria-label="<?php esc_attr_e( 'Product filters', 'happywood' ); ?>">
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
