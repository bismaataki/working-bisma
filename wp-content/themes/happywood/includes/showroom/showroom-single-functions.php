<?php
/**
 * Showroom single page block renderers.
 *
 * @package Happywood
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
function happywood_resolve_showroom_single_post_id( $block = null ): int {
	return happywood_resolve_showroom_card_post_id( $block );
}

/**
 * Google Maps directions URL for a showroom post.
 *
 * @param int $post_id Showroom post ID.
 *
 * @return string
 */
function happywood_get_showroom_directions_url_for_post( int $post_id ): string {
	$address = trim( (string) get_post_meta( $post_id, 'address', true ) );

	return happywood_get_showroom_directions_url( $address );
}

/**
 * Editor-only empty state for showroom single blocks.
 *
 * @param int $post_id Requested post ID, if any.
 *
 * @return string
 */
function happywood_render_showroom_single_empty_notice( int $post_id ): string {
	if ( $post_id > 0 && 'showrooms' !== get_post_type( $post_id ) ) {
		$message = __( 'This block only renders on showroom posts.', 'happywood' );
	} elseif ( $post_id > 0 ) {
		$message = sprintf(
		/* translators: %d: showroom post ID */
			__( 'No showroom data is available for post ID %d.', 'happywood' ),
			$post_id
		);
	} else {
		$message = __( 'Save this showroom post to preview showroom blocks.', 'happywood' );
	}

	return sprintf(
		'<div class="hw-showroom-single-notice"><p>%s</p></div>',
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
function happywood_get_showroom_single_wrapper_attributes( string $class_name, $block = null ): string {
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
function happywood_render_showroom_status_bar( $block = null ): string {
	$post_id  = happywood_resolve_showroom_single_post_id( $block );
	$showroom = happywood_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( happywood_is_showroom_card_editor_preview() ) {
			return happywood_render_showroom_single_empty_notice( $post_id );
		}

		return '';
	}

	$today        = happywood_get_showroom_live_status( $post_id );
	$address      = trim( (string) ( $showroom['address'] ?? '' ) );
	$phone        = trim( (string) ( $showroom['phone'] ?? '' ) );
	$phone_href   = (string) ( $showroom['phone_href'] ?? '' );
	$timezone     = happywood_get_showroom_location_timezone( $post_id );
	$schedule     = happywood_get_showroom_opening_hours_schedule( $post_id );
	$labels       = happywood_get_showroom_status_labels();
	$status_class = (string) ( $today['class'] ?? ( $today['is_open'] ? 'is-open-now' : 'is-closed-today' ) );

	$wrapper = happywood_get_showroom_single_wrapper_attributes(
		'hw-showroom-status-bar hw-showroom-status-bar--' . sanitize_html_class( (string) ( $today['state'] ?? 'unknown' ) ),
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div
			class="hw-showroom-status-bar__pill hw-showroom-status-bar__pill--status <?php echo esc_attr( $status_class ); ?>"
			data-hw-showroom-hours-status
			data-timezone="<?php echo esc_attr( $timezone ); ?>"
			data-hours="<?php echo esc_attr( wp_json_encode( $schedule ) ); ?>"
			data-labels="<?php echo esc_attr( wp_json_encode( $labels ) ); ?>"
			data-day-labels="<?php echo esc_attr( wp_json_encode( happywood_get_showroom_weekday_definitions() ) ); ?>"
		>
			<span class="hw-showroom-status-bar__icon hw-showroom-status-bar__icon--check" aria-hidden="true"></span>
			<span class="hw-showroom-status-bar__text" data-hw-showroom-hours-status-text><?php echo esc_html( (string) $today['label'] ); ?></span>
		</div>
		<?php if ( '' !== $address ) : ?>
			<div class="hw-showroom-status-bar__pill hw-showroom-status-bar__pill--address">
				<span class="hw-showroom-status-bar__icon hw-showroom-status-bar__icon--map-pin"
						aria-hidden="true"></span>
				<span class="hw-showroom-status-bar__text"><?php echo wp_kses( $address, HAPPYWOOD_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></span>
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
			<div class="hw-showroom-status-bar__pill hw-showroom-status-bar__pill--phone">
				<span class="hw-showroom-status-bar__icon hw-showroom-status-bar__icon--phone"
						aria-hidden="true"></span>
				<?php if ( '' !== $phone_tel_href ) : ?>
					<a class="hw-showroom-status-bar__text hw-showroom-status-bar__link"
						href="<?php echo esc_url( $phone_tel_href ); ?>"
						target="_blank"
						rel="noopener noreferrer"><?php echo esc_html( $phone ); ?></a>
				<?php else : ?>
					<span class="hw-showroom-status-bar__text"><?php echo esc_html( $phone ); ?></span>
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
function happywood_render_showroom_location_details( $block = null ): string {
	$post_id  = happywood_resolve_showroom_single_post_id( $block );
	$showroom = happywood_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( happywood_is_showroom_card_editor_preview() ) {
			return happywood_render_showroom_single_empty_notice( $post_id );
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
	$hours_rows       = happywood_get_showroom_opening_hours_rows( $post_id );
	$has_hours        = false;

	foreach ( $hours_rows as $row ) {
		if ( ! $row['closed'] || '' !== trim( (string) $row['hours'] ) ) {
			$has_hours = true;
			break;
		}
	}

	$wrapper = happywood_get_showroom_single_wrapper_attributes(
		'hw-showroom-location-details',
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="hw-showroom-location-details__grid">
			<div class="hw-showroom-location-details__contact">
				<?php if ( '' !== $address ) : ?>
					<div class="hw-showroom-location-details__item">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Full Address', 'happywood' ); ?></p>
						<p class="hw-showroom-location-details__value"><?php echo wp_kses( $address, HAPPYWOOD_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></p>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $phone ) : ?>
					<div class="hw-showroom-location-details__item">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Phone', 'happywood' ); ?></p>
						<p class="hw-showroom-location-details__value">
							<?php if ( '' !== $phone_href ) : ?>
								<a class="hw-showroom-location-details__link"
									href="<?php echo esc_attr( $phone_href ); ?>"><?php echo esc_html( $phone ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $phone ); ?>
							<?php endif; ?>
						</p>
						<?php if ( '' !== $time ) : ?>
							<p class="hw-showroom-location-details__meta"><?php echo wp_kses( $time, HAPPYWOOD_SHOWROOM_CARD_TIME_ALLOWED_HTML ); ?></p>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $email ) : ?>
					<div class="hw-showroom-location-details__item">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Email', 'happywood' ); ?></p>
						<p class="hw-showroom-location-details__value">
							<a class="hw-showroom-location-details__link"
								href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
						</p>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $entry_directions ) : ?>
					<div class="hw-showroom-location-details__item">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Entry directions', 'happywood' ); ?></p>
						<p class="hw-showroom-location-details__value"><?php echo esc_html( $entry_directions ); ?></p>
					</div>
				<?php endif; ?>
			</div>
			<div class="hw-showroom-location-details__hours">
				<?php if ( $has_hours ) : ?>
					<div class="hw-showroom-location-details__item">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Opening Hours', 'happywood' ); ?></p>
						<dl class="hw-showroom-location-details__hours-list">
							<?php foreach ( $hours_rows as $row ) : ?>
								<div class="hw-showroom-location-details__hours-row<?php echo $row['closed'] ? ' is-closed' : ''; ?>">
									<dt class="hw-showroom-location-details__hours-day"><?php echo esc_html( (string) $row['label'] ); ?></dt>
									<dd class="hw-showroom-location-details__hours-time"><?php echo esc_html( (string) $row['hours'] ); ?></dd>
								</div>
							<?php endforeach; ?>
						</dl>
					</div>
				<?php endif; ?>
				<?php if ( '' !== $holiday_hours || '' !== $holiday_notes ) : ?>
					<div class="hw-showroom-location-details__item hw-showroom-location-details__item--holidays">
						<p class="hw-showroom-location-details__label"><?php esc_html_e( 'Public Holidays', 'happywood' ); ?></p>
						<?php if ( '' !== $holiday_hours ) : ?>
							<p class="hw-showroom-location-details__value"><?php echo esc_html( $holiday_hours ); ?></p>
						<?php endif; ?>
						<?php if ( '' !== $holiday_notes ) : ?>
							<p class="hw-showroom-location-details__meta"><?php echo esc_html( $holiday_notes ); ?></p>
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
function happywood_render_showroom_map_link( $block = null ): string {
	$post_id  = happywood_resolve_showroom_single_post_id( $block );
	$showroom = happywood_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( happywood_is_showroom_card_editor_preview() ) {
			return happywood_render_showroom_single_empty_notice( $post_id );
		}

		return '';
	}

	$address        = trim( (string) ( $showroom['address'] ?? '' ) );
	$directions_url = happywood_get_showroom_directions_url_for_post( $post_id );
	$map_url        = get_theme_file_uri( 'assets/images/showroom-locator-map.svg' );
	$wrapper        = happywood_get_showroom_single_wrapper_attributes(
		'hw-showroom-map-link',
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<div class="hw-showroom-map-link__visual" aria-hidden="true">
			<img class="hw-showroom-map-link__map-image" src="<?php echo esc_url( $map_url ); ?>" alt="" width="264"
				height="209" decoding="async"/>
			<span class="hw-showroom-map-link__pin"></span>
		</div>
		<?php if ( '' !== $address ) : ?>
			<p class="hw-showroom-map-link__address"><?php echo wp_kses( $address, HAPPYWOOD_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML ); ?></p>
		<?php endif; ?>
		<?php if ( '' !== $directions_url ) : ?>
			<?php
			echo happywood_get_buttons_markup( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					array(
						'label'    => __( 'Open in Google Maps', 'happywood' ),
						'url'      => $directions_url,
						'style'    => 'is-style-primary',
						'external' => true,
						'icon'     => 'map-pin',
					),
				),
				array(
					'wrapper_class' => 'hw-showroom-map-link__actions',
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
