<?php
/**
 * Product archive filter bar buttons.
 *
 * @package Skaflow
 *
 * @var array<int, array<string, mixed>> $filter_groups Filter groups from skaflow_get_archive_filter_groups().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_groups = $filter_groups ?? skaflow_get_archive_filter_groups();

if ( empty( $filter_groups ) ) {
	return;
}

$bar_buttons = array();

foreach ( $filter_groups as $group ) {
	$slug  = (string) ( $group['slug'] ?? '' );
	$label = (string) ( $group['label'] ?? $slug );

	if ( '' === $slug ) {
		continue;
	}

	$applied_count   = skaflow_get_filter_group_applied_count( $group );
	$element_class   = 'sk-product-filters__bar-button';
	$html_attributes = array(
		'data-filter-card' => $slug,
		'aria-expanded'    => 'false',
		'aria-controls'    => 'sk-filter-card-' . $slug,
	);

	if ( $applied_count > 0 ) {
		$element_class .= ' sk-product-filters__bar-button--applied';
	}

	$bar_buttons[] = array(
		'label'           => $label,
		'tag'             => 'button',
		'style'           => 'is-style-light',
		'icon'            => skaflow_get_filter_group_icon_slug( $slug ),
		'element_class'   => $element_class,
		'html_attributes' => $html_attributes,
	);
}
?>
<div class="sk-product-filters__bar">
	<div class="sk-product-filters__bar-scroll sk-scrollbar" role="toolbar" aria-label="<?php esc_attr_e( 'Product filters', 'skaflow' ); ?>">
		<?php
		echo skaflow_get_buttons_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns do_blocks() HTML.
			$bar_buttons,
			array(
				'wrapper_class' => 'sk-product-filters__bar-buttons',
				'default_style' => 'is-style-light',
				'layout'        => array(
					'type'     => 'flex',
					'flexWrap' => 'nowrap',
				),
			)
		);
		?>
	</div>
</div>
