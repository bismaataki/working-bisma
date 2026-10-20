/**
 * Site header interactions — mega menu toggles and mobile drawer.
 *
 * The template-part wrapper is a bare <header> (no .site-header class).
 * Bind to BEM elements (.site-header__*) that the PHP partial emits.
 */

const MENU_OPEN_CLASS = 'menu-open';
const MEGA_MENU_OPEN_CLASS = 'mega-menu-open';
const OPEN_CLASS = 'is-open';
const HIDE_ON_SCROLL_CLASS = 'site-header--hide-on-scroll';
const HEADER_HIDDEN_CLASS = 'site-header--is-hidden';
const SCROLL_THRESHOLD = 6;
const DRILL_ACTIVE_CLASS = 'is-drill-active';
const DRILL_SOURCE_CLASS = 'is-drill-source';

function getBackdrop() {
	return document.querySelector( '.site-header__backdrop' );
}

function closeMegaPanels( exceptItem = null ) {
	document
		.querySelectorAll( '.site-header__nav-item--has-mega' )
		.forEach( ( item ) => {
			if ( exceptItem && item === exceptItem ) {
				return;
			}

			item.classList.remove( OPEN_CLASS );
			const trigger = item.querySelector(
				'.site-header__nav-link[type="button"]',
			);
			const panel = item.querySelector( '.site-header__mega-menu' );

			if ( trigger ) {
				trigger.setAttribute( 'aria-expanded', 'false' );
			}

			if ( panel ) {
				panel.hidden = true;
			}
		} );

	document.documentElement.classList.remove( MEGA_MENU_OPEN_CLASS );
}

function openMegaPanel( item ) {
	const trigger = item.querySelector( '.site-header__nav-link[type="button"]' );
	const panel = item.querySelector( '.site-header__mega-menu' );

	if ( ! trigger || ! panel ) {
		return;
	}

	closeMegaPanels( item );
	item.classList.add( OPEN_CLASS );
	trigger.setAttribute( 'aria-expanded', 'true' );
	panel.hidden = false;
	document.documentElement.classList.add( MEGA_MENU_OPEN_CLASS );
}

function toggleMegaPanel( item ) {
	const isOpen = item.classList.contains( OPEN_CLASS );

	if ( isOpen ) {
		closeMegaPanels();
		return;
	}

	openMegaPanel( item );
}

function initDesktopMegaMenus() {
	document
		.querySelectorAll( '.site-header__nav-item--has-mega' )
		.forEach( ( item ) => {
			const trigger = item.querySelector(
				'.site-header__nav-link[type="button"]',
			);

			if ( ! trigger ) {
				return;
			}

			trigger.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				toggleMegaPanel( item );
			} );

			trigger.addEventListener( 'keydown', ( event ) => {
				if ( event.key === 'Enter' || event.key === ' ' ) {
					event.preventDefault();
					toggleMegaPanel( item );
				}
			} );
		} );

	document.addEventListener( 'keydown', ( event ) => {
		if ( event.key === 'Escape' ) {
			closeMegaPanels();
		}
	} );

	document.addEventListener( 'click', ( event ) => {
		const backdrop = getBackdrop();
		if ( backdrop && event.target === backdrop ) {
			closeMegaPanels();
			return;
		}

		if ( ! event.target.closest( '.site-header__nav-primary' ) ) {
			closeMegaPanels();
		}
	} );
}

function updateMobileDrawerOffset() {
	const primary = document.querySelector( '.site-header__primary' );

	if ( ! primary ) {
		return;
	}

	document.documentElement.style.setProperty(
		'--site-header-primary-bottom',
		`${ primary.getBoundingClientRect().bottom }px`,
	);
}

