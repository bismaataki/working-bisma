/**
 * Product archive filters — panel UI + full-page fetch shell swap (PJAX-style).
 *
 * Desktop vertical: in-flow push sidebar (no portal/backdrop).
 * Mobile vertical: fixed drawer portaled to body. Desktop horizontal: dropdown unchanged.
 */

import {
	delegateDocument,
} from './shared/delegated-events';
import {
	ACCORDION_ALL_EXPANDED_CLASS,
	ACCORDION_OPEN_CLASS,
	ACCORDION_TOGGLE_SELECTOR,
	ACCORDION_TRIGGER_SELECTOR,
	BAR_BUTTON_SELECTOR,
	CHIP_SELECTOR,
	CLEAR_SELECTOR,
	FILTER_SECTION_SELECTOR,
	OPEN_CLASS,
	PANEL_CLOSE_SELECTOR,
	PANEL_SELECTOR,
	PRICE_APPLY_SELECTOR,
	ROOT_SELECTOR,
	SORT_SELECT_SELECTOR,
	TERM_SELECTOR,
} from './product-filters/config';
import {
	buildCatalogUrl,
	getCatalogParamsFromUrl,
	parseCatalogOrderby,
	refreshCatalogFromUrl,
} from './product-filters/catalog';
import {
	getDesktopBreakpoint,
	getFilterPanel,
	getFiltersRoot,
	isDesktopVerticalPushMode,
	isProductArchivePage,
	syncFilterPanelPortal,
	syncLayoutOpenState,
	syncPanelAccessibility,
	syncVerticalOverlayHtmlClass,
} from './product-filters/dom';
import {
	collapseAllAccordions,
	expandAllAccordions,
	openPanelForCard,
	setAccordionSectionOpen,
	setPanelOpen,
	syncAccordionToggleState,
} from './product-filters/panel';

/**
 * Toggle one layered-nav term in URL params.
 *
 * @param {string}          filterName
 * @param {string}          termSlug
 * @param {URLSearchParams} params
 */
