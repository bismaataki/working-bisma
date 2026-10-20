<?php
/**
 * PDP delivery estimate — markup for the postcode/quantity lookup card.
 *
 * **Dormant without a companion plugin.** This is the front end only. It posts
 * to the `cf_postcode_estimate` AJAX action, which no plugin in this repository
 * registers, so the card renders and every lookup returns the error state until
 * a shipping-rate plugin supplies that endpoint. Nothing else breaks.
 *
 * The full contract, so a replacement plugin can be written against it:
 *
 * - Action: `wp_ajax_` / `wp_ajax_nopriv_` + `cf_postcode_estimate`
 * - Posted: `postcode`, `quantity`, `product_id`, and `chk_page` selecting the
 *   single-product path (1) or the cart path (2)
 * - Expected back: JSON with a rate, an ETA string, and a status the panels
 *   below switch on
 *
 * Every customer-facing string and every result state is authored here rather
 * than in `view.js` so the copy stays translatable and the cart/checkout
 * delivery modal can reuse the same panels.
 *
 * To drop the feature entirely: delete this file, its require in
 * `includes/woocommerce/bootstrap.php`, and `src-jsx-blocks/delivery-estimate/`.
 *
 * @package Skaflow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX action a companion shipping-rate plugin must register.
 *
 * Not registered anywhere in this repository. See the file docblock.
 */
const SKAFLOW_DELIVERY_ESTIMATE_ACTION = 'cf_postcode_estimate';

/**
 * `chk_page` value selecting the single-product path.
 *
 * The cart path (`2`) ignores posted weights and reads the real cart instead.
 */
const SKAFLOW_DELIVERY_ESTIMATE_PDP_PAGE = 1;

/**
 * Resolve the per-unit weight to post for a product.
 *
 * Returns an empty string rather than a zero when the weight is unknown. The
 * endpoint rejects a missing, zero or non-numeric weight with a 400, and it
 * used to answer such a request with the flat "up to 4kg" rate — so an absent
 * weight must stay absent all the way to the front end instead of collapsing
 * into a number that prices.
 *
 * @param int $product_id Product post ID.
 * @return string Weight in kilograms, or '' when the product has none.
 */
