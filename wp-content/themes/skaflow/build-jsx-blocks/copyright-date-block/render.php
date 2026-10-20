<?php
$attributes = $attributes ?? array();

$block_props   = get_block_wrapper_attributes();
$starting_year = isset( $attributes['startingYear'] ) ? (string) $attributes['startingYear'] : '';
$current_year  = (string) gmdate( 'Y' );
$range_sep     = ( '' !== $starting_year && $starting_year < $current_year ) ? ' - ' : '';

printf(
	'
	<span %1$s>
		<span>Copyright &copy; %2$s%3$s%4$s</span>
	</span>',
	$block_props, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- get_block_wrapper_attributes().
	esc_html( $starting_year ),
	esc_html( $range_sep ),
	esc_html( $current_year )
);