function initMobileDrawer() {
	const drawer = document.getElementById( 'happywood-mobile-drawer' );
	const toggle = document.querySelector( '.site-header__menu-toggle' );

	if ( ! drawer || ! toggle ) {
		return;
	}

	const overlay = drawer.querySelector( '.site-header__mobile-drawer-overlay' );
	const rootBar = drawer.querySelector(
		'.site-header__mobile-drawer-bar--root',
	);
	const drillBar = drawer.querySelector(
		'.site-header__mobile-drawer-bar--drill',
	);
	const drillTitle = drawer.querySelector(
		'.site-header__mobile-drawer-drill-title',
	);
	const drillBack = drawer.querySelector( '.site-header__mobile-drawer-back' );
	const closeButtons = drawer.querySelectorAll(
		'.site-header__mobile-drawer-close',
	);
	const backdrop = getBackdrop();
	let activeDrillItem = null;

	const resetDrawerScroll = () => {
		const body = drawer.querySelector( '.site-header__mobile-drawer-body' );
		if ( body ) {
			body.scrollLeft = 0;
		}
	};

	const resetDrill = () => {
		drawer.classList.remove( DRILL_ACTIVE_CLASS );
		activeDrillItem?.classList.remove( DRILL_SOURCE_CLASS );
		activeDrillItem = null;
		resetDrawerScroll();

		if ( drillTitle ) {
			drillTitle.textContent = '';
			drillTitle.hidden = true;
		}

		if ( drillBar ) {
			drillBar.hidden = true;
		}

		if ( rootBar ) {
			rootBar.hidden = false;
		}
	};

	const closeDrawer = () => {
		resetDrill();
		drawer.setAttribute( 'aria-hidden', 'true' );
		drawer.inert = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.documentElement.classList.remove( MENU_OPEN_CLASS );
		document.body.style.overflow = '';
		toggle.focus();
	};

	const openDrawer = () => {
		updateMobileDrawerOffset();
		resetDrill();
		resetDrawerScroll();
		drawer.setAttribute( 'aria-hidden', 'false' );
		drawer.inert = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		document.documentElement.classList.add( MENU_OPEN_CLASS );
		document.body.style.overflow = 'hidden';
		const closeBtn = drawer.querySelector(
			'.site-header__mobile-drawer-bar--root .site-header__mobile-drawer-close',
		);
		if ( closeBtn ) {
			closeBtn.focus();
		}
	};

	const openDrill = ( navItem, trigger ) => {
		const submenu = navItem.querySelector(
			':scope > .site-header__mega-menu-list',
		);

		if ( ! submenu ) {
			return;
		}

		resetDrill();
		activeDrillItem = navItem;
		navItem.classList.add( DRILL_SOURCE_CLASS );
		drawer.classList.add( DRILL_ACTIVE_CLASS );

		if ( drillTitle ) {
			drillTitle.textContent =
				trigger.getAttribute( 'data-drill-title' ) ||
				trigger.textContent.trim();
			drillTitle.hidden = false;
		}

		if ( rootBar ) {
			rootBar.hidden = true;
		}

		if ( drillBar ) {
			drillBar.hidden = false;
		}

		if ( drillBack ) {
			drillBack.focus();
		}

		resetDrawerScroll();
	};

	toggle.addEventListener( 'click', () => {
		const isOpen = drawer.getAttribute( 'aria-hidden' ) === 'false';
		if ( isOpen ) {
			closeDrawer();
		} else {
			openDrawer();
		}
	} );

	closeButtons.forEach( ( button ) => {
		button.addEventListener( 'click', closeDrawer );
	} );

	if ( overlay ) {
		overlay.addEventListener( 'click', closeDrawer );
	}

	if ( drillBack ) {
		drillBack.addEventListener( 'click', resetDrill );
	}

	drawer
		.querySelectorAll( '.site-header__mobile-drill-trigger' )
		.forEach( ( trigger ) => {
			trigger.addEventListener( 'click', ( event ) => {
				event.preventDefault();
				const navItem = trigger.closest( '.site-header__nav-item' );
				if ( navItem ) {
					openDrill( navItem, trigger );
				}
			} );
		} );

	if ( backdrop ) {
		backdrop.addEventListener( 'click', () => {
			if ( drawer.getAttribute( 'aria-hidden' ) === 'false' ) {
				closeDrawer();
			}
		} );
	}

	document.addEventListener( 'keydown', ( event ) => {
		if (
			event.key === 'Escape' &&
			drawer.getAttribute( 'aria-hidden' ) === 'false'
		) {
			if ( drawer.classList.contains( DRILL_ACTIVE_CLASS ) ) {
				resetDrill();
				return;
			}
			closeDrawer();
		}
	} );

	window.addEventListener( 'resize', updateMobileDrawerOffset );
	window.addEventListener( 'scroll', updateMobileDrawerOffset, {
		passive: true,
	} );
}

