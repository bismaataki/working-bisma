/**
 * Delivery estimate card — postcode lookup against a shipping-rate plugin.
 *
 * **Dormant in this repository.** No bundled plugin registers the action below,
 * so lookups return the error state until one does. The contract is documented
 * in `includes/woocommerce/delivery-estimate-functions.php`.
 *
 * Posts to the plugin's `cf_postcode_estimate` action on the single-product
 * path (`chk_page=1`), where the server prices the hypothesis "one of this
 * product, times this quantity" from the weight we send it. Nothing is in a
 * cart and nobody is charged, so the answer is advisory.
 *
 * Two rules from the plugin's contract shape the code below:
 *
 * - The endpoint requires POST with a same-origin `Origin`/`Referer`. `fetch()`
 *   from the site's own pages satisfies that with no extra work.
 * - `weight` is required and must not default. A missing, zero or non-numeric
 *   weight returns a 400, and it used to answer with the flat "up to 4kg" rate
 *   instead. So an unweighable product must not be submitted at all.
 *
 * Postcode validation is deliberately left to the server: it owns the rule and
 * authors the customer-facing message, and restating either here is how the two
 * drift apart.
 *
 * @see ../../includes/woocommerce/delivery-estimate-functions.php
 */

import {
	CONTENT_UPDATED_EVENT,
	delegateDocument,
} from '../../src/js/shared/delegated-events';

const ROOT_SELECTOR = '[data-hw-delivery-estimate]';
const FORM_SELECTOR = '.hw-delivery-estimate__form';
const POSTCODE_SELECTOR = '.hw-delivery-estimate__input--postcode';
const QUANTITY_SELECTOR = '.hw-delivery-estimate__input--quantity';
const SUBMIT_SELECTOR = '.hw-delivery-estimate__submit';
const RESULT_SELECTOR = '.hw-delivery-estimate__result';
const TEMPLATE_SELECTOR = '.hw-delivery-estimate__template';

const INIT_FLAG = 'cfDeliveryEstimateInitialized';
const DEFAULT_WEIGHT_FLAG = 'cfDeliveryEstimateDefaultWeight';
const REQUEST_FLAG = 'cfDeliveryEstimateRequestToken';

let listenersBound = false;

/**
 * Format a price the way the store formats prices elsewhere.
 *
 * @param {HTMLElement} root  Estimate root carrying the currency data attributes.
 * @param {number}      price Marked-up price from the endpoint.
 * @return {string} Formatted price.
 */
function formatPrice( root, price ) {
	const {
		priceSymbol = '',
		priceDecimals = '2',
		priceDecimalSep = '.',
		priceThousandSep = ',',
		priceFormat = '%1$s%2$s',
	} = root.dataset;

	const decimals = Number.parseInt( priceDecimals, 10 );
	const fixed = price.toFixed( Number.isNaN( decimals ) ? 2 : decimals );
	const [ whole, fraction ] = fixed.split( '.' );
	const grouped = whole.replace( /\B(?=(\d{3})+(?!\d))/g, priceThousandSep );
	const amount = fraction
		? `${ grouped }${ priceDecimalSep }${ fraction }`
		: grouped;

	return priceFormat.replace( '%1$s', priceSymbol ).replace( '%2$s', amount );
}

/**
 * Replace the result region's contents.
 *
 * @param {HTMLElement}      root Estimate root.
 * @param {Node|string|null} node Result node, plain text, or null to clear.
 */
function setResult( root, node ) {
	const result = root.querySelector( RESULT_SELECTOR );

	if ( ! result ) {
		return;
	}

	result.textContent = '';

	if ( ! node ) {
		result.setAttribute( 'hidden', '' );
		return;
	}

	if ( typeof node === 'string' ) {
		result.textContent = node;
	} else {
		result.appendChild( node );
	}

	result.removeAttribute( 'hidden' );
}

