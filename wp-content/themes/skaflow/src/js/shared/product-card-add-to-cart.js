/**
 * Sync product-card add-to-cart labels with WooCommerce cart fragments.
 *
 * Simple ajax buttons show "X in cart" when the product is already in the cart,
 * matching WC Blocks product-button behaviour.
 */

import { CONTENT_UPDATED_EVENT } from './delegated-events';

const QUANTITIES_SELECTOR = '#sk-product-cart-quantities';
const BUTTON_SELECTOR = '.sk-add-to-cart .ajax_add_to_cart[data-product_id]';

/**
 * @return {Record<string, number>} Cart quantities keyed by product ID.
 */
function getQuantitiesFromDocument() {
	const node = document.querySelector( QUANTITIES_SELECTOR );

	if ( ! node?.textContent ) {
		return {};
	}

	try {
		const parsed = JSON.parse( node.textContent );

		return parsed && typeof parsed === 'object' ? parsed : {};
	} catch {
		return {};
	}
}

/**
 * @param {number} quantity
 * @return {string} Add-to-cart label when the product is in the cart.
 */
function getInCartLabel( quantity ) {
	const template = window.Skaflow_Public?.inCartLabel || '%d in cart';

	return template.replace( '%d', String( quantity ) );
}

/**
 * @param {HTMLAnchorElement} button
 * @return {string} Default add-to-cart label for the button.
 */
function getDefaultLabel( button ) {
	return button.dataset.skDefaultLabel || button.textContent.trim();
}

/**
 * @param {HTMLAnchorElement} button
 * @param {number}            quantity
 */
function updateButtonLabel( button, quantity ) {
	const defaultLabel = getDefaultLabel( button );
	const wrapper = button.closest( '.sk-add-to-cart' );

	if ( quantity > 0 ) {
		button.textContent = getInCartLabel( quantity );
		button.classList.add( 'sk-in-cart', 'added' );
	} else {
		button.textContent = defaultLabel;
		button.classList.remove( 'sk-in-cart', 'added' );
	}

	wrapper?.querySelector( ':scope > .added_to_cart' )?.remove();
}

/**
 * Apply cart quantities to all product-card ajax add-to-cart buttons.
 *
 * @param {Element|Document|DocumentFragment} [root]
 */
function syncProductCardAddToCartLabels( root = document ) {
	const quantities = getQuantitiesFromDocument();

	root.querySelectorAll( BUTTON_SELECTOR ).forEach( ( button ) => {
		if ( ! ( button instanceof HTMLAnchorElement ) ) {
			return;
		}

		const productId = parseInt( button.dataset.product_id || '', 10 );
		const quantity = Number.isFinite( productId )
			? quantities[ productId ] || 0
			: 0;

		updateButtonLabel( button, quantity );
	} );
}

/**
 * Wire WC cart fragment events to product-card button labels.
 */
export function initProductCardAddToCart() {
	const sync = () => syncProductCardAddToCartLabels();

	if ( typeof jQuery !== 'undefined' ) {
		jQuery( document.body ).on(
			'added_to_cart removed_from_cart wc_fragments_loaded wc_fragments_refreshed',
			sync,
		);
	}

	document.addEventListener( CONTENT_UPDATED_EVENT, ( event ) => {
		const root = event.detail?.root;

		if ( root instanceof Element || root instanceof DocumentFragment ) {
			syncProductCardAddToCartLabels( root );
			return;
		}

		sync();
	} );

	sync();
}
