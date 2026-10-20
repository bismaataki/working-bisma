/**
 * My Account front-end entry.
 *
 * Loads only on is_account_page(). Do not import sass/index.scss or public.js.
 */
import './sass/woocommerce/_my-account.scss';

/**
 * When a heart is turned off on the wishlist screen, drop that card and
 * refresh the header count. Toggle itself stays in public wishlist.js.
 * @param {HTMLElement} page
 * @param {number}      count
 */
function formatWishlistCount( page, count ) {
	const template =
		1 === count
			? page.getAttribute( 'data-item-singular' )
			: page.getAttribute( 'data-item-plural' );

	if ( ! template ) {
		return String( count );
	}

	return template.replace( '%s', String( count ) );
}

function initAccountWishlistList() {
	const page = document.querySelector( '.hw-account-wishlist' );

	if ( ! page ) {
		return;
	}

	document.addEventListener( 'happywood:wishlist-toggled', ( event ) => {
		const detail = event.detail || {};

		if ( detail.inWishlist ) {
			return;
		}

		const productId = Number( detail.productId );

		if ( ! Number.isFinite( productId ) || productId <= 0 ) {
			return;
		}

		const card = page.querySelector(
			`.hw-account-wishlist__grid > li[data-product-id="${ productId }"]`,
		);

		if ( card ) {
			card.remove();
		}

		const remaining = page.querySelectorAll(
			'.hw-account-wishlist__grid > li',
		).length;
		const countEl = page.querySelector( '.hw-account-wishlist__head-count' );
		const filtersActive = '1' === page.getAttribute( 'data-filters-active' );
		const emptyEl = page.querySelector(
			filtersActive
				? '.hw-account-wishlist__empty--filtered'
				: '.hw-account-wishlist__empty--none',
		);
		const grid = page.querySelector( '.hw-account-wishlist__grid' );
		const pager = page.querySelector( '.hw-account-wishlist__pagination' );
		const storedTotal = Number( page.getAttribute( 'data-item-total' ) );
		const total = Number.isFinite( storedTotal )
			? Math.max( 0, storedTotal - 1 )
			: remaining;

		page.setAttribute( 'data-item-total', String( total ) );

		if ( countEl ) {
			countEl.textContent = formatWishlistCount( page, total );
		}

		if ( 0 === remaining ) {
			if ( grid ) {
				grid.remove();
			}
		}

		if ( 0 === total ) {
			if ( pager ) {
				pager.remove();
			}

			if ( emptyEl ) {
				emptyEl.hidden = false;
			}
		}
	} );
}

initAccountWishlistList();
