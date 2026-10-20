<?php
/**
 * Showroom opening hours, timezone, and live status.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Weekday keys in Monday-first display order.
 *
 * @return array<string, string> slug => translated label.
 */
function skaflow_get_showroom_weekday_definitions(): array {
	return array(
		'monday'    => __( 'Monday', 'skaflow' ),
		'tuesday'   => __( 'Tuesday', 'skaflow' ),
		'wednesday' => __( 'Wednesday', 'skaflow' ),
		'thursday'  => __( 'Thursday', 'skaflow' ),
		'friday'    => __( 'Friday', 'skaflow' ),
		'saturday'  => __( 'Saturday', 'skaflow' ),
		'sunday'    => __( 'Sunday', 'skaflow' ),
	);
}

/**
 * Read raw opening hours group meta for a showroom post.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return array<string, array<string, mixed>>
 */
function skaflow_get_showroom_opening_hours_raw( int $post_id ): array {
	if ( $post_id <= 0 ) {
		return array();
	}

	if ( function_exists( 'get_field' ) ) {
		$hours = get_field( 'opening_hours', $post_id );
	} else {
		$hours = get_post_meta( $post_id, 'opening_hours', true );
	}

	return is_array( $hours ) ? $hours : array();
}

/**
 * Format a stored showroom time for display (Figma-style, e.g. 9:00am).
 *
 * Accepts legacy free-text values and ACF time_picker H:i:s values.
 *
 * @param string $value Raw opens/closes value.
 *
 * @return string
 */
function skaflow_format_showroom_time_value( string $value ): string {
	$value = trim( $value );

	if ( '' === $value ) {
		return '';
	}

	if ( preg_match( '/[ap]m/i', $value ) ) {
		return $value;
	}

	$timezone = wp_timezone();
	$formats  = array( 'H:i:s', 'H:i', 'G:i:s', 'G:i' );

	foreach ( $formats as $format ) {
		$date_time = \DateTimeImmutable::createFromFormat( $format, $value, $timezone );

		if ( $date_time instanceof \DateTimeImmutable ) {
			return wp_date( 'g:ia', $date_time->getTimestamp() );
		}
	}

	return $value;
}

/**
 * Format a single day's opening hours for display.
 *
 * Prefers separate opens/closes fields; falls back to legacy combined `hours`.
 *
 * @param array<string, mixed> $day Day group from opening_hours.
 *
 * @return string Empty when closed or no times are available.
 */
function skaflow_format_showroom_day_hours( array $day ): string {
	if ( ! empty( $day['closed'] ) ) {
		return '';
	}

	$opens  = skaflow_format_showroom_time_value( (string) ( $day['opens'] ?? '' ) );
	$closes = skaflow_format_showroom_time_value( (string) ( $day['closes'] ?? '' ) );

	if ( '' !== $opens && '' !== $closes ) {
		return $opens . ' – ' . $closes;
	}

	if ( '' !== $opens || '' !== $closes ) {
		return trim( $opens . ' ' . $closes );
	}

	return trim( (string) ( $day['hours'] ?? '' ) );
}

/**
 * Default opening hours per weekday (Figma / Sydney showroom reference).
 *
 * @return array<string, array{opens: string, closes: string, closed: bool}>
 */
function skaflow_get_showroom_opening_hours_day_defaults(): array {
	return array(
		'monday'    => array(
			'opens'  => '09:00:00',
			'closes' => '18:00:00',
			'closed' => false,
		),
		'tuesday'   => array(
			'opens'  => '09:00:00',
			'closes' => '18:00:00',
			'closed' => false,
		),
		'wednesday' => array(
			'opens'  => '09:00:00',
			'closes' => '18:00:00',
			'closed' => false,
		),
		'thursday'  => array(
			'opens'  => '09:00:00',
			'closes' => '20:00:00',
			'closed' => false,
		),
		'friday'    => array(
			'opens'  => '09:00:00',
			'closes' => '18:00:00',
			'closed' => false,
		),
		'saturday'  => array(
			'opens'  => '10:00:00',
			'closes' => '17:00:00',
			'closed' => false,
		),
		'sunday'    => array(
			'opens'  => '10:00:00',
			'closes' => '17:00:00',
			'closed' => false,
		),
	);
}

