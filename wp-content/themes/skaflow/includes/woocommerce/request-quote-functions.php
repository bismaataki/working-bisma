<?php
/**
 * Request a Quote page helpers (YITH).
 *
 * @package Skaflow
 */

defined( 'ABSPATH' ) || exit;

use Skaflow\Product_Swatches;

/**
 * Whether the current request is the YITH quote page.
 */
function skaflow_is_request_quote_page(): bool {
	if ( ! is_singular( 'page' ) ) {
		return false;
	}

	if ( ! skaflow_is_ywraq_available() || ! class_exists( 'YITH_Request_Quote' ) ) {
		return false;
	}

	$page_id = (int) YITH_Request_Quote()->get_raq_page_id();

	return $page_id > 0 && is_page( $page_id );
}

/**
 * Render YITH notices and success message wrapper for the split layout.
 */
function skaflow_render_ywraq_notices_markup(): string {
	if ( ! skaflow_is_ywraq_available() ) {
		return '';
	}

	ob_start();

	if ( function_exists( 'wc_print_notices' ) ) {
		wc_print_notices();
	}

	if ( function_exists( 'yith_ywraq_print_notices' ) ) {
		yith_ywraq_print_notices();
	}

	$notices_html = (string) ob_get_clean();

	ob_start();
	do_action( 'ywraq_raq_message' );
	$message_html = (string) ob_get_clean();

	$has_message = (bool) preg_match( '/<(p|form)\b/i', $message_html );

	if ( '' === trim( wp_strip_all_tags( $notices_html ) ) && ! $has_message ) {
		return '<div id="yith-ywraq-message" class="sk-request-quote-message sk-request-quote-message--empty" hidden></div>';
	}

	$output  = '<div class="sk-request-quote-notices woocommerce">';
	$output .= $notices_html;
	$output .= '<div id="yith-ywraq-message" class="sk-request-quote-message">';

	if ( $has_message ) {
		$output .= $message_html;
	}

	$output .= '</div></div>';

	return $output;
}

/**
 * Shortcode: [skaflow_ywraq_notices]
 */
function skaflow_ywraq_notices_shortcode(): string {
	return skaflow_render_ywraq_notices_markup();
}
add_shortcode( 'skaflow_ywraq_notices', 'skaflow_ywraq_notices_shortcode' );

/**
 * Body class on the quote page for scoped styling.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function skaflow_request_quote_body_class( array $classes ): array {
	if ( skaflow_is_request_quote_page() ) {
		$classes[] = 'sk-request-quote-page';
	}

	return $classes;
}
add_filter( 'body_class', 'skaflow_request_quote_body_class' );

/**
 * Custom empty-list copy for the quote summary sidebar.
 */
function skaflow_ywraq_empty_list_message_text( string $message ): string {
	if ( ! skaflow_is_request_quote_page() ) {
		return $message;
	}

	return __( 'No products selected. Add items to your quote from the shop to continue.', 'skaflow' );
}
add_filter( 'ywraq_get_list_empty_message_text', 'skaflow_ywraq_empty_list_message_text' );

/**
 * Submit button label on the request quote form.
 */
function skaflow_ywraq_submit_button_label( string $label ): string {
	if ( ! skaflow_is_request_quote_page() ) {
		return $label;
	}

	return __( 'Send Quote', 'skaflow' );
}
add_filter( 'ywraq_form_defaul_submit_label', 'skaflow_ywraq_submit_button_label' );

/**
 * Open the shared submit row wrapper (Send Quote + Clear Form).
 */
function skaflow_ywraq_open_submit_row(): void {
	if ( ! skaflow_is_request_quote_page() ) {
		return;
	}

	echo '<div class="sk-request-quote-submit-row">';
}
add_action( 'ywraq_before_submit_default_form', 'skaflow_ywraq_open_submit_row', 4 );

/**
 * Add a Clear Form button after the YITH submit control.
 */
function skaflow_ywraq_render_clear_form_button(): void {
	if ( ! skaflow_is_request_quote_page() ) {
		return;
	}
	?>
	<button type="button" class="button sk-request-quote-clear-form">
		<?php esc_html_e( 'Clear Form', 'skaflow' ); ?>
	</button>
	<?php
}
add_action( 'ywraq_after_content_default_form', 'skaflow_ywraq_render_clear_form_button', 3 );

/**
 * Close the shared submit row wrapper.
 */
function skaflow_ywraq_close_submit_row(): void {
	if ( ! skaflow_is_request_quote_page() ) {
		return;
	}

	echo '</div>';
}
add_action( 'ywraq_after_content_default_form', 'skaflow_ywraq_close_submit_row', 4 );

