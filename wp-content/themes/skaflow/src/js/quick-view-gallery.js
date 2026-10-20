/**
 * Quick View product gallery — Swiper slider instead of WC thumbnail grid.
 *
 * Transforms injected `.woocommerce-product-gallery` markup into a single-image
 * swiper (Figma quick-view reference). Re-inits when swatch-driven gallery
 * swap replaces the gallery root inside `.sk-quick-view__content`.
 */

import jQuery from 'jquery';

import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

/** @type {WeakMap<Element, Swiper>} */
const swiperInstances = new WeakMap();

/**
 * @param {HTMLImageElement} img
 * @return {{ src: string, alt: string, srcset: string, sizes: string }} Slide fields read from the image.
 */
function getSlideDataFromImage( img ) {
	return {
		src:
			img.dataset.largeImage ||
			img.getAttribute( 'data-large_image' ) ||
			img.dataset.src ||
			img.getAttribute( 'data-src' ) ||
			img.currentSrc ||
			img.src,
		alt: img.alt || '',
		srcset: img.srcset || '',
		sizes: img.sizes || '',
	};
}

/**
 * @param {Element} gallery
 * @return {Array<{ src: string, alt: string, srcset: string, sizes: string }>} Slides collected from the gallery markup.
 */
function collectGallerySlides( gallery ) {
	const slides = [];

	gallery
		.querySelectorAll( '.woocommerce-product-gallery__image' )
		.forEach( ( imageWrap ) => {
			const img = imageWrap.querySelector( 'img' );

			if ( ! img ) {
				return;
			}

			slides.push( getSlideDataFromImage( img ) );
		} );

	return slides;
}

/**
 * @param {Element} gallery
 */
function destroyQuickViewGallery( gallery ) {
	const instance = swiperInstances.get( gallery );

	if ( instance ) {
		instance.destroy( true, true );
		swiperInstances.delete( gallery );
	}

	gallery.classList.remove(
		'sk-quick-view-gallery',
		'sk-quick-view-gallery--ready',
	);
	delete gallery.dataset.skQuickViewGallerySlides;
	delete gallery.dataset.skQuickViewGallerySlidesKey;
}

/**
 * @param {Element}                                                            gallery
 * @param {Array<{ src: string, alt: string, srcset: string, sizes: string }>} slides
 */
function renderQuickViewGallery( gallery, slides ) {
	const slideMarkup = slides
		.map( ( slide ) => {
			const srcsetAttr = slide.srcset ? ` srcset="${ slide.srcset }"` : '';
			const sizesAttr = slide.sizes ? ` sizes="${ slide.sizes }"` : '';

			return `
				<div class="swiper-slide sk-quick-view-gallery__slide">
					<img src="${ slide.src }" alt="${ slide.alt }"${ srcsetAttr }${ sizesAttr } loading="lazy" decoding="async" />
				</div>
			`.trim();
		} )
		.join( '' );

	const navMarkup =
		slides.length > 1
			? `
				<button type="button" class="sk-quick-view-gallery__nav sk-quick-view-gallery__nav--prev" aria-label="Previous image"></button>
				<button type="button" class="sk-quick-view-gallery__nav sk-quick-view-gallery__nav--next" aria-label="Next image"></button>
			`.trim()
			: '';

	const paginationMarkup =
		slides.length > 1
			? '<div class="swiper-pagination sk-quick-view-gallery__pagination"></div>'
			: '';

	gallery.innerHTML = `
		<div class="swiper sk-quick-view-gallery__swiper">
			<div class="swiper-wrapper">${ slideMarkup }</div>
			${ navMarkup }
			${ paginationMarkup }
		</div>
	`.trim();

	gallery.classList.add(
		'sk-quick-view-gallery',
		'sk-quick-view-gallery--ready',
	);
	gallery.style.opacity = '1';

	const swiperEl = gallery.querySelector( '.sk-quick-view-gallery__swiper' );

	if ( ! swiperEl ) {
		return;
	}

	const swiper = new Swiper( swiperEl, {
		modules: [ Navigation, Pagination ],
		slidesPerView: 1,
		spaceBetween: 0,
		loop: slides.length > 1,
		navigation: {
			prevEl: gallery.querySelector( '.sk-quick-view-gallery__nav--prev' ),
			nextEl: gallery.querySelector( '.sk-quick-view-gallery__nav--next' ),
			addIcons: false,
		},
		pagination: {
			el: gallery.querySelector( '.sk-quick-view-gallery__pagination' ),
			clickable: true,
		},
	} );

	swiperInstances.set( gallery, swiper );
	gallery.dataset.skQuickViewGallerySlides = String( slides.length );
}

