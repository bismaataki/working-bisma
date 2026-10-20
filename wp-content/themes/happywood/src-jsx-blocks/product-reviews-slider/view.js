/**
 * Product reviews slider — Swiper init for verified review cards.
 */

import Swiper from 'swiper';
import { Navigation, Scrollbar } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/scrollbar';

import {
	CONTENT_UPDATED_EVENT,
	isQueryRoot,
} from '../../src/js/shared/delegated-events';

const ROOT_SELECTOR =
	'.hw-product-reviews-slider[data-hw-product-reviews-slider]';
const SWIPER_SELECTOR = '.hw-product-reviews-slider__swiper';
const NAV_WRAP_SELECTOR = '.hw-product-reviews-slider__nav';
const PREV_SELECTOR = '.hw-product-reviews-slider__arrow--prev';
const NEXT_SELECTOR = '.hw-product-reviews-slider__arrow--next';
const SCROLLBAR_SELECTOR =
	'.hw-product-reviews-slider__scrollbar:not(.hw-product-reviews-slider__scrollbar--preview)';
const NAV_MEDIA = window.matchMedia( '(min-width: 781px)' );

/** @type {WeakMap<HTMLElement, Swiper>} */
const swiperInstances = new WeakMap();

/**
 * @param {HTMLElement} root
 * @param {string}      name
 * @param {boolean}     fallback
 * @return {boolean} Parsed boolean, or the fallback when the attribute is absent.
 */
function readBoolData( root, name, fallback ) {
	const value = root.dataset[ name ];

	if ( undefined === value ) {
		return fallback;
	}

	return 'true' === value;
}

/**
 * @param {HTMLElement} root
 */
function syncArrowVisibilityClasses( root ) {
	const nav = root.querySelector( NAV_WRAP_SELECTOR );

	if ( ! nav ) {
		return;
	}

	const showDesktop = readBoolData( root, 'showArrowsDesktop', true );
	const showMobile = readBoolData( root, 'showArrowsMobile', false );
	const isDesktop = NAV_MEDIA.matches;

	nav.hidden = ! ( ( isDesktop && showDesktop ) || ( ! isDesktop && showMobile ) );
}

/**
 * @param {HTMLElement} root
 */
function destroyProductReviewsSlider( root ) {
	const instance = swiperInstances.get( root );

	if ( instance ) {
		instance.destroy( true, true );
		swiperInstances.delete( root );
	}

	delete root.dataset.hwProductReviewsSliderInit;
}

/**
 * @param {HTMLElement} root
 */
function initProductReviewsSlider( root ) {
	if ( root.dataset.hwProductReviewsSliderInit === 'true' ) {
		return;
	}

	const swiperEl = root.querySelector( SWIPER_SELECTOR );

	if ( ! swiperEl ) {
		return;
	}

	syncArrowVisibilityClasses( root );

	const showProgressBar = readBoolData( root, 'showProgressBar', true );
	const showArrowsDesktop = readBoolData( root, 'showArrowsDesktop', true );
	const showArrowsMobile = readBoolData( root, 'showArrowsMobile', false );
	const nav = root.querySelector( NAV_WRAP_SELECTOR );
	const prevEl = nav?.querySelector( PREV_SELECTOR ) ?? null;
	const nextEl = nav?.querySelector( NEXT_SELECTOR ) ?? null;
	const scrollbarEl = root.querySelector( SCROLLBAR_SELECTOR );

	/** @type {import('swiper').SwiperOptions} */
	const options = {
		modules: [ Navigation, Scrollbar ],
		slidesPerView: 'auto',
		spaceBetween: 16,
		watchOverflow: true,
	};

	if ( showProgressBar && scrollbarEl ) {
		options.scrollbar = {
			el: scrollbarEl,
			dragSize: 80,
			hide: false,
			draggable: true,
		};
	}

	if ( nav && ( showArrowsDesktop || showArrowsMobile ) ) {
		options.navigation = {
			prevEl,
			nextEl,
			disabledClass: 'hw-product-reviews-slider__arrow--disabled',
			addIcons: false,
		};
	}

	const swiper = new Swiper( swiperEl, options );

	swiperInstances.set( root, swiper );
	root.dataset.hwProductReviewsSliderInit = 'true';
}

/**
 * @param {Element|Document|DocumentFragment|null|undefined} scope
 */
export function initProductReviewsSliders( scope ) {
	const context = scope ?? document;

	context.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		if ( root instanceof HTMLElement ) {
			initProductReviewsSlider( root );
		}
	} );
}

/**
 * @param {Element|Document|DocumentFragment|null|undefined} scope
 */
export function refreshProductReviewsSliders( scope ) {
	const context = scope ?? document;

	context.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		if ( root instanceof HTMLElement ) {
			destroyProductReviewsSlider( root );
			initProductReviewsSlider( root );
		}
	} );
}

/**
 * Bind resize + content-updated listeners.
 */
export function bindProductReviewsSliderListeners() {
	let resizeTimer = 0;

	window.addEventListener( 'resize', () => {
		window.clearTimeout( resizeTimer );
		resizeTimer = window.setTimeout( () => {
			document.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
				if ( root instanceof HTMLElement ) {
					syncArrowVisibilityClasses( root );
				}
			} );
		}, 150 );
	} );

	document.addEventListener( CONTENT_UPDATED_EVENT, ( event ) => {
		const detail = event.detail ?? {};
		const scope = isQueryRoot( detail?.scope ) ? detail.scope : document;

		refreshProductReviewsSliders( scope );
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	initProductReviewsSliders();
	bindProductReviewsSliderListeners();
} );
