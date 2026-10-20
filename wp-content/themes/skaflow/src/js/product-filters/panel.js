/**
 * Product archive filters — panel open state and accordion sections.
 */

import {
	ACCORDION_ALL_EXPANDED_CLASS,
	ACCORDION_OPEN_CLASS,
	ACCORDION_TRIGGER_SELECTOR,
	ACCORDION_TOGGLE_SELECTOR,
	BAR_BUTTON_SELECTOR,
	FILTER_SECTION_SELECTOR,
	OPEN_CLASS,
	PANEL_INNER_SELECTOR,
} from './config';
import {
	getFilterPanel,
	isDesktopVerticalPushMode,
	syncFilterPanelPortal,
	syncLayoutOpenState,
	syncPanelAccessibility,
	syncVerticalOverlayHtmlClass,
} from './dom';

/**
 * @param {HTMLElement} root
 * @param {boolean}     isOpen
 */
export function setPanelOpen( root, isOpen ) {
	const panel = getFilterPanel();

	if ( ! panel ) {
		return;
	}

	panel.classList.toggle( OPEN_CLASS, isOpen );
	panel.hidden = ! isOpen;
	panel.inert = ! isOpen;
	panel.setAttribute( 'aria-hidden', isOpen ? 'false' : 'true' );

	const backdrop = panel.querySelector( '.sk-filters-panel__backdrop' );

	if ( backdrop ) {
		backdrop.hidden = ! isOpen;
		backdrop.setAttribute( 'aria-hidden', isOpen ? 'false' : 'true' );
	}

	root.querySelectorAll( BAR_BUTTON_SELECTOR ).forEach( ( button ) => {
		if ( ! isOpen ) {
			button.classList.remove( 'is-active' );
			button.setAttribute( 'aria-expanded', 'false' );
			return;
		}

		button.setAttribute(
			'aria-expanded',
			button.classList.contains( 'is-active' ) ? 'true' : 'false',
		);
	} );

	syncLayoutOpenState( root, isOpen );
	syncPanelAccessibility( root, isOpen );

	const lockDocumentScroll = isOpen && ! isDesktopVerticalPushMode( root );
	document.documentElement.classList.toggle(
		'sk-filters-open',
		lockDocumentScroll,
	);
	syncVerticalOverlayHtmlClass( isOpen );

	if ( isOpen ) {
		panel
			.querySelector( PANEL_INNER_SELECTOR )
			?.focus( { preventScroll: true } );
	}
}

/**
 * @param {HTMLElement} section
 * @return {HTMLElement|null} Accordion body for the section, if present.
 */
function getAccordionBody( section ) {
	return section.querySelector( '.sk-filter-accordion__body' );
}

/**
 * @param {HTMLElement} section
 * @return {HTMLElement|null} Accordion trigger for the section, if present.
 */
function getAccordionTrigger( section ) {
	return section.querySelector( ACCORDION_TRIGGER_SELECTOR );
}

/**
 * @param {HTMLElement} section
 * @param {boolean}     isOpen
 */
export function setAccordionSectionOpen( section, isOpen ) {
	const trigger = getAccordionTrigger( section );
	const body = getAccordionBody( section );

	section.classList.toggle( ACCORDION_OPEN_CLASS, isOpen );

	if ( trigger ) {
		trigger.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	}

	if ( body ) {
		body.hidden = ! isOpen;
	}
}

/**
 * @param {HTMLElement|null} panel
 */
export function syncAccordionToggleState( panel ) {
	const toggle = panel?.querySelector( ACCORDION_TOGGLE_SELECTOR );

	if ( ! toggle || ! panel ) {
		return;
	}

	const sections = panel.querySelectorAll( FILTER_SECTION_SELECTOR );
	const allOpen =
		sections.length > 0 &&
		[ ...sections ].every( ( section ) =>
			section.classList.contains( ACCORDION_OPEN_CLASS ),
		);

	toggle.classList.toggle( ACCORDION_ALL_EXPANDED_CLASS, allOpen );
	toggle.setAttribute( 'aria-expanded', allOpen ? 'true' : 'false' );
	toggle.setAttribute(
		'aria-label',
		allOpen ? 'Collapse all filter sections' : 'Expand all filter sections',
	);
}

/**
 * @param {HTMLElement|null} panel
 */
export function collapseAllAccordions( panel ) {
	if ( ! panel ) {
		return;
	}

	panel.querySelectorAll( FILTER_SECTION_SELECTOR ).forEach( ( section ) => {
		setAccordionSectionOpen( section, false );
	} );

	syncAccordionToggleState( panel );
}

/**
 * @param {HTMLElement|null} panel
 */
export function expandAllAccordions( panel ) {
	if ( ! panel ) {
		return;
	}

	panel.querySelectorAll( FILTER_SECTION_SELECTOR ).forEach( ( section ) => {
		setAccordionSectionOpen( section, true );
	} );

	syncAccordionToggleState( panel );
}

/**
 * @param {HTMLElement|null}          root
 * @param {string}                    cardSlug
 * @param {{ closeOthers?: boolean }} [options]
 */
export function openAccordionForCard( root, cardSlug, options = {} ) {
	const panel = getFilterPanel();
	const closeOthers = options.closeOthers !== false;

	if ( ! panel || ! cardSlug ) {
		return;
	}

	panel.querySelectorAll( FILTER_SECTION_SELECTOR ).forEach( ( section ) => {
		const isMatch = section.getAttribute( 'data-filter-card' ) === cardSlug;

		if ( isMatch ) {
			setAccordionSectionOpen( section, true );
			return;
		}

		if ( closeOthers ) {
			setAccordionSectionOpen( section, false );
		}
	} );

	syncAccordionToggleState( panel );

	if ( root ) {
		root.querySelectorAll( BAR_BUTTON_SELECTOR ).forEach( ( button ) => {
			button.classList.toggle(
				'is-active',
				button.getAttribute( 'data-filter-card' ) === cardSlug,
			);
		} );
	}
}

/**
 * @param {HTMLElement} root
 * @param {string}      cardSlug
 */
export function openPanelForCard( root, cardSlug ) {
	root.querySelectorAll( BAR_BUTTON_SELECTOR ).forEach( ( button ) => {
		button.classList.toggle(
			'is-active',
			button.getAttribute( 'data-filter-card' ) === cardSlug,
		);
		button.setAttribute(
			'aria-expanded',
			button.getAttribute( 'data-filter-card' ) === cardSlug
				? 'true'
				: 'false',
		);
	} );

	syncFilterPanelPortal( root );
	setPanelOpen( root, true );
	openAccordionForCard( root, cardSlug );
}
