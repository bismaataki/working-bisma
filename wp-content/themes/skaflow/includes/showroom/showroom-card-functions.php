<?php
/**
 * Shared showroom card markup helpers.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Skaflow\Showroom_Locator;

/**
 * Allowed minimal markup in opening hours.
 */
const SKAFLOW_SHOWROOM_CARD_TIME_ALLOWED_HTML = array(
	'br' => array(),
);

/**
 * Allowed minimal markup in addresses.
 */
const SKAFLOW_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML = array(
	'br'     => array(),
	'b'      => array(),
	'strong' => array(),
);

/**
 * Build inline block comment markup for a showroom card (for do_blocks).
 *
 * @param array<string, mixed> $args Card configuration.
 * @return string
 */
function skaflow_get_showroom_card_blocks_markup( array $args = array() ): string {
	$defaults = array(
		'ctaType'     => 'directions',
		'showImage'   => true,
		'showAddress' => true,
		'showTime'    => true,
		'showContact' => true,
		'className'   => '',
	);

	$args = wp_parse_args( $args, $defaults );

	$block_attrs = array(
		'ctaType'     => sanitize_key( (string) $args['ctaType'] ),
		'showImage'   => (bool) $args['showImage'],
		'showAddress' => (bool) $args['showAddress'],
		'showTime'    => (bool) $args['showTime'],
		'showContact' => (bool) $args['showContact'],
	);

	$class_name = trim( (string) $args['className'] );

	if ( '' !== $class_name ) {
		$block_attrs['className'] = $class_name;
	}

	$json = wp_json_encode( $block_attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );

	return sprintf( '<!-- wp:skaflow/showroom-card %s /-->', $json );
}

/**
 * Enqueue showroom-card block styles when rendered outside a saved post context.
 *
 * @return void
 */
function skaflow_enqueue_showroom_card_assets(): void {
	static $enqueued = false;

	if ( $enqueued || ! function_exists( 'register_block_type' ) ) {
		return;
	}

	$block_type = \WP_Block_Type_Registry::get_instance()->get_registered( 'skaflow/showroom-card' );

	if ( $block_type && ! empty( $block_type->style ) ) {
		wp_enqueue_style( $block_type->style );
	}

	$enqueued = true;
}

/**
 * Render a showroom card for a specific post ID via do_blocks().
 *
 * @param int                  $post_id Showroom post ID.
 * @param array<string, mixed> $args    Block attribute overrides.
 * @return string
 */
function skaflow_render_showroom_card_for_post( int $post_id, array $args = array() ): string {
	if ( $post_id <= 0 || 'showrooms' !== get_post_type( $post_id ) ) {
		return '';
	}

	$post = get_post( $post_id );

	if ( ! $post instanceof \WP_Post ) {
		return '';
	}

	skaflow_enqueue_showroom_card_assets();

	$previous_post = isset( $GLOBALS['post'] ) && $GLOBALS['post'] instanceof \WP_Post ? $GLOBALS['post'] : null;

	// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
	$GLOBALS['post'] = $post;
	setup_postdata( $post );

	$markup = do_blocks( skaflow_get_showroom_card_blocks_markup( $args ) );

	wp_reset_postdata();

	if ( $previous_post instanceof \WP_Post ) {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = $previous_post;
		setup_postdata( $previous_post );
	} else {
		// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		$GLOBALS['post'] = null;
	}

	return $markup;
}

/**
 * Resolve the showroom post ID for card rendering.
 *
 * Order: block query context → REST SSR query arg → loop global post.
 *
 * @param \WP_Block|null $block Block instance.
 * @return int
 */