function initMobileStickyPrimary() {
	const primary = document.querySelector( '.site-header__primary' );

	if ( ! primary ) {
		return;
	}

	const mobileQuery = window.matchMedia( '(max-width: 767px)' );

	const announcement = document.querySelector( '.site-header__announcement' );
	const stickyLogo = document.querySelector( '.site-header__logo-img--sticky' );

	const updateSpacerHeight = () => {
		const isStuck = primary.classList.contains(
			'site-header__primary--is-stuck',
		);

		document.documentElement.style.setProperty(
			'--site-header-primary-spacer-height',
			isStuck && mobileQuery.matches ? `${ primary.offsetHeight }px` : '0px',
		);

		updateMobileDrawerOffset();
	};

	const setStuckState = ( isStuck ) => {
		if ( ! mobileQuery.matches ) {
			primary.classList.remove( 'site-header__primary--is-stuck' );
			primary.classList.remove( 'site-header__primary--compact' );
			updateSpacerHeight();
			return;
		}

		primary.classList.toggle( 'site-header__primary--is-stuck', isStuck );
		primary.classList.toggle(
			'site-header__primary--compact',
			isStuck && !! stickyLogo,
		);
		updateSpacerHeight();
	};

	const syncFromAnnouncement = ( isAnnouncementVisible ) => {
		setStuckState( ! isAnnouncementVisible );
	};

	if ( ! announcement ) {
		syncFromAnnouncement( false );
	} else if ( typeof IntersectionObserver !== 'undefined' ) {
		const observer = new IntersectionObserver(
			( [ entry ] ) => {
				syncFromAnnouncement( entry.isIntersecting );
			},
			{ threshold: 0 },
		);

		observer.observe( announcement );
	}

	mobileQuery.addEventListener( 'change', () => {
		if ( ! mobileQuery.matches ) {
			setStuckState( false );
			return;
		}

		if ( ! announcement ) {
			syncFromAnnouncement( false );
		}
	} );

	window.addEventListener( 'resize', updateSpacerHeight, { passive: true } );
}

function initHideOnScroll() {
	if ( ! document.body.classList.contains( HIDE_ON_SCROLL_CLASS ) ) {
		return;
	}

	const header = document.querySelector( '.wp-site-blocks > header' );

	if ( ! header ) {
		return;
	}

	let lastY = Math.max( window.scrollY, 0 );
	let ticking = false;

	// An open drawer or mega panel scrolls the page behind it, and a header
	// holding keyboard focus would strand the focus ring offscreen.
	const isPinned = () =>
		document.documentElement.classList.contains( MENU_OPEN_CLASS ) ||
		document.documentElement.classList.contains( MEGA_MENU_OPEN_CLASS ) ||
		header.contains( header.ownerDocument.activeElement );

	const setHidden = ( isHidden ) => {
		header.classList.toggle( HEADER_HIDDEN_CLASS, isHidden );
	};

	const update = () => {
		ticking = false;

		const currentY = Math.max( window.scrollY, 0 );
		const delta = currentY - lastY;

		// Ignore rubber-band overscroll and sub-pixel jitter.
		if ( Math.abs( delta ) < SCROLL_THRESHOLD ) {
			return;
		}

		lastY = currentY;

		if ( isPinned() || currentY <= header.offsetHeight ) {
			setHidden( false );
			return;
		}

		setHidden( delta > 0 );
	};

	window.addEventListener(
		'scroll',
		() => {
			if ( ticking ) {
				return;
			}

			ticking = true;
			window.requestAnimationFrame( update );
		},
		{ passive: true },
	);

	// Tabbing into a hidden header must bring it back.
	document.addEventListener( 'focusin', ( event ) => {
		if ( header.contains( event.target ) ) {
			setHidden( false );
		}
	} );
}

export function initSiteHeader() {
	initDesktopMegaMenus();
	initMobileDrawer();
	initMobileStickyPrimary();
	initHideOnScroll();
}
