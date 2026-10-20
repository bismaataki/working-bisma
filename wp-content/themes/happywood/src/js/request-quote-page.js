/**
 * Request a Quote page — empty list guard, clear form, and swatch reload.
 */

import jQuery from 'jquery';

const EMPTY_CLASS = 'hw-request-quote-is-empty';
let quoteSwatchReloadBound = false;

function getQuoteTable() {
	return document.querySelector( '#yith-ywrq-table-list' );
}

function getQuoteItems() {
	const table = getQuoteTable();
	if ( ! table ) {
		return [];
	}

	return table.querySelectorAll( 'tbody tr.cart_item' );
}

function getSubmitButton() {
	return document.querySelector(
		'#yith-ywraq-default-form .raq-send-request, #yith-ywraq-default-form button[type="submit"], #yith-ywraq-default-form input[type="submit"]',
	);
}

function getEmptyMessage() {
	const localized = window.Happywood_Public?.requestQuote?.emptyListMessage;

	return (
		localized ||
		'No products selected. Add items to your quote from the shop to continue.'
	);
}

function setPageEmptyState( isEmpty ) {
	const page = document.querySelector( '.hw-request-quote-page' );

	if ( page ) {
		page.classList.toggle( EMPTY_CLASS, isEmpty );
	}

	const submit = getSubmitButton();

	if ( submit ) {
		submit.disabled = isEmpty;
		submit.setAttribute( 'aria-disabled', isEmpty ? 'true' : 'false' );
	}
}

function syncEmptyState() {
	const isEmpty = getQuoteItems().length === 0;
	setPageEmptyState( isEmpty );
}

function showEmptyMessage() {
	const summary = document.querySelector( '.hw-request-quote-summary' );
	const target = summary?.querySelector( '.ywraq-before-form' ) || summary;

	if ( ! target ) {
		return;
	}

	let notice = target.querySelector( '.hw-request-quote-empty-notice' );

	if ( ! notice ) {
		notice = document.createElement( 'p' );
		notice.className = 'hw-request-quote-empty-notice';
		target.prepend( notice );
	}

	notice.textContent = getEmptyMessage();
	notice.hidden = false;
}

function bindSubmitGuard() {
	const submit = getSubmitButton();

	if ( ! submit || submit.dataset.hwQuoteGuardBound === '1' ) {
		return;
	}

	submit.dataset.hwQuoteGuardBound = '1';

	submit.addEventListener( 'click', ( event ) => {
		if ( getQuoteItems().length === 0 ) {
			event.preventDefault();
			showEmptyMessage();
		}
	} );
}

function bindClearForm() {
	const form = document.querySelector( '#yith-ywraq-default-form' );
	const clearButton = document.querySelector( '.hw-request-quote-clear-form' );

	if ( ! form || ! clearButton || clearButton.dataset.hwBound === '1' ) {
		return;
	}

	clearButton.dataset.hwBound = '1';

	clearButton.addEventListener( 'click', () => {
		form.reset();

		form.querySelectorAll( 'select' ).forEach( ( select ) => {
			select.selectedIndex = 0;
		} );

		form.querySelectorAll(
			'input[type="checkbox"], input[type="radio"]',
		).forEach( ( input ) => {
			input.checked = false;
		} );
	} );
}

function enhanceSubmitButton() {
	const input = document.querySelector(
		'#yith-ywraq-default-form input.raq-send-request[type="submit"]',
	);

	if ( ! input || input.dataset.hwEnhanced === '1' ) {
		return;
	}

	const iconSlug =
		window.Happywood_Public?.requestQuote?.submitButtonIcon ||
		'paper-plane';
	const label = input.value || 'Send Quote';
	const button = document.createElement( 'button' );

	button.type = 'submit';
	button.className =
		`${ input.className } hw-has-icon hw-icon-left hw-icon-${ iconSlug }`.trim();
	button.innerHTML = `<span class="hw-icon-preview hw-icon-${ iconSlug }" aria-hidden="true"></span>${ label }`;
	button.dataset.hwEnhanced = '1';

	if ( input.id ) {
		button.id = input.id;
	}

	if ( input.name ) {
		button.name = input.name;
	}

	input.replaceWith( button );
}

function bindQuoteMessageVisibility() {
	const messageEl = document.getElementById( 'yith-ywraq-message' );

	if ( ! messageEl ) {
		return;
	}

	const syncVisibility = () => {
		const hasContent = Boolean(
			messageEl.textContent?.trim() || messageEl.querySelector( 'p, form' ),
		);

		messageEl.hidden = ! hasContent;
	};

	syncVisibility();

	const observer = new MutationObserver( syncVisibility );
	observer.observe( messageEl, {
		childList: true,
		subtree: true,
		characterData: true,
	} );
}

function applyQuoteSwatchColors( root = document ) {
	root.querySelectorAll(
		'.hw-request-quote-summary [data-hw-swatch-color]',
	).forEach( ( element ) => {
		const color = element.getAttribute( 'data-hw-swatch-color' );

		if ( color ) {
			element.style.setProperty( 'background-color', color );
		}
	} );
}

function bindQuoteSwatchReload() {
	if ( quoteSwatchReloadBound ) {
		return;
	}

	quoteSwatchReloadBound = true;

	// YITH fires this with jQuery; native listeners do not receive it.
	jQuery( document ).on( 'ywraq_table_reloaded', () => {
		syncEmptyState();
		applyQuoteSwatchColors();
	} );
}

export function initRequestQuotePage() {
	if ( ! document.querySelector( '.hw-request-quote-page' ) ) {
		return;
	}

	syncEmptyState();
	enhanceSubmitButton();
	bindSubmitGuard();
	bindClearForm();
	bindQuoteMessageVisibility();
	bindQuoteSwatchReload();
	applyQuoteSwatchColors();

	document.addEventListener( 'ywraq_table_updated', syncEmptyState );
	document.addEventListener( 'removed_from_quote', syncEmptyState );
}