/**
 * Merge saved day data with defaults when a day has not been configured yet.
 *
 * @param array<string, mixed> $day Saved day group.
 * @param string               $slug Weekday slug.
 *
 * @return array<string, mixed>
 */
function skaflow_resolve_showroom_day_hours( array $day, string $slug ): array {
	$resolved = wp_parse_args(
		$day,
		array(
			'opens'  => '',
			'closes' => '',
			'hours'  => '',
			'closed' => false,
		)
	);

	if ( ! empty( $resolved['closed'] ) ) {
		return $resolved;
	}

	if ( '' !== skaflow_format_showroom_day_hours( $resolved ) ) {
		return $resolved;
	}

	$defaults = skaflow_get_showroom_opening_hours_day_defaults();

	return isset( $defaults[ $slug ] )
			? wp_parse_args( $resolved, $defaults[ $slug ] )
			: $resolved;
}

/**
 * Normalised Mon–Sun opening hours rows for table display.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return array<int, array{slug: string, label: string, hours: string, closed: bool}>
 */
function skaflow_get_showroom_opening_hours_rows( int $post_id ): array {
	$raw  = skaflow_get_showroom_opening_hours_raw( $post_id );
	$rows = array();

	foreach ( skaflow_get_showroom_weekday_definitions() as $slug => $label ) {
		$day     = isset( $raw[ $slug ] ) && is_array( $raw[ $slug ] ) ? $raw[ $slug ] : array();
		$day     = skaflow_resolve_showroom_day_hours( $day, $slug );
		$closed  = ! empty( $day['closed'] );
		$hours   = skaflow_format_showroom_day_hours( $day );
		$display = $closed ? __( 'Closed', 'skaflow' ) : $hours;

		$rows[] = array(
			'slug'   => $slug,
			'label'  => $label,
			'hours'  => $display,
			'closed' => $closed || '' === $hours,
		);
	}

	return $rows;
}

/**
 * Current weekday slug in the site timezone (monday–sunday).
 *
 * @return string
 */
function skaflow_get_showroom_current_weekday_slug(): string {
	$slug = strtolower( wp_date( 'l' ) );

	return array_key_exists( $slug, skaflow_get_showroom_weekday_definitions() )
			? $slug
			: 'monday';
}

/**
 * Primary showroom-locations term for a showroom post.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return WP_Term|null
 */
function skaflow_get_showroom_location_term( int $post_id ): ?WP_Term {
	if ( $post_id <= 0 ) {
		return null;
	}

	$terms = get_the_terms( $post_id, 'showroom-locations' );

	if ( ! is_array( $terms ) || empty( $terms ) ) {
		return null;
	}

	foreach ( $terms as $term ) {
		if ( $term instanceof WP_Term ) {
			return $term;
		}
	}

	return null;
}

/**
 * IANA timezone map keyed by showroom-locations term slug.
 *
 * Used only when the taxonomy term has no ACF timezone saved. The ACF field is
 * the real answer; this is convenience for a site whose location terms happen to
 * match the slugs below.
 *
 * **The entries are an example, not a contract.** They cover one country because
 * that is where this map was first needed. A project in another region should
 * filter the whole thing rather than edit theme code:
 *
 *     add_filter(
 *         'skaflow_showroom_location_timezone_map',
 *         fn() => array( 'london' => 'Europe/London' )
 *     );
 *
 * A project whose showrooms are all in one timezone can ignore this entirely and
 * let the default below use the WordPress site timezone.
 *
 * @return array<string, string>
 */