function skaflow_resolve_showroom_card_post_id( $block = null ): int {
	if ( $block instanceof \WP_Block && ! empty( $block->context['postId'] ) ) {
		return absint( $block->context['postId'] );
	}

	if ( skaflow_is_showroom_card_editor_preview() && ! empty( $_GET['post_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( wp_unslash( $_GET['post_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	$post_id = get_the_ID();

	return $post_id ? absint( $post_id ) : 0;
}

/**
 * Find mapped showroom data for a showroom post ID.
 *
 * @param int $post_id Showroom post ID.
 * @return array<string, mixed>|null
 */
function skaflow_get_showroom_by_post_id( int $post_id ): ?array {
	if ( $post_id <= 0 || 'showrooms' !== get_post_type( $post_id ) ) {
		return null;
	}

	foreach ( Showroom_Locator::get_showrooms() as $showroom ) {
		if ( (int) ( $showroom['post_id'] ?? 0 ) === $post_id ) {
			return $showroom;
		}
	}

	return null;
}

/**
 * Whether the current request is a block editor server render.
 *
 * @return bool
 */
function skaflow_is_showroom_card_editor_preview(): bool {
	return ( defined( 'REST_REQUEST' ) && REST_REQUEST );
}

/**
 * Build a Google Maps search URL from a showroom address string.
 *
 * Appends the site title to help Maps disambiguate the destination.
 *
 * @param string $address Raw address (may contain limited HTML).
 * @return string Empty when address is missing.
 */
function skaflow_get_showroom_directions_url( string $address ): string {
	$plain = wp_strip_all_tags(
		str_replace(
			array( '<br>', '<br/>', '<br />', '<BR>', '<BR/>', '<BR />' ),
			', ',
			$address
		)
	);
	$plain = trim( preg_replace( '/\s+/', ' ', $plain ) ?? '' );

	if ( '' === $plain ) {
		return '';
	}

	$site_title = trim( (string) get_bloginfo( 'name' ) );

	if ( '' !== $site_title ) {
		$plain = $plain . ', ' . $site_title;
	}

	return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode( $plain );
}

/**
 * Resolve which CTA button to render on the showroom card.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @return string directions|learn-more, or empty when hidden.
 */
function skaflow_resolve_showroom_card_cta_type( array $attributes ): string {
	if ( isset( $attributes['ctaType'] ) ) {
		$cta_type = sanitize_key( (string) $attributes['ctaType'] );

		return in_array( $cta_type, array( 'directions', 'learn-more' ), true ) ? $cta_type : 'directions';
	}

	// Legacy attribute from earlier block versions.
	if ( array_key_exists( 'showCta', $attributes ) && ! $attributes['showCta'] ) {
		return '';
	}

	return 'directions';
}

/**
 * Render showroom card CTA button group (single selected button).
 *
 * @param array<string, mixed> $showroom Mapped showroom data.
 * @param string               $cta_type Selected CTA type.
 * @return string
 */
function skaflow_render_showroom_card_cta( array $showroom, string $cta_type ): string {
	$buttons = array();

	if ( 'directions' === $cta_type ) {
		$directions_url = skaflow_get_showroom_directions_url( (string) ( $showroom['address'] ?? '' ) );

		if ( '' !== $directions_url ) {
			$buttons[] = array(
				'label'    => __( 'Get Directions', 'skaflow' ),
				'url'      => $directions_url,
				'style'    => 'is-style-primary',
				'external' => true,
				'icon'     => 'map-pin',
			);
		}
	}

	if ( 'learn-more' === $cta_type ) {
		$permalink = (string) ( $showroom['permalink'] ?? '' );

		if ( '' !== $permalink ) {
			$buttons[] = array(
				'label'         => sprintf(
					/* translators: %s: showroom city label, e.g. Sydney */
					__( 'Learn Our %s Showroom', 'skaflow' ),
					(string) ( $showroom['label'] ?? '' )
				),
				'url'           => $permalink,
				'style'         => 'is-style-ghost',
				'icon'          => 'arrow-right',
				'icon_position' => 'right',
			);
		}
	}

	if ( empty( $buttons ) ) {
		return '';
	}

	$markup = skaflow_get_buttons_markup(
		$buttons,
		array(
			'wrapper_class' => 'sk-showroom-card__actions',
			'layout'        => array(
				'type'           => 'flex',
				'justifyContent' => 'stretch',
				'flexWrap'       => 'wrap',
			),
		)
	);

	if ( '' === $markup ) {
		return '';
	}

	return '<div class="sk-showroom-card__cta">' . $markup . '</div>';
}

/**
 * Render a showroom card for the skaflow/showroom-card block.
 *
 * @param array<string, mixed> $attributes Block attributes.
 * @param \WP_Block|null       $block      Block instance.
 * @return string
 */
function skaflow_render_showroom_card( array $attributes, $block = null ): string {
	$post_id  = skaflow_resolve_showroom_card_post_id( $block );
	$showroom = skaflow_get_showroom_by_post_id( $post_id );

	if ( empty( $showroom ) ) {
		if ( skaflow_is_showroom_card_editor_preview() ) {
			return skaflow_render_showroom_card_empty_notice( $post_id );
		}

		return '';
	}

	$showroom_key = (string) ( $showroom['key'] ?? '' );

	$cta_type     = skaflow_resolve_showroom_card_cta_type( $attributes );
	$show_image   = ! array_key_exists( 'showImage', $attributes ) || (bool) $attributes['showImage'];
	$show_address = ! array_key_exists( 'showAddress', $attributes ) || (bool) $attributes['showAddress'];
	$show_time    = ! array_key_exists( 'showTime', $attributes ) || (bool) $attributes['showTime'];
	$show_contact = ! array_key_exists( 'showContact', $attributes ) || (bool) $attributes['showContact'];

	$wrapper_classes = 'sk-showroom-card';

	if ( 'directions' === $cta_type ) {
		$wrapper_classes .= ' sk-showroom-card--cta-directions';
	} elseif ( 'learn-more' === $cta_type ) {
		$wrapper_classes .= ' sk-showroom-card--cta-learn-more';
	}

	if ( $block instanceof \WP_Block ) {
		$wrapper_attributes = get_block_wrapper_attributes(
			array(
				'class' => $wrapper_classes,
			),
			$block
		);
	} else {
		$wrapper_attributes = 'class="' . esc_attr( $wrapper_classes ) . '"';
	}

	ob_start();
	?>
	<article <?php echo $wrapper_attributes; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> data-showroom-slug="<?php echo esc_attr( $showroom_key ); ?>" data-showroom-state="<?php echo esc_attr( (string) ( $showroom['state_slug'] ?? '' ) ); ?>">
		<?php
		if ( $show_image ) {
			echo skaflow_render_showroom_card_media( $showroom ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>
		<div class="sk-showroom-card__content">
			<h3 class="sk-showroom-card__title"><?php echo esc_html( (string) $showroom['warehouse'] ); ?></h3>
			<?php
			if ( $show_address && '' !== trim( (string) $showroom['address'] ) ) {
				echo skaflow_render_showroom_card_detail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'map-pin',
					wp_kses( (string) $showroom['address'], SKAFLOW_SHOWROOM_CARD_ADDRESS_ALLOWED_HTML )
				);
			}

			if ( $show_time && '' !== trim( (string) $showroom['time'] ) ) {
				echo skaflow_render_showroom_card_detail( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'clock',
					wp_kses( (string) $showroom['time'], SKAFLOW_SHOWROOM_CARD_TIME_ALLOWED_HTML )
				);
			}

			if ( $show_contact ) {
				echo skaflow_render_showroom_card_contact( $showroom ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			if ( '' !== $cta_type ) {
				echo skaflow_render_showroom_card_cta( $showroom, $cta_type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</div>
	</article>
	<?php
	return (string) ob_get_clean();
}

/**
 * Editor-only empty state when no loop showroom is available.
 *
 * @param int $post_id Requested post ID, if any.
 * @return string
 */
function skaflow_render_showroom_card_empty_notice( int $post_id ): string {
	if ( $post_id > 0 ) {
		$message = sprintf(
			/* translators: %d: showroom post ID */
			__( 'No mapped showroom data for post ID %d. The post must be a published showroom assigned to a showroom location.', 'skaflow' ),
			$post_id
		);
	} else {
		$message = __( 'Insert this block inside a Query Loop for the showrooms post type so it can render the current loop item.', 'skaflow' );
	}

	return sprintf(
		'<div class="sk-showroom-card sk-showroom-card--empty"><p>%s</p></div>',
		esc_html( $message )
	);
}

/**
 * Render card media.
 *
 * @param array<string, mixed> $showroom Mapped showroom data.
 * @return string
 */
function skaflow_render_showroom_card_media( array $showroom ): string {
	$image_id = isset( $showroom['image_id'] ) ? absint( $showroom['image_id'] ) : 0;
	$image    = '';

	if ( $image_id > 0 ) {
		$image = wp_get_attachment_image(
			$image_id,
			'large',
			false,
			array(
				'class'    => 'sk-showroom-card__image',
				'loading'  => 'lazy',
				'decoding' => 'async',
				'alt'      => (string) $showroom['warehouse'],
			)
		);
	}

	if ( ! $image ) {
		$placeholder_url = get_theme_file_uri( 'assets/images/menu-thumb-placeholder.png' );

		$image = sprintf(
			'<img class="sk-showroom-card__image" src="%1$s" alt="%2$s" loading="lazy" decoding="async" />',
			esc_url( $placeholder_url ),
			esc_attr( (string) $showroom['warehouse'] )
		);
	}

	$media_class = 'sk-showroom-card__media';

	if ( $image_id <= 0 ) {
		$media_class .= ' sk-showroom-card__media--placeholder';
	}

	return '<div class="' . esc_attr( $media_class ) . '">' . $image . '</div>';
}

/**
 * Render one detail row.
 *
 * @param string $icon_slug Lucide icon slug.
 * @param string $text      Detail text (already kses'd when needed).
 * @return string
 */
function skaflow_render_showroom_card_detail( string $icon_slug, string $text ): string {
	$icon_slug = sanitize_key( $icon_slug );

	return sprintf(
		'<div class="sk-showroom-card__detail"><span class="sk-icon-preview sk-icon-%1$s" aria-hidden="true"></span><span class="sk-showroom-card__detail-text">%2$s</span></div>',
		esc_attr( $icon_slug ),
		$text
	);
}

/**
 * Render phone and email contact row.
 *
 * @param array<string, mixed> $showroom Mapped showroom data.
 * @return string
 */
function skaflow_render_showroom_card_contact( array $showroom ): string {
	$has_phone = '' !== trim( (string) ( $showroom['phone'] ?? '' ) ) && '' !== (string) ( $showroom['phone_href'] ?? '' );
	$has_email = '' !== (string) ( $showroom['email'] ?? '' );

	if ( ! $has_phone && ! $has_email ) {
		return '';
	}

	$leading_icon = $has_phone ? 'phone' : 'mail';

	$markup = '<div class="sk-showroom-card__detail sk-showroom-card__detail--contact">';

	$markup .= '<span class="sk-showroom-card__contact-line">';

	if ( $has_phone ) {
		$markup .= sprintf(
			'<span class="sk-icon-preview sk-icon-%1$s" aria-hidden="true"></span>',
			esc_attr( $leading_icon )
		);
		$markup .= sprintf(
			'<a class="sk-showroom-card__detail-link" href="tel:%1$s">%2$s</a>',
			esc_attr( (string) $showroom['phone_href'] ),
			esc_html( (string) $showroom['phone'] )
		);
	}

	if ( $has_email ) {
		if ( $has_phone ) {
			$markup .= '<span class="sk-showroom-card__email-item">';
			$markup .= '<span class="sk-icon-preview sk-icon-mail" aria-hidden="true"></span>';
		}

		$markup .= sprintf(
			'<a class="sk-showroom-card__detail-link" href="mailto:%1$s">%2$s</a>',
			esc_attr( (string) $showroom['email'] ),
			esc_html( antispambot( (string) $showroom['email'] ) )
		);

		if ( $has_phone ) {
			$markup .= '</span>';
		}
	}

	$markup .= '</span>';
	$markup .= '</div>';

	return $markup;
}