/**
 * True when this gallery node still hosts a live Swiper, not a WC thumbnail grid.
 *
 * @param {Element} gallery
 * @return {boolean} Whether the gallery currently hosts a Swiper.
 */
function isQuickViewSwiperMounted( gallery ) {
	return (
		gallery.classList.contains( 'sk-quick-view-gallery--ready' ) &&
		Boolean( gallery.querySelector( '.sk-quick-view-gallery__swiper' ) )
	);
}

/**
 * @param {Element} gallery
 */
function initQuickViewGalleryElement( gallery ) {
	const slides = collectGallerySlides( gallery );

	if ( ! slides.length ) {
		return;
	}

	const slidesKey = slides.map( ( slide ) => slide.src ).join( '|' );

	if (
		gallery.dataset.skQuickViewGallerySlidesKey === slidesKey &&
		isQuickViewSwiperMounted( gallery )
	) {
		return;
	}

	destroyQuickViewGallery( gallery );
	renderQuickViewGallery( gallery, slides );
	gallery.dataset.skQuickViewGallerySlidesKey = slidesKey;
}

/**
 * @param {Element|Document|DocumentFragment|null|undefined} root
 */
export function initQuickViewGalleries( root ) {
	if ( ! root ) {
		return;
	}

	root.querySelectorAll( '.woocommerce-product-gallery' ).forEach( ( gallery ) => {
		initQuickViewGalleryElement( gallery );
	} );
}

/**
 * Bind re-init when swatch gallery swap finishes inside the popup.
 */
export function bindQuickViewGalleryListeners() {
	document.addEventListener( 'cf:quick-view-gallery-changed', ( event ) => {
		const target = event.target;

		if ( ! ( target instanceof Element ) ) {
			return;
		}

		const content = target.closest( '.sk-quick-view__content' );

		if ( content ) {
			initQuickViewGalleries( content );
		}
	} );

	// Clear restores Woo's thumbnail grid. Re-init after that markup lands.
	jQuery( document ).on(
		'reset_data',
		'.sk-quick-view__content .variations_form',
		( event ) => {
			window.setTimeout( () => {
				const form = event.currentTarget;

				if ( ! form ) {
					return;
				}

				const content = form.closest( '.sk-quick-view__content' );

				if ( content ) {
					initQuickViewGalleries( content );
				}
			}, 0 );
		},
	);

	// Variations with a single image swap WC's main image without replacing the
	// whole gallery root — refresh the swiper slide from variation.image.
	jQuery( document ).on(
		'found_variation',
		'.sk-quick-view__content .variations_form',
		( event, variation ) => {
			if (
				variation?.gallery_images_html ||
				variation?.sk_variation_gallery_html
			) {
				return;
			}

			window.setTimeout( () => {
				const form = event.currentTarget;

				if ( ! form || ! variation?.image?.src ) {
					return;
				}

				const gallery = getGalleryForForm( form );

				if ( ! gallery ) {
					return;
				}

				destroyQuickViewGallery( gallery );
				renderQuickViewGallery( gallery, [
					{
						src: String( variation.image.src ),
						alt: String( variation.image.alt || '' ),
						srcset: String( variation.image.srcset || '' ),
						sizes: '',
					},
				] );
				gallery.dataset.skQuickViewGallerySlidesKey = String(
					variation.image.src,
				);
			}, 0 );
		},
	);
}

/**
 * @param {Element} form
 * @return {Element|null} Gallery element tied to the form, if present.
 */
function getGalleryForForm( form ) {
	const productRoot = form.closest( '.product' );

	if ( productRoot ) {
		const scoped = productRoot.querySelector(
			'.woocommerce-product-gallery, .sk-quick-view-gallery',
		);

		if ( scoped ) {
			return scoped;
		}
	}

	return null;
}
