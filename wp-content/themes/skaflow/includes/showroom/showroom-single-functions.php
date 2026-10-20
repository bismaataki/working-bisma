<?php
/**
 * Showroom single page block renderers.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolve the showroom post ID for single-page blocks.
 *
 * @param \WP_Block|null $block Block instance.
 *
 * @return int
 */
function skaflow_resolve_showroom_single_post_id( $block = null ): int {
	return skaflow_resolve_showroom_card_post_id( $block );
}

/**
 * Google Maps directions URL for a showroom post.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return string
 */
function skaflow_get_showroom_directions_url_for_post( int $post_id ): string {
	$address = trim( (string) get_post_meta( $post_id, 'address', true ) );

	return skaflow_get_showroom_directions_url( $address );
}

/**
 * Editor-only empty state for showroom single blocks.
 *
 * @param int $post_id Requested post ID, if any.
 *
 * @return string
 */
function skaflow_render_showroom_single_empty_notice( int $post_id ): string {
	if ( $post_id > 0 && 'showrooms' !== get_post_type( $post_id ) ) {
		$message = __( 'This block only renders on showroom posts.', 'skaflow' );
	} elseif ( $post_id > 0 ) {
		$message = sprintf(
		/* translators: %d: showroom post ID */
			__( 'No showroom data is available for post ID %d.', 'skaflow' ),
			$post_id
		);
	} else {
		$message = __( 'Save this showroom post to preview showroom blocks.', 'skaflow' );
	}

	return sprintf(
		'<div class="sk-showroom-single-notice"><p>%s</p></div>',
		esc_html( $message )
	);
}

/**
 * Render block wrapper attributes.
 *
 * @param string         $class_name BEM block class.
 * @param \WP_Block|null $block Block instance.
 *
 * @return string
 */
function skaflow_get_showroom_single_wrapper_attributes( string $class_name, $block = null ): string {
	if ( $block instanceof \WP_Block ) {
		return get_block_wrapper_attributes(
			array(
				'class' => $class_name,
			),
			$block
		);
	}

	return 'class="' . esc_attr( $class_name ) . '"';
}

/**
 * Render the showroom status bar block.
 *
 * @param \WP_Block|null $block Block instance.
 *
 * @return string
 */