/**
 * Pass quote-page strings to the public script.
 *
 * @param array<string, mixed> $data Localized script data.
 * @return array<string, mixed>
 */
function skaflow_request_quote_localize_script_data( array $data ): array {
	if ( ! skaflow_is_request_quote_page() ) {
		return $data;
	}

	$data['requestQuote'] = array(
		'emptyListMessage' => skaflow_ywraq_empty_list_message_text( '' ),
		'submitButtonIcon' => 'paper-plane',
	);

	return $data;
}
add_filter( 'skaflow_public_script_localize_data', 'skaflow_request_quote_localize_script_data' );

/**
 * Quote summary thumbnail with theme placeholder fallback.
 *
 * @param string                     $image   Product image HTML from YITH.
 * @param array<string, mixed>|false $raq     Quote line item (false in email templates).
 * @param \WC_Product                $product Product object.
 * @return string
 */
function skaflow_ywraq_quote_product_image( string $image, array|false $raq, \WC_Product $product ): string {
	unset( $raq );

	if ( ! $product instanceof \WC_Product ) {
		return $image;
	}

	$needs_placeholder = ! $product->get_image_id()
		|| str_contains( $image, 'woocommerce-placeholder' );

	if ( ! $needs_placeholder ) {
		return $image;
	}

	return skaflow_get_ywraq_quote_product_image_html( $product );
}
add_filter( 'ywraq_product_image', 'skaflow_ywraq_quote_product_image', 10, 3 );

/**
 * Force quote summary table columns on the request quote page.
 *
 * @param array<string, mixed> $out   Parsed shortcode attributes.
 * @param array<string, mixed> $pairs Default attribute pairs.
 * @param array<string, mixed> $atts  Raw shortcode attributes.
 * @return array<string, mixed>
 */
function skaflow_ywraq_table_shortcode_atts( array $out, array $pairs, array $atts ): array {
	unset( $pairs, $atts );

	if ( ! skaflow_is_request_quote_page() ) {
		return $out;
	}

	$show_prices = 'no' === get_option( 'ywraq_hide_price', 'no' );

	$out['show_thumbnail']     = true;
	$out['show_sku']           = true;
	$out['show_single_price']  = $show_prices;
	$out['show_line_total']    = false;
	$out['show_quantity']      = true;
	$out['show_totals']        = $show_prices;
	$out['show_update_button'] = 'no';

	return $out;
}
add_filter( 'shortcode_atts_yith_ywraq_request_quote_table', 'skaflow_ywraq_table_shortcode_atts', 10, 3 );

/**
 * Hide YITH "Update list" on the request quote page (template reads the option directly).
 *
 * @param mixed $pre Option pre-filter value.
 * @return mixed
 */
function skaflow_ywraq_disable_update_list_option( $pre ) {
	if ( skaflow_is_request_quote_page() ) {
		return 'no';
	}

	return $pre;
}
add_filter( 'pre_option_ywraq_show_update_list', 'skaflow_ywraq_disable_update_list_option' );

/**
 * Keep SKU in the meta block instead of under the product title.
 *
 * @param string      $sku_html SKU markup appended to the title.
 * @param \WC_Product $product  Product object.
 */
function skaflow_ywraq_remove_title_sku( string $sku_html, \WC_Product $product ): string {
	unset( $product );

	if ( skaflow_is_request_quote_page() ) {
		return '';
	}

	return $sku_html;
}
add_filter( 'ywraq_sku_label_html', 'skaflow_ywraq_remove_title_sku', 10, 2 );

/**
 * Whether an attribute label/taxonomy represents a colour option.
 */
