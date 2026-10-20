/**
 * Quick View popup — shell, fetch, open/close.
 *
 * @see context/kb/grid-product.md
 */

import jQuery from 'jquery';

import { delegateDocument } from './shared/delegated-events';
import {
	bindQuickViewGalleryListeners,
	initQuickViewGalleries,
} from './quick-view-gallery';
import { initQuickViewDetails } from './quick-view-details';
import { primeVariationForms } from './single-product-swatches';

/** @type {HTMLElement|null} */
let shellRoot = null;

/** @type {HTMLElement|null} */
let lastTrigger = null;

/** @type {boolean} */
let isClosing = false;

const TRANSITION_MS = 400;

/**
 * @return {Record<string, string|undefined>} Localized public config object.
 */
function getPublicConfig() {
	return window.Happywood_Public || {};
}

/**
 * @return {'modal'|'drawer'} Quick-view display mode.
 */
function getDisplayMode() {
	const mode = getPublicConfig().quickViewDisplay;

	return mode === 'modal' ? 'modal' : 'drawer';
}

/**
 * Build the popup shell once on first open.
 */
function ensureShell() {
	if ( shellRoot ) {
		return;
	}

	const display = getDisplayMode();

	shellRoot = document.createElement( 'div' );
	shellRoot.id = 'hw-quick-view';
	shellRoot.className = `hw-quick-view hw-quick-view--${ display }`;
	shellRoot.hidden = true;
	shellRoot.inert = true;
	shellRoot.innerHTML = `
		<div class="hw-quick-view__backdrop" data-hw-quick-view-close></div>
		<div
			class="hw-quick-view__panel"
			role="dialog"
			aria-modal="true"
			aria-labelledby="hw-quick-view-title"
			tabindex="-1"
		>
			<button type="button" class="hw-quick-view__close" data-hw-quick-view-close aria-label="Close quick view">
				<span aria-hidden="true">&times;</span>
			</button>
			<div class="hw-quick-view__content woocommerce"></div>
		</div>
	`.trim();

	document.body.appendChild( shellRoot );

	shellRoot.addEventListener( 'click', handleShellClick );
	document.addEventListener( 'keydown', handleDocumentKeydown );
	shellRoot.addEventListener( 'transitionend', handleTransitionEnd );
}

/**
 * @return {HTMLElement|null} Quick-view content element, if present.
 */
function getContentEl() {
	return shellRoot?.querySelector( '.hw-quick-view__content' ) || null;
}

/**
 * @return {HTMLElement|null} Quick-view panel element, if present.
 */
function getPanelEl() {
	return shellRoot?.querySelector( '.hw-quick-view__panel' ) || null;
}

/**
 * @param {boolean} loading
 */
function setLoading( loading ) {
	if ( ! shellRoot ) {
		return;
	}

	shellRoot.classList.toggle( 'hw-quick-view--loading', loading );

	const content = getContentEl();

	if ( loading && content ) {
		content.innerHTML =
			'<div class="hw-quick-view__loader" role="status"><span class="screen-reader-text">Loading product…</span></div>';
	}
}

/**
 * @param {string} html
 */
function injectContent( html ) {
	const content = getContentEl();

	if ( ! content ) {
		return;
	}

	content.innerHTML = html;
	initQuickViewProductScripts( content );
}

/**
 * Initialize variation forms and Swiper gallery after content is injected.
 *
 * @param {HTMLElement} content
 */
function finalizeQuickViewContent( content ) {
	primeVariationForms( content );
	initQuickViewGalleries( content );
	initQuickViewDetails( content );
}

/**
 * Initialize WooCommerce variation form + gallery inside injected markup.
 *
 * @param {HTMLElement} root
 */
function initQuickViewProductScripts( root ) {
	const $root = jQuery( root );

	$root.find( '.variations_form' ).each( function initVariationForm() {
		const $form = jQuery( this );

		if ( $form.data( 'hw_variation_form_initialized' ) ) {
			return;
		}

		$form.data( 'hw_variation_form_initialized', true );

		if ( typeof $form.wc_variation_form === 'function' ) {
			$form.wc_variation_form();
		}
	} );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} trigger
 */
async function handleTriggerClick( event, trigger ) {
	event.preventDefault();

	const productId = trigger.dataset.productId;

	if ( ! productId ) {
		return;
	}

	const restBase = getPublicConfig().quickViewRestUrl;

	if ( ! restBase ) {
		return;
	}

	lastTrigger = trigger;
	ensureShell();

	if ( ! shellRoot ) {
		return;
	}

	openShell();
	setLoading( true );

	try {
		const response = await fetch(
			`${ String( restBase ).replace( /\/$/, '' ) }/${ productId }`,
			{
				headers: {
					'X-WP-Nonce': getPublicConfig().nonce || '',
				},
			},
		);

		const data = await response.json();

		if ( ! response.ok || ! data?.html ) {
			throw new Error( 'Quick view response empty' );
		}

		injectContent( data.html );

		const content = getContentEl();

		if ( content ) {
			finalizeQuickViewContent( content );
		}

		setLoading( false );

		const panel = getPanelEl();
		panel?.focus();
	} catch {
		setLoading( false );
		closeShell( true );
	}
}

/**
 * @param {boolean} [immediate=false]
 */
function closeShell( immediate = false ) {
	if ( ! shellRoot?.classList.contains( 'is-open' ) ) {
		if ( immediate ) {
			finishClose();
		}
		return;
	}

	isClosing = true;
	shellRoot.classList.remove( 'is-open' );
	document.documentElement.classList.remove( 'hw-quick-view-open' );

	if ( immediate ) {
		finishClose();
		return;
	}

	window.setTimeout( () => {
		if ( isClosing ) {
			finishClose();
		}
	}, TRANSITION_MS + 50 );
}

function finishClose() {
	if ( ! shellRoot ) {
		return;
	}

	isClosing = false;

	const content = getContentEl();

	if ( content ) {
		content.innerHTML = '';
	}

	shellRoot.hidden = true;
	shellRoot.inert = true;
	shellRoot.classList.remove( 'hw-quick-view--loading' );

	if ( lastTrigger ) {
		lastTrigger.focus();
	}
}

function openShell() {
	if ( ! shellRoot ) {
		return;
	}

	isClosing = false;

	shellRoot.hidden = false;
	shellRoot.inert = false;
	document.documentElement.classList.add( 'hw-quick-view-open' );

	window.requestAnimationFrame( () => {
		shellRoot?.classList.add( 'is-open' );
	} );
}

/**
 * @param {Event} event
 */
function handleShellClick( event ) {
	const target = event.target;

	if (
		target instanceof HTMLElement &&
		target.closest( '[data-hw-quick-view-close]' )
	) {
		event.preventDefault();
		closeShell();
	}
}

/**
 * @param {KeyboardEvent} event
 */
function handleDocumentKeydown( event ) {
	if ( event.key === 'Escape' && shellRoot?.classList.contains( 'is-open' ) ) {
		event.preventDefault();
		closeShell();
	}
}

/**
 * @param {TransitionEvent} event
 */
function handleTransitionEnd( event ) {
	if (
		! shellRoot ||
		shellRoot.classList.contains( 'is-open' ) ||
		! isClosing ||
		event.target !== getPanelEl()
	) {
		return;
	}

	finishClose();
}

/**
 * Bind delegated quick-view triggers and prepare for lazy shell creation.
 */
export function initQuickView() {
	bindQuickViewGalleryListeners();
	delegateDocument( 'click', '.hw-quick-view-trigger', handleTriggerClick );
}