function skaflow_get_showroom_location_timezone_map(): array {
	/**
	 * Filters the showroom-location term slug to IANA timezone map.
	 *
	 * @param array<string, string> $map Term slug => IANA timezone identifier.
	 */
	return (array) apply_filters(
		'skaflow_showroom_location_timezone_map',
		array(
			'new-south-wales'              => 'Australia/Sydney',
			'queensland'                   => 'Australia/Brisbane',
			'victoria'                     => 'Australia/Melbourne',
			'south-australia'              => 'Australia/Adelaide',
			'western-australia'            => 'Australia/Perth',
			'tasmania'                     => 'Australia/Hobart',
			'australian-capital-territory' => 'Australia/Sydney',
			'northern-territory'           => 'Australia/Darwin',
			'new-zealand'                  => 'Pacific/Auckland',
		)
	);
}

/**
 * Default IANA timezone when a showroom has no location term or unknown slug.
 *
 * Falls back to the WordPress site timezone rather than a hardcoded region, so a
 * fresh project reports correct opening hours before any showroom is configured.
 *
 * @return string
 */
function skaflow_get_showroom_location_timezone_default(): string {
	$site_timezone = wp_timezone_string();

	// wp_timezone_string() can return a UTC offset like "+10:00", which is not a
	// valid IANA identifier for DateTimeZone-based hour calculations.
	if ( '' !== $site_timezone && ! preg_match( '/^[+-]\d{2}:\d{2}$/', $site_timezone ) ) {
		return $site_timezone;
	}

	return 'UTC';
}

/**
 * Resolve the IANA timezone for a showroom from its location term.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return string
 */
function skaflow_get_showroom_location_timezone( int $post_id ): string {
	$term = skaflow_get_showroom_location_term( $post_id );

	if ( ! $term instanceof WP_Term ) {
		return skaflow_get_showroom_location_timezone_default();
	}

	if ( function_exists( 'get_field' ) ) {
		$timezone = get_field( 'timezone', $term );

		if ( is_string( $timezone ) && '' !== trim( $timezone ) ) {
			return trim( $timezone );
		}
	}

	$map = skaflow_get_showroom_location_timezone_map();

	return $map[ $term->slug ] ?? skaflow_get_showroom_location_timezone_default();
}

/**
 * Normalise a stored opens/closes value to 24-hour HH:MM for schedule logic.
 *
 * @param string $value Raw ACF time value.
 *
 * @return string Empty when parsing fails.
 */
function skaflow_normalize_showroom_time_for_schedule( string $value ): string {
	$value = trim( $value );

	if ( '' === $value ) {
		return '';
	}

	if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $value, $matches ) ) {
		return sprintf( '%02d:%02d', (int) $matches[1], (int) $matches[2] );
	}

	$parsed = DateTimeImmutable::createFromFormat( 'g:ia', str_replace( ' ', '', strtolower( $value ) ) );

	if ( ! $parsed instanceof DateTimeImmutable ) {
		$parsed = DateTimeImmutable::createFromFormat( 'g:i a', $value );
	}

	return $parsed instanceof DateTimeImmutable ? $parsed->format( 'H:i' ) : '';
}

/**
 * Convert HH:MM to minutes since midnight.
 *
 * @param string $value 24-hour time string.
 *
 * @return int|null
 */
function skaflow_showroom_time_string_to_minutes( string $value ): ?int {
	$value = skaflow_normalize_showroom_time_for_schedule( $value );

	if ( '' === $value || ! preg_match( '/^(\d{2}):(\d{2})$/', $value, $matches ) ) {
		return null;
	}

	return ( (int) $matches[1] * 60 ) + (int) $matches[2];
}

/**
 * Format a schedule range for display (Figma-style, e.g. 9:00am – 5:00pm).
 *
 * @param string $opens  HH:MM.
 * @param string $closes HH:MM.
 *
 * @return string
 */
function skaflow_format_showroom_schedule_range( string $opens, string $closes ): string {
	$opens_display  = skaflow_format_showroom_time_value( $opens );
	$closes_display = skaflow_format_showroom_time_value( $closes );

	if ( '' === $opens_display || '' === $closes_display ) {
		return '';
	}

	return $opens_display . ' – ' . $closes_display;
}