function skaflow_render_showroom_status_bar( $block = null ): string {
	$post_id  = skaflow_resolve_showroom_single_post_id( $block );
	$showroom = skaflow_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( skaflow_is_showroom_card_editor_preview() ) {
			return skaflow_render_showroom_single_empty_notice( $post_id );
		}

		return '';
	}

	$today        = skaflow_get_showroom_live_status( $post_id );
	$address      = trim( (string) ( $showroom['address'] ?? '' ) );
	$phone        = trim( (string) ( $showroom['phone'] ?? '' ) );
	$phone_href   = (string) ( $showroom['phone_href'] ?? '' );
	$timezone     = skaflow_get_showroom_location_timezone( $post_id );
	$schedule     = skaflow_get_showroom_opening_hours_schedule( $post_id );
	$labels       = skaflow_get_showroom_status_labels();
	$status_class = (string) ( $today['class'] ?? ( $today['is_open'] ? 'is-open-now' : 'is-closed-today' ) );

	$wrapper = skaflow_get_showroom_single_wrapper_attributes(
		'sk-showroom-status-bar sk-showroom-status-bar--' . sanitize_html_class( (string) ( $today['state'] ?? 'unknown' ) ),
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div
			class="sk-showroom-status-bar__pill sk-showroom-status-bar__pill--status <?php echo esc_attr( $status_class ); ?>"
			data-sk-showroom-hours-status
			data-timezone="<?php echo esc_attr( $timezone ); ?>"
			data-hours="<?php echo esc_attr( wp_json_encode( $schedule ) ); ?>"
			data-labels="<?php echo esc_attr( wp_json_encode( $labels ) ); ?>"
			data-day-labels="<?php echo esc_attr( wp_json_encode( skaflow_get_showroom_weekday_definitions() ) ); ?>"
		>
			<span class="sk-showroom-status-bar__icon sk-showroom-status-bar__icon--check" aria-hidden="true"></span>
			<span class="sk-showroom-status-bar__text" data-sk-showroom-hours-status-text><?php echo esc_html( (string) $today['label'] ); ?></span>
		</div>
		<?php if ( '' !== $address ) : ?>
			<div class="sk-showroom-status-bar__pill sk-showroom-status-bar__pill--address">
				<span class="sk-showroom-status-bar__icon sk-showroom-status-bar__icon--map-pin"
						aria-hidden="true"></span>
				<span class="sk-showroom-status-bar__text"><?php echo wp_kses( $address, SKAFLOW_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></span>
			</div>
		<?php endif; ?>
		<?php if ( '' !== $phone ) : ?>
			<?php
			$phone_tel_href = (string) $phone_href;

			if ( '' === $phone_tel_href ) {
				$digits         = preg_replace( '/[^\d+]/', '', $phone );
				$phone_tel_href = is_string( $digits ) && '' !== $digits ? 'tel:' . $digits : '';
			} elseif ( ! str_starts_with( $phone_tel_href, 'tel:' ) ) {
				$phone_tel_href = 'tel:' . $phone_tel_href;
			}
			?>
			<div class="sk-showroom-status-bar__pill sk-showroom-status-bar__pill--phone">
				<span class="sk-showroom-status-bar__icon sk-showroom-status-bar__icon--phone"
						aria-hidden="true"></span>
				<?php if ( '' !== $phone_tel_href ) : ?>
					<a class="sk-showroom-status-bar__text sk-showroom-status-bar__link"
						href="<?php echo esc_url( $phone_tel_href ); ?>"
						target="_blank"
						rel="noopener noreferrer"><?php echo esc_html( $phone ); ?></a>
				<?php else : ?>
					<span class="sk-showroom-status-bar__text"><?php echo esc_html( $phone ); ?></span>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the showroom location details block.
 *
 * @param \WP_Block|null $block Block instance.
 *
 * @return string
 */
function skaflow_render_showroom_location_details( $block = null ): string {
	$post_id  = skaflow_resolve_showroom_single_post_id( $block );
	$showroom = skaflow_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( skaflow_is_showroom_card_editor_preview() ) {
			return skaflow_render_showroom_single_empty_notice( $post_id );
		}

		return '';
	}

	$address          = trim( (string) ( $showroom['address'] ?? '' ) );
	$phone            = trim( (string) ( $showroom['phone'] ?? '' ) );
	$phone_href       = (string) ( $showroom['phone_href'] ?? '' );
	$email            = sanitize_email( (string) ( $showroom['email'] ?? '' ) );
	$time             = trim( (string) ( $showroom['time'] ?? '' ) );
	$entry_directions = trim( (string) get_post_meta( $post_id, '_description', true ) );
	$holiday_hours    = trim( (string) get_post_meta( $post_id, 'public_holiday_hours', true ) );
	$holiday_notes    = trim( (string) get_post_meta( $post_id, 'public_holiday_notes', true ) );
	$hours_rows       = skaflow_get_showroom_opening_hours_rows( $post_id );
	$has_hours        = false;

	foreach ( $hours_rows as $row ) {
		if ( ! $row['closed'] || '' !== trim( (string) $row['hours'] ) ) {
			$has_hours = true;
			break;
		}
	}

	$wrapper = skaflow_get_showroom_single_wrapper_attributes(
		'sk-showroom-location-details',
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="sk-showroom-location-details__grid">
			<div class="sk-showroom-location-details__contact">
				<?php if ( '' !== $address ) : ?>
					<div class="sk-showroom-location-details__item">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Full Address', 'skaflow' ); ?></p>
						<p class="sk-showroom-location-details__value"><?php echo wp_kses( $address, SKAFLOW_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $phone ) : ?>
					<div class="sk-showroom-location-details__item">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Phone', 'skaflow' ); ?></p>
						<p class="sk-showroom-location-details__value">
							<?php if ( '' !== $phone_href ) : ?>
								<a class="sk-showroom-location-details__link"
									href="<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phone ); ?>
							<?php endif; ?>
						</p>
						<?php if ( '' !== $time ) : ?>
							<p class="sk-showroom-location-details__meta"><?php echo wp_kses( $time, SKAFLOW_SHOWROOM_CARD_TIME_ALLOWED_HTML ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $email ) : ?>
					<div class="sk-showroom-location-details__item">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Email', 'skaflow' ); ?></p>
						<p class="sk-showroom-location-details__value">
							<a class="sk-showroom-location-details__link"
								href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
						</p>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $entry_directions ) : ?>
					<div class="sk-showroom-location-details__item">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Entry directions', 'skaflow' ); ?></p>
						<p class="sk-showroom-location-details__value"><?php echo esc_html( $entry_directions ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="sk-showroom-location-details__hours">
				<?php if ( $has_hours ) : ?>
					<div class="sk-showroom-location-details__item">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Opening Hours', 'skaflow' ); ?></p>
						<dl class="sk-showroom-location-details__hours-list">
							<?php foreach ( $hours_rows as $row ) : ?>
								<div class="sk-showroom-location-details__hours-row<?php echo $row['closed'] ? ' is-closed' : ''; ?>">
									<dt class="sk-showroom-location-details__hours-day"><?php echo esc_html( (string) $row['label'] ); ?></dt>
									<dd class="sk-showroom-location-details__hours-time"><?php echo esc_html( (string) $row['hours'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $holiday_hours || '' !== $holiday_notes ) : ?>
					<div class="sk-showroom-location-details__item sk-showroom-location-details__item--holidays">
						<p class="sk-showroom-location-details__label"><?php esc_html_e( 'Public Holidays', 'skaflow' ); ?></p>
						<?php if ( '' !== $holiday_hours ) : ?>
							<p class="sk-showroom-location-details__value"><?php echo esc_html( $holiday_hours ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $holiday_notes ) : ?>
							<p class="sk-showroom-location-details__meta"><?php echo esc_html( $holiday_notes ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
	return (string) ob_get_clean();
}

/**
 * Render the showroom map link block.
 *
 * @param \WP_Block|null $block Block instance.
 *
 * @return string
 */
function skaflow_render_showroom_map_link( $block = null ): string {
	$post_id  = skaflow_resolve_showroom_single_post_id( $block );
	$showroom = skaflow_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( skaflow_is_showroom_card_editor_preview() ) {
			return skaflow_render_showroom_single_empty_notice( $post_id );
		}

		return '';
	}

	$address        = trim( (string) ( $showroom['address'] ?? '' ) );
	$directions_url = skaflow_get_showroom_directions_url_for_post( $post_id );
	$map_url        = get_theme_file_uri( 'assets/images/showroom-locator-map.svg' );
	$wrapper        = skaflow_get_showroom_single_wrapper_attributes(
		'sk-showroom-map-link',
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="sk-showroom-map-link__visual" aria-hidden="true">
			<img class="sk-showroom-map-link__map-image" src="<?php echo esc_url( $map_url ); ?>" alt="" width="264"
				height="209" decoding="async"/>
			<span class="sk-showroom-map-link__pin"></span>
		</div>
		<?php if ( '' !== $address ) : ?>
			<p class="sk-showroom-map-link__address"><?php echo wp_kses( $address, SKAFLOW_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $directions_url ) : ?>
			<?php
			echo skaflow_get_buttons_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					array(
						'label'    => __( 'Open in Google Maps', 'skaflow' ),
						'url'      => $directions_url,
						'style'    => 'is-style-primary',
						'external' => true,
						'icon'     => 'map-pin',
					),
				),
				array(
					'wrapper_class' => 'sk-showroom-map-link__actions',
					'layout'        => array(
						'type'           => 'flex',
						'justifyContent' => 'center',
					),
				)
			);
			?>
		<?php endif; ?>
	</div>
	<?php
	return (string) ob_get_clean();
}
