/**
 * Cart chooser — localized config, address helpers, and Store API writes.
 */

import { dispatch, select } from '@wordpress/data';

export const { CART_STORE_KEY } = window.wc?.wcBlocksData || {};

/**
 * Namespace for Store API extension data. Must match the PHP side and
 * checkout.js.
 */
export const NAMESPACE = 'happywood';

export const { RadioControl, RadioControlAccordion, Textarea, TextInput, Title } =
	window.wc?.blocksComponents || {};

const localized = window.happywoodCartQuestions || {};
export const pickupLocations = Array.isArray( localized.pickupLocations )
	? localized.pickupLocations
	: [];
export const localizedAnswers =
	localized.answers && typeof localized.answers === 'object'
		? localized.answers
		: {};
export const estimate = localized.estimate || {};
export const shippingCountries =
	localized.countries && typeof localized.countries === 'object'
		? localized.countries
		: {};
const shippingCountryCodes = Object.keys( shippingCountries );
const soleShippingCountry =
	1 === shippingCountryCodes.length ? shippingCountryCodes[ 0 ] : '';
export const showCountryField = shippingCountryCodes.length > 1;
const shippingStates =
	localized.states && typeof localized.states === 'object'
		? localized.states
		: {};
export const isLoggedIn = Boolean( localized.isLoggedIn );

/**
 * Country code to post. One ship-to country wins even if the cart still
 * holds another; otherwise keep a valid saved code, then AU.
 *
 * @param {string} [country] Current or typed country.
 *
 * @return {string} Store API country code.
 */
export const resolveShippingCountry = ( country ) => {
	if ( soleShippingCountry ) {
		return soleShippingCountry;
	}

	if ( country && shippingCountries[ country ] ) {
		return country;
	}

	return 'AU';
};

export const statesForCountry = ( country ) =>
	shippingStates[ country ] && typeof shippingStates[ country ] === 'object'
		? shippingStates[ country ]
		: {};

export const countryHasStates = ( country ) =>
	Object.keys( statesForCountry( country ) ).length > 0;

export const countryLabel = ( country ) =>
	shippingCountries[ country ] || country || '';

const stateLabel = ( country, state ) =>
	statesForCountry( country )[ state ] || state || '';

/**
 * WooCommerce's currency symbol is an HTML entity (`&#36;`). Dataset
 * attributes decode it; `wp_localize_script` does not.
 *
 * @param {string} value Maybe-encoded string.
 *
 * @return {string} Plain text.
 */
const decodeHtmlEntities = ( value ) => {
	if ( ! value || typeof value !== 'string' || ! value.includes( '&' ) ) {
		return value || '';
	}

	const textarea = document.createElement( 'textarea' );
	textarea.innerHTML = value;
	return textarea.value;
};

/**
 * Format a plugin price the way the PDP estimate card does.
 *
 * @param {number} price Marked-up price from the endpoint.
 *
 * @return {string} Formatted price.
 */
export const formatEstimatePrice = ( price ) => {
	const {
		symbol = '',
		decimals = '2',
		decimalSep = '.',
		thousandSep = ',',
		format = '%1$s%2$s',
	} = estimate.currency || {};

	const places = Number.parseInt( decimals, 10 );
	const fixed = price.toFixed( Number.isNaN( places ) ? 2 : places );
	const [ whole, fraction ] = fixed.split( '.' );
	const grouped = whole.replace( /\B(?=(\d{3})+(?!\d))/g, thousandSep );
	const amount = fraction ? `${ grouped }${ decimalSep }${ fraction }` : grouped;

	return format
		.replace( '%1$s', decodeHtmlEntities( symbol ) )
		.replace( '%2$s', amount );
};

/**
 * Answers currently on the cart (session via extensions), falling back to
 * what PHP localised on page load.
 *
 * @return {Object} Partial answers.
 */
export const getStoredAnswers = () => {
	const fromStore =
		select( CART_STORE_KEY )?.getCartData?.()?.extensions?.[ NAMESPACE ];

	if ( fromStore && typeof fromStore === 'object' ) {
		return fromStore;
	}

	return localizedAnswers;
};

/**
 * Writes answers through the cart extensions door.
 *
 * @param {Object} answers Partial answers to merge on the session.
 *
 * @return {Promise} The cart store update.
 */
export const pushCartAnswers = ( answers ) =>
	dispatch( CART_STORE_KEY ).applyExtensionCartUpdate( {
		namespace: NAMESPACE,
		data: answers,
	} );

export const isPickupRate = ( rateId ) =>
	typeof rateId === 'string' && rateId.startsWith( 'pickup_location:' );

export const formatAddressLine = ( address ) => {
	if ( ! address || typeof address !== 'object' ) {
		return '';
	}

	const name = [ address.first_name, address.last_name ]
		.filter( Boolean )
		.join( ' ' );

	return [
		name,
		address.address_1,
		address.city,
		stateLabel( address.country, address.state ),
		address.postcode,
	]
		.filter( Boolean )
		.join( ', ' );
};

export const formatCardAddress = ( address ) => {
	const line = formatAddressLine( address );

	if ( ! line ) {
		return '';
	}

	if ( ! showCountryField ) {
		return line;
	}

	return [ line, countryLabel( address?.country ) ].filter( Boolean ).join( ', ' );
};

export const hasValue = ( value ) =>
	typeof value === 'string' && value.trim() !== '';

/**
 * Shipping path: the same required set as the checkout Shipping address
 * block, including phone.
 *
 * @param {Object} address Current shipping address.
 *
 * @return {boolean} Whether Continue can leave the address step.
 */
export const hasSavedAddress = ( address ) => {
	if (
		! hasValue( address?.first_name ) ||
		! hasValue( address?.last_name ) ||
		! hasValue( address?.address_1 ) ||
		! hasValue( address?.city ) ||
		! hasValue( address?.postcode ) ||
		! hasValue( address?.phone ) ||
		! address?.country
	) {
		return false;
	}

	if ( countryHasStates( address.country ) && ! address.state ) {
		return false;
	}

	return true;
};

/**
 * Pickup path: a warehouse, plus carrier name when a carrier is collecting.
 *
 * @param {string} rateId  Selected pickup_location rate.
 * @param {Object} answers Pickup answers.
 *
 * @return {boolean} Whether Continue can save the pickup path.
 */
export const hasCompletePickupPath = ( rateId, answers ) => {
	if ( ! rateId || ! answers?.collected_by ) {
		return false;
	}

	if ( answers.collected_by === 'carrier' && ! hasValue( answers.carrier_name ) ) {
		return false;
	}

	return true;
};

export const emptyAddress = ( shipping ) => {
	const country = resolveShippingCountry( shipping?.country );
	// Guest carts inherit the shop base (AU:WA). That is not a chosen
	// address — only keep the state once a street and postcode exist.
	const entered = Boolean( shipping?.address_1 && shipping?.postcode );
	const state = entered ? shipping?.state || '' : '';

	return {
		first_name: shipping?.first_name || '',
		last_name: shipping?.last_name || '',
		address_1: shipping?.address_1 || '',
		address_2: shipping?.address_2 || '',
		city: shipping?.city || '',
		state: statesForCountry( country )[ state ] ? state : '',
		postcode: shipping?.postcode || '',
		phone: shipping?.phone || '',
		country,
	};
};
