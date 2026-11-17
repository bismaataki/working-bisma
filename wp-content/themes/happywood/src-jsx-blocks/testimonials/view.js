/**
 * Testimonials frontend slider.
 *
 * @package Happywood
 */

const ROOT_SELECTOR = '[data-testimonials-slider]';

function initTestimonialsSlider( root ) {
	if ( root.dataset.testimonialsInitialized ) {
		return;
	}

	const slides = Array.from(
		root.querySelectorAll( '[data-testimonial-slide]' )
	);

	if ( slides.length <= 1 ) {
		return;
	}

	let activeIndex = 0;

	const showSlide = ( index ) => {
		activeIndex = ( index + slides.length ) % slides.length;

		slides.forEach( ( slide, slideIndex ) => {
			const isActive = slideIndex === activeIndex;

			slide.hidden = ! isActive;
			slide.classList.toggle( 'is-active', isActive );

			slide.querySelectorAll( '[data-testimonial-to]' ).forEach(
				( dot ) => {
					const isCurrent =
						Number( dot.dataset.testimonialTo ) === activeIndex;

					dot.classList.toggle( 'is-active', isCurrent );
					dot.setAttribute(
						'aria-current',
						isCurrent ? 'true' : 'false'
					);
				}
			);
		} );
	};

	root.addEventListener( 'click', ( event ) => {
		if ( ! ( event.target instanceof Element ) ) {
			return;
		}

		const nextButton = event.target.closest(
			'[data-testimonial-next]'
		);

		const previousButton = event.target.closest(
			'[data-testimonial-prev]'
		);

		const dotButton = event.target.closest(
			'[data-testimonial-to]'
		);

		if ( nextButton ) {
			showSlide( activeIndex + 1 );
		} else if ( previousButton ) {
			showSlide( activeIndex - 1 );
		} else if ( dotButton ) {
			showSlide(
				Number( dotButton.dataset.testimonialTo )
			);
		}
	} );

	root.dataset.testimonialsInitialized = 'true';

	showSlide( 0 );
}

function initTestimonialsSliders() {
	document.querySelectorAll( ROOT_SELECTOR ).forEach( ( root ) => {
		initTestimonialsSlider( root );
	} );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener(
		'DOMContentLoaded',
		initTestimonialsSliders
	);
} else {
	initTestimonialsSliders();
}
