<?php
/**
 * Showroom Contact Details block helpers.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Happywood\Showroom_Locator;

/**
 * Resolve mapped showrooms for the contact-details block.
 *
 * Empty selection returns all mapped showrooms in menu order.
 * When IDs are provided, order follows the saved attribute array.
 *
 * @param array<int, array<string, mixed>> $selected Selected showroom entries from block attributes.
 * @return array<int, array<string, mixed>>
 */
function happywood_get_showroom_contact_details_items( array $selected = array() ): array {
	$all = Showroom_Locator::get_showrooms();

	if ( empty( $all ) ) {
		return array();
	}

	if ( empty( $selected ) ) {
		return array_values( $all );
	}

	$by_id = array();

	foreach ( $all as $showroom ) {
		$post_id = absint( $showroom['post_id'] ?? 0 );

		if ( $post_id > 0 ) {
			$by_id[ $post_id ] = $showroom;
		}
	}

	$items = array();

	foreach ( $selected as $entry ) {
		if ( ! is_array( $entry ) ) {
			continue;
		}

		$post_id = absint( $entry['id'] ?? 0 );

		if ( $post_id > 0 && isset( $by_id[ $post_id ] ) ) {
			$items[] = $by_id[ $post_id ];
		}
	}

	return $items;
}

/**
 * Parse showroom-card attribute overrides from block attributes.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return array<string, mixed>
 */
function happywood_parse_showroom_contact_details_card_args( array $attributes ): array {
	$cta_type = happywood_resolve_showroom_card_cta_type( $attributes );

	return array(
		'ctaType'     => '' !== $cta_type ? $cta_type : 'directions',
		'showImage'   => array_key_exists( 'showImage', $attributes ) ? (bool) $attributes['showImage'] : false,
		'showAddress' => ! array_key_exists( 'showAddress', $attributes ) || (bool) $attributes['showAddress'],
		'showTime'    => ! array_key_exists( 'showTime', $attributes ) || (bool) $attributes['showTime'],
		'showContact' => ! array_key_exists( 'showContact', $attributes ) || (bool) $attributes['showContact'],
	);
}

/**
 * Render the showroom picker trigger and option list.
 *
 * @param array<int, array<string, mixed>> $showrooms   Mapped showrooms.
 * @param string                           $active_slug Active showroom slug.
 * @param string                           $instance_id Unique instance prefix.
 * @return string
 */
function happywood_render_showroom_contact_details_picker( array $showrooms, string $active_slug, string $instance_id ): string {
	$active_label = '';

	foreach ( $showrooms as $showroom ) {
		if ( (string) ( $showroom['key'] ?? '' ) === $active_slug ) {
			$active_label = (string) ( $showroom['tab_label'] ?? '' );
			break;
		}
	}

	if ( '' === $active_label && ! empty( $showrooms[0]['tab_label'] ) ) {
		$active_label = (string) $showrooms[0]['tab_label'];
	}

	$list_id    = $instance_id . '-list';
	$trigger_id = $instance_id . '-trigger';

	ob_start();
	?>
	<div class="hw-showroom-contact-details__picker">
		<button
			type="button"
			id="<?php echo esc_attr( $trigger_id ); ?>"
			class="hw-showroom-contact-details__trigger"
			aria-haspopup="listbox"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr( $list_id ); ?>"
		>
			<span class="hw-showroom-contact-details__trigger-icon hw-icon-preview hw-icon-map-pin" aria-hidden="true"></span>
			<span class="hw-showroom-contact-details__trigger-label"><?php echo esc_html( $active_label ); ?></span>
			<span class="hw-showroom-contact-details__trigger-chevron hw-icon-preview hw-icon-chevron-down" aria-hidden="true"></span>
		</button>
		<ul
			id="<?php echo esc_attr( $list_id ); ?>"
			class="hw-showroom-contact-details__list"
			role="listbox"
			aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
			hidden
		>
			<?php foreach ( $showrooms as $showroom ) : ?>
				<?php
				$slug      = (string) ( $showroom['key'] ?? '' );
				$is_active = $slug === $active_slug;
				$classes   = 'hw-showroom-contact-details__option' . ( $is_active ? ' is-active' : '' );
				?>
				<li
					role="option"
					class="<?php echo esc_attr( $classes ); ?>"
					data-showroom-slug="<?php echo esc_attr( $slug ); ?>"
					aria-selected="<?php echo $is_active ? 'true' : 'false'; ?>"
					tabindex="<?php echo $is_active ? 0 : -1; ?>"
				>
					<?php echo esc_html( (string) ( $showroom['tab_label'] ?? '' ) ); ?>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php

	return (string) ob_get_clean();
}