/**
 * Weekly opening schedule keyed by weekday slug for JS status resolution.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return array<string, array{opens: string, closes: string}|null>
 */
function skaflow_get_showroom_opening_hours_schedule( int $post_id ): array {
	$raw      = skaflow_get_showroom_opening_hours_raw( $post_id );
	$schedule = array();

	foreach ( array_keys( skaflow_get_showroom_weekday_definitions() ) as $slug ) {
		$day = isset( $raw[ $slug ] ) && is_array( $raw[ $slug ] ) ? $raw[ $slug ] : array();
		$day = skaflow_resolve_showroom_day_hours( $day, $slug );

		if ( ! empty( $day['closed'] ) ) {
			$schedule[ $slug ] = null;
			continue;
		}

		$opens  = skaflow_normalize_showroom_time_for_schedule( (string) ( $day['opens'] ?? '' ) );
		$closes = skaflow_normalize_showroom_time_for_schedule( (string) ( $day['closes'] ?? '' ) );

		if ( '' === $opens || '' === $closes ) {
			$schedule[ $slug ] = null;
			continue;
		}

		$schedule[ $slug ] = array(
			'opens'  => $opens,
			'closes' => $closes,
		);
	}

	return $schedule;
}

/**
 * Translatable status pill labels for server + client rendering.
 *
 * @return array<string, string>
 */
function skaflow_get_showroom_status_labels(): array {
	return array(
		'open_now'              => __( 'Open now · %s', 'skaflow' ),
		'opens_today'           => __( 'Opens today · %s', 'skaflow' ),
		'closed_now'            => __( 'Closed now · %s', 'skaflow' ),
		'closed_now_opens_next' => __( 'Closed now · Opens %s', 'skaflow' ),
		'closed_today'          => __( 'Closed today', 'skaflow' ),
		'hours_unavailable'     => __( 'Hours unavailable', 'skaflow' ),
	);
}

/**
 * Next opening day/time after the given moment, for status-bar copy.
 *
 * @param array<string, array{opens: string, closes: string}|null> $schedule Weekly schedule.
 * @param DateTimeImmutable                                        $from     Reference moment in showroom timezone.
 *
 * @return string Day name + open time, e.g. "Monday 10:00am", or empty when none found.
 */
function skaflow_get_showroom_next_opening_display( array $schedule, DateTimeImmutable $from ): string {
	$definitions = skaflow_get_showroom_weekday_definitions();

	for ( $offset = 1; $offset <= 7; $offset++ ) {
		$candidate = $from->modify( '+' . $offset . ' days' );
		$slug      = strtolower( $candidate->format( 'l' ) );
		$day       = $schedule[ $slug ] ?? null;

		if ( null === $day ) {
			continue;
		}

		$opens = trim( (string) ( $day['opens'] ?? '' ) );

		if ( '' === $opens ) {
			continue;
		}

		$day_label  = $definitions[ $slug ] ?? ucfirst( $slug );
		$time_label = skaflow_format_showroom_time_value( $opens );

		return trim( $day_label . ' ' . $time_label );
	}

	return '';
}

/**
 * Build a normalised live status payload.
 *
 * @param string $state  open_now|opens_today|closed_now|closed_today|unavailable.
 * @param array  $labels Label templates from skaflow_get_showroom_status_labels().
 * @param string $range  Display range for today, when applicable.
 *
 * @return array{state: string, label: string, hours: string, is_open: bool, class: string}
 */