/**
 * Clone one of the PHP-authored result templates.
 *
 * @param {HTMLElement} root  Estimate root.
 * @param {string}      state Template state: `priced`, `unpriced` or `notice`.
 * @return {DocumentFragment|null} Cloned content, or null when absent.
 */
function cloneTemplate( root, state ) {
	const template = root.querySelector(
		`${ TEMPLATE_SELECTOR }[data-state="${ state }"]`,
	);

	return template ? template.content.cloneNode( true ) : null;
}

/**
 * Render a short message through the notice template.
 *
 * @param {HTMLElement} root    Estimate root.
 * @param {string}      message Message to display.
 */
function showNotice( root, message ) {
	const fragment = cloneTemplate( root, 'notice' );

	if ( ! fragment ) {
		setResult( root, message );
		return;
	}

	const field = fragment.querySelector( '[data-field="message"]' );

	if ( field ) {
		field.textContent = message;
	}

	setResult( root, fragment );
}

/**
 * @param {HTMLElement} root      Estimate root.
 * @param {boolean}     isLoading Whether a request is in flight.
 */
function setLoading( root, isLoading ) {
	const submit = root.querySelector( SUBMIT_SELECTOR );

	root.classList.toggle( 'is-loading', isLoading );

	if ( submit ) {
		submit.disabled = isLoading;
	}

	if ( isLoading ) {
		setResult( root, root.dataset.loadingText || '' );
	}
}

/**
 * Render whatever the endpoint answered.
 *
 * @param {HTMLElement} root     Estimate root.
 * @param {number}      httpCode Response status code.
 * @param {Object}      payload  Parsed JSON body.
 */
function renderResponse( root, httpCode, payload ) {
	const status = payload?.status;

	if ( 'priced' === status && 'number' === typeof payload.price ) {
		const fragment = cloneTemplate( root, 'priced' );

		if ( fragment ) {
			const field = fragment.querySelector( '[data-field="price"]' );

			if ( field ) {
				field.textContent = formatPrice( root, payload.price );
			}

			setResult( root, fragment );
			return;
		}
	}

	// An unpriced lookup is a successful one whose answer is "we'll quote you",
	// but there are four ways to get there and they are not interchangeable —
	// blaming the postcode for a weight limit is a claim the customer can
	// disprove by lowering the quantity. The plugin sends both which one it was
	// and the sentence to show, so `reason` is used only for icon and tone.
	if ( 'unpriced' === status ) {
		const fragment = cloneTemplate( root, 'unpriced' );

		if ( fragment ) {
			const panel = fragment.querySelector(
				'.hw-delivery-estimate__quote',
			);
			const field = fragment.querySelector( '[data-field="message"]' );

			if ( panel && payload.reason ) {
				panel.classList.add(
					`hw-delivery-estimate__quote--${ String(
						payload.reason,
					).replace( /_/g, '-' ) }`,
				);
			}

			if ( field ) {
				field.textContent = payload.message || '';
			}

			setResult( root, fragment );

			// An unrecognised postcode is the one unpriced outcome the customer
			// can act on, so put the cursor back where the fix is. This is the
			// actionability cue in place of a red panel — see style.scss for why
			// colour was the wrong signal — so it selects the text too, letting
			// them retype without clearing it first.
			if ( 'location_unknown' === payload.reason ) {
				const postcode = root.querySelector( POSTCODE_SELECTOR );

				if ( postcode ) {
					postcode.focus();
					postcode.select();
				}
			}

			return;
		}
	}

	// 400s carry a message written for the customer. A 403 means the request is
	// wrong rather than their input, so its message is never shown.
	if ( 400 === httpCode && payload?.message ) {
		showNotice( root, payload.message );
		return;
	}

	showNotice( root, root.dataset.failureText || '' );
}

/**
 * @param {HTMLElement} root Estimate root.
 */