/**
 * Render showroom card panels for each mapped showroom.
 *
 * @param array<int, array<string, mixed>> $showrooms   Mapped showrooms.
 * @param string                           $active_slug Active showroom slug.
 * @param array<string, mixed>             $card_args   Showroom card attribute overrides.
 * @return string
 */
function happywood_render_showroom_contact_details_panels( array $showrooms, string $active_slug, array $card_args ): string {
	$markup = '';

	foreach ( $showrooms as $showroom ) {
		$slug      = (string) ( $showroom['key'] ?? '' );
		$is_active = $slug === $active_slug;
		$classes   = 'hw-showroom-contact-details__panel' . ( $is_active ? ' is-active' : '' );
		$hidden    = $is_active ? '' : ' hidden';

		$markup .= sprintf(
			'<div class="%1$s" data-showroom-slug="%2$s"%3$s>',
			esc_attr( $classes ),
			esc_attr( $slug ),
			$hidden
		);
		$markup .= happywood_render_showroom_card_for_post(
			(int) ( $showroom['post_id'] ?? 0 ),
			$card_args
		);
		$markup .= '</div>';
	}

	return $markup;
}

/**
 * Editor-only empty state for the contact details block.
 *
 * @return string
 */
function happywood_render_showroom_contact_details_empty_notice(): string {
	return sprintf(
		'<div class="hw-showroom-contact-details hw-showroom-contact-details--empty"><p>%s</p></div>',
		esc_html__( 'No mapped showrooms are available. Publish showroom posts assigned to a showroom location, or choose specific showrooms in the block sidebar.', 'happywood' )
	);
}

/**
 * Render the Showroom Contact Details block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param \WP_Block|null       $block      Block instance.
 * @return string
 */
function happywood_render_showroom_contact_details( array $attributes, $block = null ): string {
	$selected    = is_array( $attributes['showrooms'] ?? null ) ? $attributes['showrooms'] : array();
	$showrooms   = happywood_get_showroom_contact_details_items( $selected );
	$is_editor   = happywood_is_showroom_card_editor_preview();
	$instance_id = wp_unique_id( 'hw-showroom-contact-details-' );

	if ( empty( $showrooms ) ) {
		if ( $is_editor ) {
			return happywood_render_showroom_contact_details_empty_notice();
		}

		return '';
	}

	$active_slug = (string) ( $showrooms[0]['key'] ?? '' );
	$card_args   = happywood_parse_showroom_contact_details_card_args( $attributes );

	happywood_enqueue_showroom_card_assets();

	$wrapper_attributes = get_block_wrapper_attributes(
		array(
			'class'                => 'hw-showroom-contact-details',
			'data-active-showroom' => esc_attr( $active_slug ),
		),
		$block
	);

	ob_start();
	?>
	<div <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php
		if ( count( $showrooms ) > 1 ) {
			echo happywood_render_showroom_contact_details_picker( $showrooms, $active_slug, $instance_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<div class="hw-showroom-contact-details__panels">
			<?php echo happywood_render_showroom_contact_details_panels( $showrooms, $active_slug, $card_args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>
	</div>
	<?php

	return (string) ob_get_clean();
}