function toggleFilterTermInParams( filterName, termSlug, params ) {
	const current = ( params.get( filterName ) || '' )
		.split( ',' )
		.map( ( slug ) => slug.trim() )
		.filter( Boolean );

	const index = current.indexOf( termSlug );

	if ( index >= 0 ) {
		current.splice( index, 1 );
	} else {
		current.push( termSlug );
	}

	if ( current.length ) {
		params.set( filterName, current.join( ',' ) );
	} else {
		params.delete( filterName );
	}
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
function handleBarButtonClick( event, button ) {
	event.preventDefault();

	const root = button.closest( ROOT_SELECTOR );

	if ( ! root ) {
		return;
	}

	const cardSlug = button.getAttribute( 'data-filter-card' ) || '';

	if ( ! cardSlug ) {
		return;
	}

	const panel = getFilterPanel();
	const isOpen = panel?.classList.contains( OPEN_CLASS );
	const isSame = button.classList.contains( 'is-active' );

	if ( isOpen && isSame ) {
		setPanelOpen( root, false );
		return;
	}

	openPanelForCard( root, cardSlug );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
async function handleFilterTermClick( event, button ) {
	event.preventDefault();

	const filterName = button.getAttribute( 'data-filter-name' );
	const termSlug = button.getAttribute( 'data-term-slug' );

	if ( ! filterName || ! termSlug ) {
		return;
	}

	const params = getCatalogParamsFromUrl();
	toggleFilterTermInParams( filterName, termSlug, params );

	await refreshCatalogFromUrl( {
		push: true,
		replaceUrl: buildCatalogUrl( params ),
	} );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
async function handleChipClick( event, button ) {
	event.preventDefault();

	const filterName = button.getAttribute( 'data-filter-name' );
	const termSlug = button.getAttribute( 'data-term-slug' );
	const params = getCatalogParamsFromUrl();

	if ( 'price' === filterName ) {
		params.delete( 'min_price' );
		params.delete( 'max_price' );
	} else if ( filterName && termSlug ) {
		toggleFilterTermInParams( filterName, termSlug, params );
	}

	await refreshCatalogFromUrl( {
		push: true,
		replaceUrl: buildCatalogUrl( params ),
	} );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
async function handleClearClick( event, button ) {
	event.preventDefault();

	const clearUrl =
		button.getAttribute( 'data-clear-url' ) ||
		button.closest( ROOT_SELECTOR )?.dataset.clearUrl ||
		window.location.pathname;

	await refreshCatalogFromUrl( {
		push: true,
		replaceUrl: clearUrl.split( '?' )[ 0 ],
	} );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
async function handlePriceApplyClick( event, button ) {
	event.preventDefault();

	const priceWrap = button.closest( '.sk-filter-price' );

	if ( ! priceWrap ) {
		return;
	}

	const minInput = priceWrap.querySelector( '.sk-filter-price__input--min' );
	const maxInput = priceWrap.querySelector( '.sk-filter-price__input--max' );
	const params = getCatalogParamsFromUrl();
	const minValue = minInput?.value ?? '';
	const maxValue = maxInput?.value ?? '';

	if ( minValue ) {
		params.set( 'min_price', String( minValue ) );
	} else {
		params.delete( 'min_price' );
	}

	if ( maxValue ) {
		params.set( 'max_price', String( maxValue ) );
	} else {
		params.delete( 'max_price' );
	}

	await refreshCatalogFromUrl( {
		push: true,
		replaceUrl: buildCatalogUrl( params ),
	} );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} trigger
 */
function handleAccordionTriggerClick( event, trigger ) {
	event.preventDefault();

	const section = trigger.closest( FILTER_SECTION_SELECTOR );

	if ( ! section ) {
		return;
	}

	setAccordionSectionOpen(
		section,
		! section.classList.contains( ACCORDION_OPEN_CLASS ),
	);
	syncAccordionToggleState( getFilterPanel() );
}

/**
 * @param {Event}       event
 * @param {HTMLElement} button
 */
function handleAccordionToggleClick( event, button ) {
	event.preventDefault();

	const panel = getFilterPanel();

	if ( ! panel ) {
		return;
	}

	if ( button.classList.contains( ACCORDION_ALL_EXPANDED_CLASS ) ) {
		collapseAllAccordions( panel );
		return;
	}

	expandAllAccordions( panel );
}

/**
 * @param {Event} event
 */
function handlePanelCloseClick( event ) {
	event.preventDefault();

	const root = getFiltersRoot();

	if ( root ) {
		setPanelOpen( root, false );
	}
}

/**
 * @param {Event} event
 */
function handleDocumentClick( event ) {
	const panel = document.querySelector( `${ PANEL_SELECTOR }.${ OPEN_CLASS }` );

	if ( ! panel ) {
		return;
	}

	const target = event.target;

	if ( ! ( target instanceof Element ) ) {
		return;
	}

	if ( target.closest( PANEL_SELECTOR ) || target.closest( BAR_BUTTON_SELECTOR ) ) {
		return;
	}

	const root = getFiltersRoot();

	if ( root ) {
		setPanelOpen( root, false );
	}
}

/**
 * @param {Event} event
 */
function handleEscapeKey( event ) {
	if ( event.key !== 'Escape' ) {
		return;
	}

	const panel = document.querySelector( `${ PANEL_SELECTOR }.${ OPEN_CLASS }` );
	const root = getFiltersRoot();

	if ( root && panel ) {
		setPanelOpen( root, false );
	}
}

/**
 * @param {Event}       event
 * @param {HTMLElement} select
 */
async function handleSortChange( event, select ) {
	if ( ! isProductArchivePage() ) {
		return;
	}

	if ( ! ( select instanceof HTMLSelectElement ) ) {
		return;
	}

	event.preventDefault();
	event.stopPropagation();

	const params = getCatalogParamsFromUrl();
	const parsed = parseCatalogOrderby( select.value, params.get( 'order' ) );

	if ( parsed.orderby ) {
		params.set( 'orderby', parsed.orderby );
	} else {
		params.delete( 'orderby' );
	}

	if ( parsed.order ) {
		params.set( 'order', parsed.order );
	} else {
		params.delete( 'order' );
	}

	await refreshCatalogFromUrl( {
		push: true,
		replaceUrl: buildCatalogUrl( params ),
	} );
}

/**
 * Refresh the catalog when the shopper uses browser history.
 */
function handlePopState() {
	if ( ! isProductArchivePage() ) {
		return;
	}

	refreshCatalogFromUrl();
}

/**
 * Register product archive filter interactions.
 */
export function initProductFilters() {
	if ( ! isProductArchivePage() ) {
		return;
	}

	const root = getFiltersRoot();

	if ( root ) {
		syncFilterPanelPortal( root );
		syncPanelAccessibility( root, false );
		syncAccordionToggleState( getFilterPanel() );

		const mq = getDesktopBreakpoint();
		mq?.addEventListener( 'change', () => {
			syncFilterPanelPortal( root );

			const panel = getFilterPanel();

			if ( panel?.classList.contains( OPEN_CLASS ) ) {
				syncLayoutOpenState( root, isDesktopVerticalPushMode( root ) );
				syncPanelAccessibility( root, true );
				syncVerticalOverlayHtmlClass( true );
				document.documentElement.classList.toggle(
					'sk-filters-open',
					! isDesktopVerticalPushMode( root ),
				);
			}
		} );
	}

	delegateDocument( 'click', BAR_BUTTON_SELECTOR, handleBarButtonClick );
	delegateDocument( 'click', TERM_SELECTOR, handleFilterTermClick );
	delegateDocument( 'click', CHIP_SELECTOR, handleChipClick );
	delegateDocument( 'click', CLEAR_SELECTOR, handleClearClick );
	delegateDocument( 'click', PRICE_APPLY_SELECTOR, handlePriceApplyClick );
	delegateDocument( 'click', PANEL_CLOSE_SELECTOR, handlePanelCloseClick );
	delegateDocument(
		'click',
		ACCORDION_TRIGGER_SELECTOR,
		handleAccordionTriggerClick,
	);
	delegateDocument(
		'click',
		ACCORDION_TOGGLE_SELECTOR,
		handleAccordionToggleClick,
	);

	document.addEventListener( 'click', handleDocumentClick );
	document.addEventListener( 'keydown', handleEscapeKey );
	delegateDocument( 'change', SORT_SELECT_SELECTOR, handleSortChange, true );
	delegateDocument(
		'submit',
		'.wp-block-woocommerce-catalog-sorting form, .wc-block-catalog-sorting form',
		( event ) => {
			if ( ! isProductArchivePage() ) {
				return;
			}

			event.preventDefault();
		},
	);
	window.addEventListener( 'popstate', handlePopState );
}
