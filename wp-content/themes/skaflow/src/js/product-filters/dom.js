/**
 * Product archive filters — layout, orientation, and panel portal.
 */

import {
	DESKTOP_BREAKPOINT,
	LAYOUT_OPEN_CLASS,
	LAYOUT_SELECTOR,
	PANEL_INNER_SELECTOR,
	PANEL_SELECTOR,
	ROOT_SELECTOR,
	SIDEBAR_SELECTOR,
	SWAPPING_HTML_CLASS,
	VERTICAL_OPEN_HTML_CLASS,
} from './config';

/**
 * @return {HTMLElement|null} Filters root element, if present.
 */
export function getFiltersRoot() {
	return document.querySelector( ROOT_SELECTOR );
}

/**
 * @return {HTMLElement|null} Filter panel element, if present.
 */
export function getFilterPanel() {
	return document.querySelector( PANEL_SELECTOR );
}

/**
 * @return {boolean} Whether the current page is a product archive.
 */
export function isProductArchivePage() {
	return Boolean( getFiltersRoot() );
}

/**
 * @return {MediaQueryList|null} Desktop media query, or null when unavailable.
 */
export function getDesktopBreakpoint() {
	if ( typeof window.matchMedia !== 'function' ) {
		return null;
	}

	return window.matchMedia( DESKTOP_BREAKPOINT );
}

/**
 * @param {HTMLElement|null} root
 * @return {HTMLElement|null} Archive layout wrapper, if present.
 */
export function getArchiveLayout( root ) {
	return (
		root?.closest( LAYOUT_SELECTOR ) ||
		document.querySelector( LAYOUT_SELECTOR )
	);
}

/**
 * Desktop + vertical theme option → push sidebar (not mobile drawer).
 *
 * @param {HTMLElement|null} root
 * @return {boolean} Whether the vertical panel should push the grid.
 */
export function isDesktopVerticalPushMode( root ) {
	if ( ! root ) {
		return false;
	}

	const mq = getDesktopBreakpoint();

	if ( ! mq?.matches ) {
		return false;
	}

	return root.dataset.panelDesktop !== 'horizontal';
}

/**
 * Mobile vertical drawer still uses body portal + html overlay.
 *
 * @param {HTMLElement|null} root
 * @return {boolean} Whether the panel should portal to the document.
 */
export function shouldPortalVerticalPanel( root ) {
	if ( ! root || getActivePanelOrientation( root ) !== 'vertical' ) {
		return false;
	}

	return ! isDesktopVerticalPushMode( root );
}

/**
 * @param {HTMLElement} root
 * @return {'vertical'|'horizontal'} Active panel orientation.
 */
export function getActivePanelOrientation( root ) {
	const desktop =
		root.dataset.panelDesktop === 'horizontal' ? 'horizontal' : 'vertical';
	const mobile =
		root.dataset.panelMobile === 'horizontal' ? 'horizontal' : 'vertical';
	const mq = getDesktopBreakpoint();

	return mq?.matches ? desktop : mobile;
}

/**
 * @param {HTMLElement} root
 */
export function syncPanelOrientationClasses( root ) {
	const panel = getFilterPanel();

	if ( ! panel ) {
		return;
	}

	const orientation = getActivePanelOrientation( root );

	panel.classList.remove(
		'sk-filters-panel--vertical',
		'sk-filters-panel--horizontal',
	);
	panel.classList.add( `sk-filters-panel--${ orientation }` );
}

/**
 * @param {HTMLElement|null} root
 * @return {HTMLElement|null} Host element that owns the filter panel.
 */
export function getFilterPanelHost( root ) {
	if ( ! root ) {
		return null;
	}

	if ( shouldPortalVerticalPanel( root ) ) {
		return document.body;
	}

	if ( getActivePanelOrientation( root ) === 'horizontal' ) {
		return (
			root.querySelector( '.sk-shop-archive-filters-band > .alignwide' ) ||
			root.querySelector( '.sk-shop-archive-filters-band' ) ||
			root
		);
	}

	return root.querySelector( SIDEBAR_SELECTOR ) || root;
}

/**
 * Move the panel back into the correct host (before shell swap destroys portaled copy).
 *
 * @param {HTMLElement|null} root
 */
export function embedFilterPanelInRoot( root ) {
	const panel = getFilterPanel();

	if ( ! panel || ! root ) {
		return;
	}

	const host = getFilterPanelHost( root );

	if ( ! host || host.contains( panel ) ) {
		return;
	}

	host.appendChild( panel );
}

/**
 * Reparent panel for mobile drawer, horizontal dropdown, or vertical push sidebar.
 *
 * @param {HTMLElement|null} root
 */
export function syncFilterPanelPortal( root ) {
	const panel = getFilterPanel();

	if ( ! panel || ! root ) {
		return;
	}

	syncPanelOrientationClasses( root );

	const host = getFilterPanelHost( root );

	if ( ! host ) {
		return;
	}

	if ( panel.parentElement !== host ) {
		host.appendChild( panel );
	}
}

/**
 * @param {HTMLElement|null} root
 * @param {boolean}          isOpen
 */
export function syncLayoutOpenState( root, isOpen ) {
	const layout = getArchiveLayout( root );

	if ( ! layout || ! isDesktopVerticalPushMode( root ) ) {
		return;
	}

	layout.classList.toggle( LAYOUT_OPEN_CLASS, isOpen );
}

/**
 * @param {HTMLElement|null} root
 * @param {boolean}          isOpen
 */
export function syncPanelAccessibility( root, isOpen ) {
	const panel = getFilterPanel();
	const inner = panel?.querySelector( PANEL_INNER_SELECTOR );

	if ( ! inner ) {
		return;
	}

	if ( isDesktopVerticalPushMode( root ) ) {
		inner.setAttribute( 'role', 'region' );
		inner.removeAttribute( 'aria-modal' );
		panel?.removeAttribute( 'aria-modal' );
		return;
	}

	inner.setAttribute( 'role', 'dialog' );
	inner.setAttribute( 'aria-modal', isOpen ? 'true' : 'false' );
}

/**
 * @param {boolean} isOpen
 */
export function syncVerticalOverlayHtmlClass( isOpen ) {
	const root = getFiltersRoot();

	document.documentElement.classList.toggle(
		VERTICAL_OPEN_HTML_CLASS,
		isOpen && shouldPortalVerticalPanel( root ),
	);
}

/**
 * @param {boolean} swapping
 */
export function setFiltersSwapping( swapping ) {
	const root = getFiltersRoot();

	document.documentElement.classList.toggle(
		SWAPPING_HTML_CLASS,
		swapping && shouldPortalVerticalPanel( root ),
	);
}
