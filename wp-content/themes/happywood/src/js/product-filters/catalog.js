/**
 * Product archive filters — catalog URLs and archive-shell swap.
 */

import { dispatchContentUpdated } from '../shared/delegated-events';
import {
	ACCORDION_OPEN_CLASS,
	ARCHIVE_SHELL_QUERY_ARG,
	BAR_BUTTON_SELECTOR,
	FILTER_SECTION_SELECTOR,
	OPEN_CLASS,
	SHELL_SELECTOR,
} from './config';
import {
	embedFilterPanelInRoot,
	getFilterPanel,
	getFiltersRoot,
	setFiltersSwapping,
	shouldPortalVerticalPanel,
	syncFilterPanelPortal,
} from './dom';
import {
	openAccordionForCard,
	setPanelOpen,
	syncAccordionToggleState,
} from './panel';

let activeFetchController = null;

/**
 * @param {URLSearchParams} params
 */
export function stripArchiveShellParam( params ) {
	params.delete( ARCHIVE_SHELL_QUERY_ARG );
}

/**
 * @return {URLSearchParams} Catalog query params from the current URL.
 */
export function getCatalogParamsFromUrl() {
	const params = new URLSearchParams( window.location.search );
	stripArchiveShellParam( params );

	return params;
}

/**
 * @return {string} Archive base path without /page/N/ segment.
 */
function getCatalogBasePath() {
	return window.location.pathname.replace( /\/page\/\d+\/?$/, '' ) || '/';
}

/**
 * Build a catalog URL from params.
 *
 * @param {URLSearchParams}         params
 * @param {{ stripSort?: boolean }} [options] Pass stripSort when clearing filters entirely.
 * @return {string} Catalog URL with the given params.
 */
export function buildCatalogUrl( params, options = {} ) {
	const next = new URLSearchParams( params );
	stripArchiveShellParam( next );

	if ( options.stripSort ) {
		next.delete( 'orderby' );
		next.delete( 'order' );
	}

	const query = next.toString();

	return query ? `${ getCatalogBasePath() }?${ query }` : getCatalogBasePath();
}

/**
 * Split WooCommerce hyphenated orderby values.
 *
 * @param {string|null} orderby
 * @param {string|null} order
 * @return {{ orderby: string|null, order: string|null }} Normalised orderby and order values.
 */
export function parseCatalogOrderby( orderby, order ) {
	if ( ! orderby ) {
		return { orderby: null, order: order || null };
	}

	const parts = orderby.split( '-' );

	if ( parts.length >= 2 && /^(asc|desc)$/i.test( parts[ parts.length - 1 ] ) ) {
		const direction = parts.pop().toUpperCase();

		return {
			orderby: parts.join( '-' ) || null,
			order: order || direction,
		};
	}

	return { orderby, order: order || null };
}

/**
 * Build the catalog URL for a full-page fetch (strip legacy partial query arg).
 *
 * @param {string} catalogUrl Public catalog URL.
 * @return {string} URL used to fetch the filtered catalog HTML.
 */
export function buildCatalogFetchUrl( catalogUrl ) {
	const url = new URL( catalogUrl, window.location.origin );

	stripArchiveShellParam( url.searchParams );

	return url.toString();
}

/**
 * Extract the archive shell node from a full-page HTML response.
 *
 * @param {string} html Full document HTML.
 * @return {HTMLElement|null} Archive shell element parsed from the HTML.
 */
function parseArchiveShellFromHtml( html ) {
	const doc = new DOMParser().parseFromString( html, 'text/html' );

	return doc.querySelector( SHELL_SELECTOR );
}

/**
 * @param {HTMLElement|null} root
 * @return {{ wasOpen: boolean, activeCard: string|null }} Panel open state to restore after a refresh.
 */
function getPanelRestoreState( root ) {
	if ( ! root ) {
		return { wasOpen: false, activeCard: null };
	}

	const panel = getFilterPanel();
	const wasOpen = Boolean( panel?.classList.contains( OPEN_CLASS ) );
	const activeCard =
		root
			.querySelector( `${ BAR_BUTTON_SELECTOR }.is-active` )
			?.getAttribute( 'data-filter-card' ) ||
		panel
			?.querySelector(
				`${ FILTER_SECTION_SELECTOR }.${ ACCORDION_OPEN_CLASS }`,
			)
			?.getAttribute( 'data-filter-card' ) ||
		null;

	return { wasOpen, activeCard };
}

/**
 * Replace the archive shell from a server-rendered HTML fragment.
 *
 * @param {string}                                  html
 * @param {{ push?: boolean, replaceUrl?: string }} [options]
 */
export function applyArchiveShellHtml( html, options = {} ) {
	const currentShell = document.querySelector( SHELL_SELECTOR );
	const root = getFiltersRoot();
	const newShell = parseArchiveShellFromHtml( html );
	const { wasOpen, activeCard } = getPanelRestoreState( root );

	if ( ! currentShell || ! newShell ) {
		setFiltersSwapping( false );
		return;
	}

	if ( wasOpen && shouldPortalVerticalPanel( root ) ) {
		document.documentElement.classList.add( 'hw-filters-open' );
		setFiltersSwapping( true );
	}

	if ( shouldPortalVerticalPanel( root ) ) {
		embedFilterPanelInRoot( root );
	}

	currentShell.outerHTML = newShell.outerHTML;

	const nextRoot = getFiltersRoot();

	if ( nextRoot ) {
		syncFilterPanelPortal( nextRoot );

		if ( wasOpen ) {
			setPanelOpen( nextRoot, true );

			if ( activeCard ) {
				openAccordionForCard( nextRoot, activeCard );
			} else {
				syncAccordionToggleState( getFilterPanel() );
			}
		}
	}

	setFiltersSwapping( false );

	if ( options.push && options.replaceUrl ) {
		window.history.pushState( { hwFilters: true }, '', options.replaceUrl );
	}

	dispatchContentUpdated( { source: 'filters' } );
}

/**
 * Fetch full catalog page HTML and swap the archive shell for filter/sort refresh.
 *
 * @param {{ push?: boolean, replaceUrl?: string }} [options]
 */
export async function refreshCatalogFromUrl( options = {} ) {
	const root = getFiltersRoot();
	const catalogUrl =
		options.replaceUrl || buildCatalogUrl( getCatalogParamsFromUrl() );

	if ( ! root || ! document.querySelector( SHELL_SELECTOR ) ) {
		return;
	}

	const fetchUrl = buildCatalogFetchUrl( catalogUrl );
	const panelWasOpen = Boolean(
		getFilterPanel()?.classList.contains( OPEN_CLASS ),
	);

	if ( activeFetchController ) {
		activeFetchController.abort();
	}

	activeFetchController = new AbortController();
	root.classList.add( 'is-loading' );

	if ( panelWasOpen && shouldPortalVerticalPanel( root ) ) {
		setFiltersSwapping( true );
	}

	try {
		const response = await fetch( fetchUrl, {
			method: 'GET',
			credentials: 'same-origin',
			signal: activeFetchController.signal,
		} );

		if ( ! response.ok ) {
			throw new Error( `Filter refresh failed (${ response.status })` );
		}

		const html = await response.text();

		applyArchiveShellHtml( html, {
			push: options.push,
			replaceUrl: catalogUrl,
		} );
	} catch ( error ) {
		if ( error?.name !== 'AbortError' ) {
			// eslint-disable-next-line no-console
			console.error( '[happywood product-filters]', error );
		}

		setFiltersSwapping( false );
	} finally {
		getFiltersRoot()?.classList.remove( 'is-loading' );
		activeFetchController = null;
	}
}
