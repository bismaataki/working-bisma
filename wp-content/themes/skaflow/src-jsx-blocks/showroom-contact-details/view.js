import {
	CONTENT_UPDATED_EVENT,
	delegateOn,
} from '../../src/js/shared/delegated-events';

const ROOT_SELECTOR = '.wp-block-skaflow-showroom-contact-details';
const TRIGGER_SELECTOR = '.sk-showroom-contact-details__trigger';
const LIST_SELECTOR = '.sk-showroom-contact-details__list';
const OPTION_SELECTOR = '.sk-showroom-contact-details__option';
const PANEL_SELECTOR = '.sk-showroom-contact-details__panel';
const INIT_FLAG = 'cfShowroomContactDetailsInitialized';
const OPEN_CLASS = 'is-open';

/**
 * @param {HTMLElement} root Block root.
 */
function closeList( root ) {
	const trigger = root.querySelector( TRIGGER_SELECTOR );
	const list = root.querySelector( LIST_SELECTOR );

	if ( ! trigger || ! list ) {
		return;
	}

	root.classList.remove( OPEN_CLASS );
	trigger.setAttribute( 'aria-expanded', 'false' );
	list.hidden = true;
}

/**
 * @param {HTMLElement} root Block root.
 */
function openList( root ) {
	const trigger = root.querySelector( TRIGGER_SELECTOR );
	const list = root.querySelector( LIST_SELECTOR );

	if ( ! trigger || ! list ) {
		return;
	}

	root.classList.add( OPEN_CLASS );
	trigger.setAttribute( 'aria-expanded', 'true' );
	list.hidden = false;
}

/**
 * @param {HTMLElement} root         Block root.
 * @param {string}      showroomSlug Selected showroom slug.
 * @param {string}      label        Selected showroom label.
 */
function selectShowroom( root, showroomSlug, label ) {
	root.dataset.activeShowroom = showroomSlug;

	const triggerLabel = root.querySelector(
		'.sk-showroom-contact-details__trigger-label',
	);

	if ( triggerLabel && label ) {
		triggerLabel.textContent = label;
	}

	root.querySelectorAll( OPTION_SELECTOR ).forEach( ( option ) => {
		const isActive = option.dataset.showroomSlug === showroomSlug;

		option.classList.toggle( 'is-active', isActive );
		option.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		option.setAttribute( 'tabindex', isActive ? '0' : '-1' );
	} );

	root.querySelectorAll( PANEL_SELECTOR ).forEach( ( panel ) => {
		const isActive = panel.dataset.showroomSlug === showroomSlug;

		panel.classList.toggle( 'is-active', isActive );

		if ( isActive ) {
			panel.removeAttribute( 'hidden' );
		} else {
			panel.setAttribute( 'hidden', '' );
		}
	} );

	closeList( root );
}

/**
 * @param {HTMLElement} root Block root.
 */
function initShowroomContactDetails( root ) {
	if ( root[ INIT_FLAG ] ) {
		return;
	}

	root[ INIT_FLAG ] = true;
}

/**
 * @param {Document|HTMLElement} scope
 */
function initAllShowroomContactDetails( scope = document ) {
	scope.querySelectorAll( ROOT_SELECTOR ).forEach( initShowroomContactDetails );
}

document.addEventListener( 'DOMContentLoaded', () => {
	initAllShowroomContactDetails();
} );

document.addEventListener( CONTENT_UPDATED_EVENT, () => {
	initAllShowroomContactDetails();
} );

delegateOn( document, 'click', TRIGGER_SELECTOR, ( event, trigger ) => {
	const root = trigger.closest( ROOT_SELECTOR );

	if ( ! root ) {
		return;
	}

	event.preventDefault();

	if ( root.classList.contains( OPEN_CLASS ) ) {
		closeList( root );
		return;
	}

	openList( root );
} );

delegateOn( document, 'click', OPTION_SELECTOR, ( event, option ) => {
	const root = option.closest( ROOT_SELECTOR );

	if ( ! root ) {
		return;
	}

	event.preventDefault();

	const showroomSlug = option.dataset.showroomSlug || '';

	if ( ! showroomSlug ) {
		return;
	}

	selectShowroom( root, showroomSlug, option.textContent?.trim() || '' );
} );

document.addEventListener( 'click', ( event ) => {
	if ( event.target?.closest?.( ROOT_SELECTOR ) ) {
		return;
	}

	document.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		if ( root.classList.contains( OPEN_CLASS ) ) {
			closeList( root );
		}
	} );
} );

document.addEventListener( 'keydown', ( event ) => {
	if ( event.key !== 'Escape' ) {
		return;
	}

	document.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		if ( root.classList.contains( OPEN_CLASS ) ) {
			closeList( root );
			root.querySelector( TRIGGER_SELECTOR )?.focus();
		}
	} );
} );

delegateOn( document, 'keydown', OPTION_SELECTOR, ( event, option ) => {
	const root = option.closest( ROOT_SELECTOR );

	if ( ! root ) {
		return;
	}

	const options = Array.from( root.querySelectorAll( OPTION_SELECTOR ) );
	const currentIndex = options.indexOf( option );

	if ( currentIndex < 0 ) {
		return;
	}

	if ( event.key === 'Enter' || event.key === ' ' ) {
		event.preventDefault();
		selectShowroom(
			root,
			option.dataset.showroomSlug || '',
			option.textContent?.trim() || '',
		);
		return;
	}

	if ( event.key === 'ArrowDown' ) {
		event.preventDefault();
		options[ ( currentIndex + 1 ) % options.length ]?.focus();
		return;
	}

	if ( event.key === 'ArrowUp' ) {
		event.preventDefault();
		options[ ( currentIndex - 1 + options.length ) % options.length ]?.focus();
	}
} );
