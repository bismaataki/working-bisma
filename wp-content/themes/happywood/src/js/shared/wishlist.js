/**
 * Wishlist toggle (logged-in customers only).
 *
 * @see context/kb/plugin-wc-my-account.md
 */

import { delegateDocument } from './delegated-events';

/**
 * @return {Record<string, unknown>} Localized public config object.
 */
function getPublicConfig() {
	return window.Happywood_Public || {};
}

/**
 * @param {string} fallback
 */
function getLoginRedirectUrl( fallback ) {
	const config = getPublicConfig();
	const loginUrl =
		typeof config.wishlistLoginUrl === 'string'
			? config.wishlistLoginUrl
			: '';

	if ( ! loginUrl ) {
		return fallback;
	}

	try {
		const url = new URL( loginUrl, window.location.origin );
		const redirectParam =
			url.pathname.includes( 'my-account' ) ||
			url.pathname.includes( 'myaccount' )
				? 'redirect'
				: 'redirect_to';
		url.searchParams.set( redirectParam, fallback );
		return url.toString();
	} catch {
		return loginUrl;
	}
}

/**
 * @param {HTMLButtonElement} button
 * @param {boolean}           inWishlist
 */
function setButtonState( button, inWishlist ) {
	const config = getPublicConfig();
	const active = Boolean( inWishlist );

	button.classList.toggle( 'is-active', active );
	button.setAttribute( 'aria-pressed', active ? 'true' : 'false' );

	const label = active ? config.wishlistRemoveLabel : config.wishlistAddLabel;

	if ( typeof label === 'string' && label ) {
		button.setAttribute( 'aria-label', label );
	}

	const textLabel = button.querySelector( '.hw-wishlist-trigger__label' );

	if ( textLabel && typeof label === 'string' && label ) {
		textLabel.textContent = label;
	}

	const wrapper = button.closest( '.hw-wishlist-button' );

	if ( wrapper ) {
		wrapper.classList.toggle( 'is-active', active );
	}
}

/**
 * @param {HTMLButtonElement} button
 */
async function toggleWishlist( button ) {
	const config = getPublicConfig();

	if ( ! config.wishlistEnabled ) {
		return;
	}

	const productId = Number.parseInt( button.dataset.productId || '', 10 );

	if ( ! Number.isFinite( productId ) || productId <= 0 ) {
		return;
	}

	if ( ! config.wishlistIsLoggedIn ) {
		window.location.href = getLoginRedirectUrl( window.location.href );
		return;
	}

	const toggleUrl = config.wishlistToggleUrl;
	const nonce = config.nonce;

	if (
		typeof toggleUrl !== 'string' ||
		! toggleUrl ||
		typeof nonce !== 'string' ||
		! nonce
	) {
		return;
	}

	button.disabled = true;
	button.setAttribute( 'aria-busy', 'true' );

	try {
		const response = await fetch( toggleUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': nonce,
			},
			body: JSON.stringify( { productId } ),
		} );

		if ( response.status === 401 ) {
			window.location.href = getLoginRedirectUrl( window.location.href );
			return;
		}

		if ( ! response.ok ) {
			return;
		}

		const data = await response.json();
		setButtonState( button, data.inWishlist );

		document.dispatchEvent(
			new CustomEvent( 'happywood:wishlist-toggled', {
				detail: {
					productId,
					inWishlist: Boolean( data.inWishlist ),
					count: Number( data.count ) || 0,
				},
			} ),
		);
	} finally {
		button.disabled = false;
		button.removeAttribute( 'aria-busy' );
	}
}

/**
 * Bind delegated wishlist toggle handlers.
 */
export function initWishlist() {
	const config = getPublicConfig();

	if ( ! config.wishlistEnabled ) {
		return;
	}

	delegateDocument( 'click', '.hw-wishlist-trigger', ( event, button ) => {
		event.preventDefault();
		toggleWishlist( button );
	} );
}
