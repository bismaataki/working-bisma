/**
 * Blog listing filter controls — navigate via full-page GET URLs.
 *
 * Pills use buttons (not anchors) so crawlers do not discover combinatorial
 * filter URLs from href attributes in the page source.
 */
document.addEventListener( 'DOMContentLoaded', () => {
	const navigateToFilterUrl = ( url ) => {
		if ( url ) {
			window.location.href = url;
		}
	};

	document
		.querySelectorAll( 'select[data-hw-post-filters-url]' )
		.forEach( ( select ) => {
			select.addEventListener( 'change', () => {
				navigateToFilterUrl( select.value );
			} );
		} );

	document
		.querySelectorAll( 'button[data-hw-post-filters-url]' )
		.forEach( ( button ) => {
			button.addEventListener( 'click', () => {
				navigateToFilterUrl(
					button.getAttribute( 'data-hw-post-filters-url' ),
				);
			} );
		} );

	document.querySelectorAll( '.hw-post-filters__search' ).forEach( ( form ) => {
		const input = form.querySelector( '.hw-post-filters__search-input' );
		const clearUrl = form.getAttribute( 'data-hw-post-filters-clear-url' );

		if ( ! ( input instanceof HTMLInputElement ) || ! clearUrl ) {
			return;
		}

		const navigateToClearUrl = () => {
			if ( ! input.value.trim() ) {
				navigateToFilterUrl( clearUrl );
			}
		};

		form.addEventListener( 'submit', ( event ) => {
			if ( ! input.value.trim() ) {
				event.preventDefault();
				navigateToClearUrl();
			}
		} );

		// Native clear (×) on type="search" inputs.
		input.addEventListener( 'search', navigateToClearUrl );
	} );
} );
