<?php
/**
 * Active catalog filter chips.
 *
 * @package Skaflow
 *
 * @var string $context Display context: `chrome` (bar area) or `panel` (filter drawer).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$context   = isset( $args['context'] ) ? (string) $args['context'] : 'chrome';
$chips     = skaflow_get_active_filter_chips();
$clear_url = skaflow_get_clear_catalog_filters_url();
$is_empty  = empty( $chips );

// Panel drawer: omit entirely when empty. Chrome bar: always render a stable slot.
if ( $is_empty && 'panel' === $context ) {
	return;
}

$wrapper_class = 'sk-active-filters';

if ( 'panel' === $context ) {
	$wrapper_class .= ' sk-active-filters--panel';
} else {
	$wrapper_class .= ' sk-active-filters--chrome';

	if ( $is_empty ) {
		$wrapper_class .= ' is-empty';
	}
}

$chip_buttons = array();

foreach ( $chips as $chip ) {
	$chip_label = (string) ( $chip['label'] ?? '' );

	if ( '' === $chip_label ) {
		continue;
	}

	$html_attributes = array(
		'data-filter-name' => (string) ( $chip['filter_name'] ?? '' ),
		'data-remove-url'  => (string) ( $chip['remove_url'] ?? '' ),
		'aria-label'       => sprintf(
			/* translators: %s: active filter label */
			__( 'Remove filter: %s', 'skaflow' ),
			$chip_label
		),
	);

	if ( ! empty( $chip['term_slug'] ) ) {
		$html_attributes['data-term-slug'] = (string) $chip['term_slug'];
	}

	$chip_buttons[] = array(
		'label'           => $chip_label,
		'tag'             => 'button',
		'style'           => 'is-style-light',
		'icon'            => 'x',
		'icon_position'   => 'right',
		'element_class'   => 'sk-active-filters__chip',
		'html_attributes' => $html_attributes,
	);
}
?>
<div
	class="<?php echo esc_attr( $wrapper_class ); ?>"
	<?php if ( $is_empty && 'chrome' === $context ) : ?>
		aria-hidden="true"
	<?php endif; ?>
>
	<div class="sk-active-filters__inner">
		<?php if ( 'chrome' === $context ) : ?>
			<p class="sk-active-filters__label"><?php esc_html_e( 'Active Filters:', 'skaflow' ); ?></p>
		<?php endif; ?>
		<?php if ( ! $is_empty ) : ?>
			<?php
			echo skaflow_get_buttons_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- helper returns do_blocks() HTML.
				$chip_buttons,
				array(
					'wrapper_class' => 'sk-active-filters__chips',
					'default_style' => 'is-style-light',
					'layout'        => array(
						'type'     => 'flex',
						'flexWrap' => 'wrap',
					),
				)
			);
			?>
			<?php if ( $clear_url ) : ?>
				<button
					type="button"
					class="sk-active-filters__clear"
					data-clear-url="<?php echo esc_url( $clear_url ); ?>"
				>
					<?php esc_html_e( 'Clear all', 'skaflow' ); ?>
				</button>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</div>