function skaflow_get_delivery_estimate_weight( int $product_id ): string {

	if ( $product_id <= 0 || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$weight = $product->get_weight();

	if ( '' === $weight || null === $weight || ! is_numeric( $weight ) || (float) $weight <= 0 ) {
		return '';
	}

	return (string) $weight;
}

/**
 * Currency settings view.js needs to format the endpoint's bare float.
 *
 * The endpoint returns a number, not formatted HTML, so the card has to render
 * it the way the store renders every other price. Reading these from
 * WooCommerce rather than hardcoding a dollar sign keeps the two in step.
 *
 * @return array<string, string>
 */
function skaflow_get_delivery_estimate_currency_atts(): array {

	if ( ! function_exists( 'get_woocommerce_currency_symbol' ) ) {
		return array();
	}

	return array(
		'data-price-symbol'       => get_woocommerce_currency_symbol(),
		'data-price-decimals'     => (string) wc_get_price_decimals(),
		'data-price-decimal-sep'  => wc_get_price_decimal_separator(),
		'data-price-thousand-sep' => wc_get_price_thousand_separator(),
		'data-price-format'       => get_woocommerce_price_format(),
	);
}

/**
 * Customer-facing strings for the estimate card.
 *
 * "Standard Freight" and the transit-time line are design copy, not plugin
 * data — `DeliveryShippingMethod` labels its rate `Delivery` and carries no
 * transit times. Filter rather than editing the design's wording in place.
 *
 * @return array<string, string>
 */
function skaflow_get_delivery_estimate_strings(): array {

	$strings = array(
		'title'                => __( 'Delivery Estimate', 'skaflow' ),
		'postcode_label'       => __( 'Delivery postcode', 'skaflow' ),
		'postcode_placeholder' => __( 'Enter postcode', 'skaflow' ),
		'quantity_label'       => __( 'Quantity', 'skaflow' ),
		// Abbreviated because the field is narrow in the PDP's right-hand column;
		// the full word stays on the label for screen readers.
		'quantity_placeholder' => __( 'Qty', 'skaflow' ),
		'submit'               => __( 'Check', 'skaflow' ),
		'loading'              => __( 'Checking delivery…', 'skaflow' ),
		'priced_title'         => __( 'Standard Freight', 'skaflow' ),
		'priced_transit'       => __( 'Estimated 5–7 business days', 'skaflow' ),
		'priced_note'          => __( 'Delivered to ground floor or loading dock. Signature required.', 'skaflow' ),
		'failure'              => __( 'We couldn’t check delivery just now. Please try again.', 'skaflow' ),
		'select_options'       => __( 'Choose your options above so we can weigh this item.', 'skaflow' ),
	);

	/**
	 * Filter the delivery estimate card's customer-facing copy.
	 *
	 * @param array<string, string> $strings Copy keyed by slug.
	 */
	return (array) apply_filters( 'skaflow_delivery_estimate_strings', $strings );
}

/**
 * Result templates cloned by view.js.
 *
 * `<template>` elements keep every result string in PHP while leaving the
 * state machine in JS. Fields marked `data-field` are filled at runtime.
 *
 * @param array<string, string> $strings Copy from skaflow_get_delivery_estimate_strings().
 * @return string
 */
function skaflow_get_delivery_estimate_templates_html( array $strings ): string {

	$priced = sprintf(
		'<div class="sk-delivery-estimate__rate">
			<div class="sk-delivery-estimate__rate-head">
				<span class="sk-delivery-estimate__rate-title">%1$s</span>
				<span class="sk-delivery-estimate__rate-price" data-field="price"></span>
			</div>
			<p class="sk-delivery-estimate__rate-transit">%2$s</p>
			<p class="sk-delivery-estimate__rate-note">%3$s</p>
		</div>',
		esc_html( $strings['priced_title'] ),
		esc_html( $strings['priced_transit'] ),
		esc_html( $strings['priced_note'] )
	);

	// No wording of our own here. An unpriced lookup can mean four different
	// things and the plugin decides which, so it also supplies the sentence —
	// otherwise this card, the mini-cart and the cart modal would each explain
	// the same outcome in their own words. `reason` only picks the icon and tone.
	$unpriced = '<div class="sk-delivery-estimate__quote">
			<span class="sk-delivery-estimate__quote-icon" aria-hidden="true"></span>
			<p class="sk-delivery-estimate__quote-text" data-field="message"></p>
		</div>';

	$notice = '<div class="sk-delivery-estimate__notice">
			<span class="sk-delivery-estimate__notice-icon" aria-hidden="true"></span>
			<p class="sk-delivery-estimate__notice-text" data-field="message"></p>
		</div>';

	return sprintf(
		'<template class="sk-delivery-estimate__template" data-state="priced">%1$s</template>
		<template class="sk-delivery-estimate__template" data-state="unpriced">%2$s</template>
		<template class="sk-delivery-estimate__template" data-state="notice">%3$s</template>',
		$priced, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments.
		$unpriced,
		$notice
	);
}

/**
 * Build the delivery estimate card.
 *
 * The card is self-contained: its quantity is its own field and has nothing to
 * do with the add-to-cart quantity.
 *
 * @param int $product_id Product post ID.
 * @return string Safe HTML, or '' when the product cannot be estimated.
 */
function skaflow_get_delivery_estimate_html( int $product_id ): string {

	if ( $product_id <= 0 || ! function_exists( 'wc_get_product' ) ) {
		return '';
	}

	$product = wc_get_product( $product_id );

	if ( ! $product instanceof WC_Product ) {
		return '';
	}

	$strings     = skaflow_get_delivery_estimate_strings();
	$weight      = skaflow_get_delivery_estimate_weight( $product_id );
	$postcode_id = wp_unique_id( 'sk-delivery-estimate-postcode-' );
	$quantity_id = wp_unique_id( 'sk-delivery-estimate-quantity-' );

	$fields = sprintf(
		'<div class="sk-delivery-estimate__fields">
			<label class="screen-reader-text" for="%1$s">%2$s</label>
			<input type="text" id="%1$s" class="sk-delivery-estimate__input sk-delivery-estimate__input--postcode" name="cf_delivery_postcode" placeholder="%3$s" inputmode="numeric" autocomplete="postal-code" maxlength="4" />
			<label class="screen-reader-text" for="%4$s">%5$s</label>
			<input type="number" id="%4$s" class="sk-delivery-estimate__input sk-delivery-estimate__input--quantity" name="cf_delivery_quantity" placeholder="%6$s" inputmode="numeric" min="1" step="1" value="" />
			<button type="submit" class="sk-delivery-estimate__submit">%7$s</button>
		</div>',
		esc_attr( $postcode_id ),
		esc_html( $strings['postcode_label'] ),
		esc_attr( $strings['postcode_placeholder'] ),
		esc_attr( $quantity_id ),
		esc_html( $strings['quantity_label'] ),
		esc_attr( $strings['quantity_placeholder'] ),
		esc_html( $strings['submit'] )
	);

	$data_atts = array_merge(
		array(
			'data-sk-delivery-estimate' => '',
			'data-ajax-url'             => admin_url( 'admin-ajax.php' ),
			'data-action'               => SKAFLOW_DELIVERY_ESTIMATE_ACTION,
			'data-chk-page'             => (string) SKAFLOW_DELIVERY_ESTIMATE_PDP_PAGE,
			'data-product-id'           => (string) $product_id,
			'data-product-type'         => $product->get_type(),
			'data-weight'               => $weight,
			'data-loading-text'         => $strings['loading'],
			'data-failure-text'         => $strings['failure'],
			'data-select-options-text'  => $strings['select_options'],
		),
		skaflow_get_delivery_estimate_currency_atts()
	);

	$attributes = '';

	foreach ( $data_atts as $name => $value ) {
		$attributes .= '' === $value
			? ' ' . esc_attr( $name )
			: sprintf( ' %1$s="%2$s"', esc_attr( $name ), esc_attr( $value ) );
	}

	return sprintf(
		'<div class="sk-delivery-estimate__inner"%1$s>
			<div class="sk-delivery-estimate__header">
				<span class="sk-delivery-estimate__header-icon" aria-hidden="true"></span>
				<span class="sk-delivery-estimate__title">%2$s</span>
			</div>
			<form class="sk-delivery-estimate__form" novalidate>%3$s</form>
			<div class="sk-delivery-estimate__result" role="status" aria-live="polite" hidden></div>
			%4$s
		</div>',
		$attributes, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- names and values escaped above.
		esc_html( $strings['title'] ),
		$fields, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments.
		skaflow_get_delivery_estimate_templates_html( $strings ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped fragments.
	);
}
