/**
 * Product grid color swatches — hover/click image swap (Mode A).
 *
 * Loaded globally via public.js so Load More batches work when page 1
 * products omit swatch markup (block viewScript would never enqueue).
 *
 * @see context/kb/grid-product.md
 */

import { delegateDocument } from './delegated-events';

const SWATCH_SELECTOR = '.sk-swatches-grid .sk-swatch';
const DIVIDER_SELECTOR = '.sk-swatches-grid .sk-swatch-divider';
const PRODUCT_CARD_SELECTOR =
	'.wc-block-product, .product-grid-item, .product-teaser';

let productGridSwatchesInitialized = false;

/**
 * @return {boolean} Whether the current pointer is coarse (touch).
 */
function isTouchDevice() {
	return (
		'ontouchstart' in window ||
		( navigator.maxTouchPoints && navigator.maxTouchPoints > 0 )
	);
}

/**
 * @param {Element} swatch
 * @return {Element|null} Product card that owns the swatch, if present.
 */
function getProductCard( swatch ) {
	return swatch.closest( PRODUCT_CARD_SELECTOR );
}

/**
 * @param {Element} product
 * @return {HTMLImageElement|null} Primary product image in the card, if present.
 */
function getProductImage( product ) {
	return product.querySelector(
		'.wc-block-components-product-image img, .product-image-link > img, .product-image-link > picture > img',
	);
}

/**
 * @param {HTMLImageElement} image
 */
function cacheOriginalImage( image ) {
	if ( ! image.dataset.skOriginalSrc ) {
		image.dataset.skOriginalSrc = image.getAttribute( 'src' ) || '';
	}

	if ( ! image.dataset.skOriginalSrcset ) {
		image.dataset.skOriginalSrcset = image.getAttribute( 'srcset' ) || '';
	}

	if ( ! image.dataset.skOriginalSizes ) {
		image.dataset.skOriginalSizes = image.getAttribute( 'sizes' ) || '';
	}
}

/**
 * Swap the card thumbnail to the swatch's variation image (swap-and-stay).
 *
 * @param {Element} swatch
 */
function swapProductImage( swatch ) {
	const imageSrc = swatch.dataset.imageSrc;

	if ( ! imageSrc ) {
		return;
	}

	const product = getProductCard( swatch );

	if ( ! product ) {
		return;
	}

	const image = getProductImage( product );

	if ( ! image ) {
		return;
	}

	const imageSrcset = swatch.dataset.imageSrcset || '';
	const imageSizes = swatch.dataset.imageSizes || '';

	cacheOriginalImage( image );

	if ( image.getAttribute( 'src' ) === imageSrc ) {
		return;
	}

	const swatchGroup = swatch.closest( '.sk-swatches-grid' );

	swatchGroup
		?.querySelectorAll( '.sk-swatch--active' )
		.forEach( ( activeSwatch ) => {
			activeSwatch.classList.remove( 'sk-swatch--active' );
		} );

	swatch.classList.add( 'sk-swatch--active' );
	product.classList.add( 'sk-product-swatched' );
	product.classList.add( 'sk-loading-image' );

	const onLoaded = () => {
		product.classList.remove( 'sk-loading-image' );
	};

	if ( image.complete ) {
		onLoaded();
	} else {
		image.addEventListener( 'load', onLoaded, { once: true } );
		image.addEventListener( 'error', onLoaded, { once: true } );
	}

	image.setAttribute( 'src', imageSrc );

	if ( imageSrcset ) {
		image.setAttribute( 'srcset', imageSrcset );
	}

	if ( imageSizes ) {
		image.setAttribute( 'sizes', imageSizes );
	}

	const pictureSource = product.querySelector(
		'.wc-block-components-product-image picture source, .product-image-link picture source',
	);

	if ( pictureSource && imageSrcset ) {
		pictureSource.setAttribute( 'srcset', imageSrcset );

		if ( imageSizes ) {
			pictureSource.setAttribute( 'sizes', imageSizes );
		}
	}
}

/**
 * @param {Element} target Swatch or divider inside a limited group.
 */
function expandSwatches( target ) {
	const group = target.closest( '.sk-swatches-product' );

	if ( ! group || group.classList.contains( 'sk-swatches--all-shown' ) ) {
		return;
	}

	group.classList.add( 'sk-swatches--all-shown' );
	group.querySelectorAll( '.sk-swatch--hidden' ).forEach( ( hiddenSwatch ) => {
		hiddenSwatch.classList.remove( 'sk-swatch--hidden' );
	} );
}

/**
 * @param {Element} swatch
 */
function handleSwatchActivate( swatch ) {
	const group = swatch.closest( '.sk-swatches-product' );

	if (
		group?.classList.contains( 'sk-swatches--limited' ) &&
		! group.classList.contains( 'sk-swatches--all-shown' )
	) {
		expandSwatches( swatch );
	}

	swapProductImage( swatch );
}

/**
 * Bind delegated grid swatch handlers (safe to call once).
 */
export function initProductGridSwatches() {
	if ( productGridSwatchesInitialized ) {
		return;
	}

	productGridSwatchesInitialized = true;

	delegateDocument( 'click', DIVIDER_SELECTOR, ( event, divider ) => {
		event.preventDefault();
		expandSwatches( divider );
	} );

	delegateDocument( 'click', SWATCH_SELECTOR, ( event, swatch ) => {
		event.preventDefault();
		handleSwatchActivate( swatch );
	} );

	if ( ! isTouchDevice() ) {
		delegateDocument( 'mouseover', SWATCH_SELECTOR, ( event, swatch ) => {
			if ( swatch.contains( event.relatedTarget ) ) {
				return;
			}

			if ( swatch.classList.contains( 'sk-swatch--hidden' ) ) {
				return;
			}

			handleSwatchActivate( swatch );
		} );
	}
}