function skaflow_build_showroom_status_result( string $state, array $labels, string $range = '' ): array {
	switch ( $state ) {
		case 'open_now':
			return array(
				'state'   => 'open_now',
				'label'   => sprintf( $labels['open_now'], $range ),
				'hours'   => $range,
				'is_open' => true,
				'class'   => 'is-open-now',
			);
		case 'opens_today':
			return array(
				'state'   => 'opens_today',
				'label'   => sprintf( $labels['opens_today'], $range ),
				'hours'   => $range,
				'is_open' => false,
				'class'   => 'is-opens-today',
			);
		case 'closed_now':
			return array(
				'state'   => 'closed_now',
				'label'   => sprintf( $labels['closed_now'], $range ),
				'hours'   => $range,
				'is_open' => false,
				'class'   => 'is-closed-now',
			);
		case 'closed_today':
			return array(
				'state'   => 'closed_today',
				'label'   => $labels['closed_today'],
				'hours'   => '',
				'is_open' => false,
				'class'   => 'is-closed-today',
			);
		default:
			return array(
				'state'   => 'unavailable',
				'label'   => $labels['hours_unavailable'],
				'hours'   => '',
				'is_open' => false,
				'class'   => 'is-unavailable',
			);
	}
}

/**
 * Live open/closed status for the status-bar pill in the showroom timezone.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return array{state: string, label: string, hours: string, is_open: bool, class: string}
 */
function skaflow_get_showroom_live_status( int $post_id ): array {
	$labels   = skaflow_get_showroom_status_labels();
	$schedule = skaflow_get_showroom_opening_hours_schedule( $post_id );
	$timezone = skaflow_get_showroom_location_timezone( $post_id );

	try {
		$now = new DateTimeImmutable( 'now', new DateTimeZone( $timezone ) );
	} catch ( Exception $exception ) {
		unset( $exception );

		return skaflow_build_showroom_status_result( 'unavailable', $labels );
	}

	$slug  = strtolower( $now->format( 'l' ) );
	$today = $schedule[ $slug ] ?? null;

	if ( null === $today ) {
		return skaflow_build_showroom_status_result( 'closed_today', $labels );
	}

	$opens_min  = skaflow_showroom_time_string_to_minutes( $today['opens'] );
	$closes_min = skaflow_showroom_time_string_to_minutes( $today['closes'] );

	if ( null === $opens_min || null === $closes_min ) {
		return skaflow_build_showroom_status_result( 'unavailable', $labels );
	}

	$now_min = ( (int) $now->format( 'H' ) * 60 ) + (int) $now->format( 'i' );
	$range   = skaflow_format_showroom_schedule_range( $today['opens'], $today['closes'] );

	if ( $now_min >= $opens_min && $now_min < $closes_min ) {
		return skaflow_build_showroom_status_result( 'open_now', $labels, $range );
	}

	if ( $now_min < $opens_min ) {
		return skaflow_build_showroom_status_result( 'opens_today', $labels, $range );
	}

	$next_open = skaflow_get_showroom_next_opening_display( $schedule, $now );

	if ( '' !== $next_open ) {
		return array(
			'state'   => 'closed_now',
			'label'   => sprintf( $labels['closed_now_opens_next'], $next_open ),
			'hours'   => $range,
			'is_open' => false,
			'class'   => 'is-closed-now',
		);
	}

	return skaflow_build_showroom_status_result( 'closed_now', $labels, $range );
}

/**
 * Today status for the status-bar pill.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return array{is_open: bool, label: string, hours: string, state?: string, class?: string}
 */
function skaflow_get_showroom_today_status( int $post_id ): array {
	$live = skaflow_get_showroom_live_status( $post_id );

	if ( 'unavailable' !== $live['state'] ) {
		return $live;
	}

	$legacy = trim( (string) get_post_meta( $post_id, 'time', true ) );

	if ( '' !== $legacy ) {
		$plain_legacy = trim( wp_strip_all_tags( $legacy ) );

		return array(
			'state'   => 'open_now',
			'label'   => sprintf(
			/* translators: %s: opening hours string */
				__( 'Open now · %s', 'skaflow' ),
				$plain_legacy
			),
			'hours'   => $plain_legacy,
			'is_open' => true,
			'class'   => 'is-open-now',
		);
	}

	return $live;
}