function skaflow_is_color_attribute( string $taxonomy_or_name, string $label = '' ): bool {
	$haystack = strtolower( trim( $taxonomy_or_name . ' ' . $label ) );

	foreach ( array( 'colour', 'color', 'pa_colour', 'pa_color' ) as $needle ) {
		if ( str_contains( $haystack, $needle ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Resolve the selected colour variation from a quote line item.
 *
 * @param array<string, mixed> $raq     Quote line item.
 * @param \WC_Product          $product Product object.
 * @return array{taxonomy: string, slug: string, label: string}|null
 */
function skaflow_get_raq_color_variation( array $raq, \WC_Product $product ): ?array {
	if ( empty( $raq['variations'] ) || ! is_array( $raq['variations'] ) ) {
		return null;
	}

	foreach ( $raq['variations'] as $name => $value ) {
		if ( '' === $value || is_a( $value, 'WC_Product_Attribute' ) ) {
			continue;
		}

		$attr_name = str_replace( 'attribute_', '', urldecode( (string) $name ) );
		$taxonomy  = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( (string) $name ) ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		if ( ! taxonomy_exists( $taxonomy ) ) {
			$taxonomy = $attr_name;
		}

		$label = taxonomy_exists( $taxonomy )
			? wc_attribute_label( $taxonomy )
			: wc_attribute_label( $attr_name, $product );

		if ( ! skaflow_is_color_attribute( $taxonomy, $label ) ) {
			continue;
		}

		$slug    = (string) $value;
		$display = $slug;

		if ( taxonomy_exists( $taxonomy ) && class_exists( Product_Swatches::class ) ) {
			$term = Product_Swatches::resolve_attribute_term( $taxonomy, (string) $value );

			if ( $term instanceof \WP_Term ) {
				$slug    = $term->slug;
				$display = $term->name;
			}
		} elseif ( taxonomy_exists( $taxonomy ) ) {
			$term = get_term_by( 'slug', $value, $taxonomy ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

			if ( ! is_wp_error( $term ) && $term && $term->name ) {
				$slug    = $term->slug;
				$display = $term->name;
			}
		}

		return array(
			'taxonomy' => $taxonomy,
			'slug'     => $slug,
			'label'    => $display,
		);
	}

	return null;
}

/**
 * Quote summary line price HTML (unit price x quantity).
 *
 * @param \WC_Product          $product Product object.
 * @param array<string, mixed> $raq     Quote line item.
 * @return string
 */
function skaflow_ywraq_get_quote_item_price_html( \WC_Product $product, array $raq ): string {
	if ( 'yes' === get_option( 'ywraq_hide_price', 'no' ) ) {
		return '';
	}

	$quantity = max( 1, (int) ( $raq['quantity'] ?? 1 ) );

	if ( isset( WC()->cart ) ) {
		$price = apply_filters(
			'yith_ywraq_product_subtotal_html',
			WC()->cart->get_product_subtotal( $product, $quantity ),
			$product,
			$raq
		);
	} else {
		$price = wc_price( wc_get_price_to_display( $product, array( 'qty' => $quantity ) ) );
	}

	return (string) apply_filters( 'yith_ywraq_hide_price_template', $price, $product->get_id(), $raq );
}

/**
 * Restyle quote summary variation rows: colour swatch, SKU, then other attrs.
 *
 * @param array<int, array<string, string>> $item_data Variation/meta rows.
 * @param array<string, mixed>              $raq         Quote line item.
 * @param \WC_Product                       $product     Product object.
 * @param bool                              $show_price  Whether prices are shown.
 * @return array<int, array<string, string>>
 */
function skaflow_ywraq_request_quote_view_item_data( array $item_data, array $raq, \WC_Product $product, bool $show_price ): array {
	if ( ! skaflow_is_request_quote_page() ) {
		return $item_data;
	}

	$formatted       = array();
	$color_variation = skaflow_get_raq_color_variation( $raq, $product );
	$parent_id       = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

	if ( $color_variation && class_exists( Product_Swatches::class ) ) {
		$formatted[] = array(
			'key'   => __( 'Color', 'skaflow' ),
			'value' => Product_Swatches::render_readonly_swatch_inline(
				$parent_id,
				$color_variation['taxonomy'],
				$color_variation['slug'],
				$color_variation['label']
			),
		);
	}

	foreach ( $item_data as $data ) {
		if ( skaflow_is_color_attribute( '', (string) ( $data['key'] ?? '' ) ) ) {
			continue;
		}

		$formatted[] = $data;
	}

	$sku = $product->get_sku();

	if ( '' !== $sku ) {
		$formatted[] = array(
			'key'   => __( 'SKU', 'skaflow' ),
			'value' => sprintf(
				'<span class="sk-request-quote-item-sku">%s</span>',
				esc_html( $sku )
			),
		);
	}

	if ( $show_price ) {
		$price_html = skaflow_ywraq_get_quote_item_price_html( $product, $raq );

		if ( '' !== trim( wp_strip_all_tags( $price_html ) ) ) {
			$formatted[] = array(
				'key'   => '',
				'value' => sprintf(
					'<span class="sk-request-quote-item-price">%s</span>',
					wp_kses_post( $price_html )
				),
			);
		}
	}

	return $formatted;
}
add_filter( 'ywraq_request_quote_view_item_data', 'skaflow_ywraq_request_quote_view_item_data', 10, 4 );

/**
 * Whether quote summary totals should be shown on the request quote page.
 */
function skaflow_ywraq_should_show_quote_totals(): bool {
	if ( ! skaflow_is_request_quote_page() ) {
		return false;
	}

	return 'yes' !== get_option( 'ywraq_hide_price', 'no' );
}

/**
 * Build a tax label such as "GST (10%)" from applied rate IDs.
 *
 * @param array<int, float> $taxes_by_rate Tax amounts keyed by rate ID.
 */
function skaflow_ywraq_get_quote_tax_label( array $taxes_by_rate ): string {
	$default_label = WC()->countries->tax_or_vat();

	if ( empty( $taxes_by_rate ) || ! wc_tax_enabled() ) {
		return $default_label;
	}

	if ( 1 === count( $taxes_by_rate ) ) {
		$rate_id = (int) array_key_first( $taxes_by_rate );
		$label   = WC_Tax::get_rate_label( $rate_id );
		$percent = WC_Tax::get_rate_percent_value( $rate_id );

		if ( '' === $label ) {
			$label = $default_label;
		}

		if ( $percent > 0 ) {
			$percent_formatted = rtrim( rtrim( number_format( $percent, 2, '.', '' ), '0' ), '.' );

			return sprintf( '%s (%s%%)', $label, $percent_formatted );
		}

		return $label;
	}

	return $default_label;
}

/**
 * Calculate quote summary totals for the sidebar table.
 *
 * @param array<string, mixed> $raq_content Quote line items.
 * @return array<string, mixed>|null
 */
function skaflow_ywraq_get_quote_totals( array $raq_content ): ?array {
	if ( ! skaflow_ywraq_should_show_quote_totals() || empty( $raq_content ) ) {
		return null;
	}

	$tax_display   = (string) apply_filters( 'ywraq_tax_display_list', get_option( 'woocommerce_tax_display_cart' ) );
	$subtotal      = 0.0;
	$total_tax     = 0.0;
	$taxes_by_rate = array();

	foreach ( $raq_content as $raq ) {
		if ( ! empty( $raq['yith_wapo_individual_item'] ) && 1 === (int) $raq['yith_wapo_individual_item'] && ! empty( $raq['yith_wapo_parent_key'] ) ) {
			continue;
		}

		$product_id = ( ! empty( $raq['variation_id'] ) && (int) $raq['variation_id'] > 0 )
			? (int) $raq['variation_id']
			: (int) ( $raq['product_id'] ?? 0 );
		$product    = wc_get_product( $product_id );

		if ( ! $product instanceof \WC_Product ) {
			continue;
		}

		do_action( 'ywraq_quote_adjust_price', $raq, $product );

		$quantity = max( 1, (int) ( $raq['quantity'] ?? 1 ) );
		$price    = ( 'incl' === $tax_display )
			? wc_get_price_including_tax( $product, array( 'qty' => $quantity ) )
			: wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );

		if ( $product->is_type( 'yith-composite' ) && ! empty( $raq['yith_wcp_component_data'] ) && is_array( $raq['yith_wcp_component_data'] ) ) {
			$component_data                   = $raq['yith_wcp_component_data'];
			$composite_stored_data            = $product->getComponentsData();
			$composite_data_price             = 0.0;
			$composite_data_price_with_tax    = 0.0;
			$composite_data_price_without_tax = 0.0;

			foreach ( $composite_stored_data as $wcp_key => $component_item ) {
				unset( $component_item );

				if ( empty( $component_data['selection_data'][ $wcp_key ] ) || (int) $component_data['selection_data'][ $wcp_key ] <= 0 ) {
					continue;
				}

				if ( ! empty( $component_data['selection_variation_data'][ $wcp_key ] ) && (int) $component_data['selection_variation_data'][ $wcp_key ] > 0 ) {
					$child_product = wc_get_product( (int) $component_data['selection_variation_data'][ $wcp_key ] );
				} else {
					$child_product = wc_get_product( (int) $component_data['selection_data'][ $wcp_key ] );
				}

				if ( ! $child_product instanceof \WC_Product ) {
					continue;
				}

				$composite_data_price             += (float) wc_get_price_to_display( $child_product, array( 'qty' => $quantity ) );
				$composite_data_price_with_tax    += (float) wc_get_price_including_tax( $child_product, array( 'qty' => $quantity ) );
				$composite_data_price_without_tax += (float) wc_get_price_excluding_tax( $child_product, array( 'qty' => $quantity ) );
			}

			$price     += $composite_data_price;
			$total_tax += (float) ( $composite_data_price_with_tax - $composite_data_price_without_tax );
		} elseif ( $price >= 0 ) {
			$price_with_tax    = (float) wc_get_price_including_tax( $product, array( 'qty' => $quantity ) );
			$price_without_tax = (float) wc_get_price_excluding_tax( $product, array( 'qty' => $quantity ) );
			$line_tax          = $price_with_tax - $price_without_tax;

			$total_tax += $line_tax;

			if ( wc_tax_enabled() && $line_tax > 0 ) {
				$line_taxes = WC_Tax::calc_tax( $price_without_tax, WC_Tax::get_rates( $product->get_tax_class() ), wc_prices_include_tax() );

				foreach ( $line_taxes as $rate_id => $tax_amount ) {
					$taxes_by_rate[ (int) $rate_id ] = ( $taxes_by_rate[ (int) $rate_id ] ?? 0 ) + (float) $tax_amount;
				}
			}
		}

		$subtotal += (float) $price;
	}

	$subtotal_ex     = $subtotal;
	$estimated_total = $subtotal_ex + $total_tax;

	if ( 'incl' === $tax_display ) {
		$subtotal_ex     = $subtotal - $total_tax;
		$estimated_total = $subtotal;
	}

	return array(
		'subtotal'  => max( 0, $subtotal_ex ),
		'tax'       => max( 0, $total_tax ),
		'total'     => max( 0, $estimated_total ),
		'show_tax'  => wc_tax_enabled() && $total_tax > 0,
		'tax_label' => skaflow_ywraq_get_quote_tax_label( $taxes_by_rate ),
	);
}

/**
 * Render the quote summary totals table under the product list.
 */
function skaflow_ywraq_render_quote_totals_table(): void {
	if ( ! skaflow_ywraq_should_show_quote_totals() || ! class_exists( 'YITH_Request_Quote' ) ) {
		return;
	}

	$totals = skaflow_ywraq_get_quote_totals( YITH_Request_Quote()->get_raq_return() );

	if ( null === $totals ) {
		return;
	}
	?>
	<table class="sk-request-quote-totals">
		<tbody>
			<tr class="sk-request-quote-totals__row sk-request-quote-totals__row--subtotal">
				<th scope="row"><?php esc_html_e( 'Subtotal', 'skaflow' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Subtotal', 'skaflow' ); ?>">
					<?php echo wp_kses_post( wc_price( $totals['subtotal'] ) ); ?>
				</td>
			</tr>
			<?php if ( $totals['show_tax'] ) : ?>
				<tr class="sk-request-quote-totals__row sk-request-quote-totals__row--tax">
					<th scope="row"><?php echo esc_html( $totals['tax_label'] ); ?></th>
					<td data-title="<?php echo esc_attr( $totals['tax_label'] ); ?>">
						<?php echo wp_kses_post( wc_price( $totals['tax'] ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
			<tr class="sk-request-quote-totals__row sk-request-quote-totals__row--total">
				<th scope="row"><?php esc_html_e( 'Est. Total', 'skaflow' ); ?></th>
				<td data-title="<?php esc_attr_e( 'Est. Total', 'skaflow' ); ?>">
					<?php echo wp_kses_post( wc_price( $totals['total'] ) ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<?php
}
add_action( 'ywraq_after_list_table', 'skaflow_ywraq_render_quote_totals_table', 5 );

/**
 * Render a quote summary product image, using the theme placeholder when needed.
 *
 * @param \WC_Product $product Product object.
 * @return string
 */
function skaflow_get_ywraq_quote_product_image_html( \WC_Product $product ): string {
	$image_size = array( 74, 74 );
	$image_id   = $product->get_image_id();

	if ( $image_id > 0 ) {
		$image = wp_get_attachment_image(
			$image_id,
			$image_size,
			false,
			array(
				'class'    => 'attachment-sk-quote-summary size-sk-quote-summary',
				'alt'      => $product->get_name(),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		if ( $image ) {
			return $image;
		}
	}

	$placeholder_id = skaflow_get_default_placeholder_image_id();

	if ( $placeholder_id > 0 ) {
		$placeholder = wp_get_attachment_image(
			$placeholder_id,
			$image_size,
			false,
			array(
				'class'    => 'attachment-sk-quote-summary size-sk-quote-summary sk-request-quote-image--placeholder',
				'alt'      => $product->get_name(),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);

		if ( $placeholder ) {
			return $placeholder;
		}
	}

	return sprintf(
		'<img src="%1$s" class="attachment-sk-quote-summary size-sk-quote-summary sk-request-quote-image--placeholder" alt="%2$s" loading="lazy" decoding="async" width="74" height="74" />',
		esc_url( get_theme_file_uri( SKAFLOW_MENU_THUMB_PLACEHOLDER ) ),
		esc_attr( $product->get_name() )
	);
}