async function submitEstimate( root ) {
	const weight = ( root.dataset.weight || '' ).trim();

	if ( ! weight ) {
		showNotice( root, root.dataset.selectOptionsText || '' );
		return;
	}

	const postcodeInput = root.querySelector( POSTCODE_SELECTOR );
	const quantityInput = root.querySelector( QUANTITY_SELECTOR );

	// The quantity field starts empty by design. Checking a postcode without
	// touching it means one unit, so fill it in rather than guessing silently.
	let quantity = Number.parseInt( quantityInput?.value ?? '', 10 );

	if ( ! Number.isFinite( quantity ) || quantity < 1 ) {
		quantity = 1;

		if ( quantityInput ) {
			quantityInput.value = String( quantity );
		}
	}

	const token = Symbol( 'hw-delivery-estimate' );
	root[ REQUEST_FLAG ] = token;

	setLoading( root, true );

	const body = new URLSearchParams( {
		action: root.dataset.action || 'cf_postcode_estimate',
		zone_data: postcodeInput?.value?.trim() ?? '',
		item_data: String( quantity ),
		weight,
		chk_page: root.dataset.chkPage || '1',
	} );

	try {
		const response = await fetch( root.dataset.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: body.toString(),
		} );

		let payload = null;

		try {
			payload = await response.json();
		} catch {
			payload = null;
		}

		if ( root[ REQUEST_FLAG ] !== token ) {
			return;
		}

		setLoading( root, false );
		renderResponse( root, response.status, payload );
	} catch {
		if ( root[ REQUEST_FLAG ] !== token ) {
			return;
		}

		setLoading( root, false );
		showNotice( root, root.dataset.failureText || '' );
	}
}

/**
 * Point a card at a new per-unit weight and drop any stale answer.
 *
 * A price for the variation the customer just switched away from is worse than
 * no price, so the result region is cleared rather than left standing.
 *
 * @param {HTMLElement} root   Estimate root.
 * @param {string}      weight Weight in kilograms, or '' when unknown.
 */
function setWeight( root, weight ) {
	root.dataset.weight = weight;
	root[ REQUEST_FLAG ] = null;
	setLoading( root, false );
	setResult( root, null );
}

/**
 * @param {(root: HTMLElement) => void} callback Applied to every card on the page.
 */
function eachRoot( callback ) {
	document.querySelectorAll( ROOT_SELECTOR ).forEach( callback );
}

/**
 * WooCommerce fires `found_variation` and `reset_data` on jQuery only, so this
 * is the same scoped exception `single-product-swatches.js` makes.
 */
function bindVariationListeners() {
	if ( 'undefined' === typeof jQuery ) {
		return;
	}

	jQuery( document ).on(
		'found_variation',
		'.variations_form',
		( event, variation ) => {
			const weight = variation?.weight;
			const resolved =
				weight && Number.parseFloat( weight ) > 0 ? String( weight ) : '';

			eachRoot( ( root ) => setWeight( root, resolved ) );
		},
	);

	jQuery( document ).on( 'reset_data', '.variations_form', () => {
		eachRoot( ( root ) => setWeight( root, root[ DEFAULT_WEIGHT_FLAG ] || '' ) );
	} );
}

/**
 * @param {Element|Document|DocumentFragment} [scope=document]
 */
function initDeliveryEstimates( scope = document ) {
	scope.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		if ( root[ INIT_FLAG ] ) {
			return;
		}

		root[ INIT_FLAG ] = true;
		root[ DEFAULT_WEIGHT_FLAG ] = root.dataset.weight || '';
	} );
}

function bindListeners() {
	if ( listenersBound ) {
		return;
	}

	listenersBound = true;

	delegateDocument( 'submit', FORM_SELECTOR, ( event, form ) => {
		event.preventDefault();

		const root = form.closest( ROOT_SELECTOR );

		if ( root ) {
			submitEstimate( root );
		}
	} );

	bindVariationListeners();
}

initDeliveryEstimates();
bindListeners();

document.addEventListener( CONTENT_UPDATED_EVENT, () => {
	initDeliveryEstimates();
} );

document.addEventListener( 'DOMContentLoaded', () => {
	initDeliveryEstimates();
	bindListeners();
} );
