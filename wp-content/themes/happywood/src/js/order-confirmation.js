/**
 * Order confirmation print and share.
 *
 * Delegated so the buttons still work if the aside is re-rendered.
 */

import { delegateDocument } from './shared/delegated-events';

export function initOrderConfirmation() {
	delegateDocument( 'click', '[data-hw-print-order]', ( event ) => {
		event.preventDefault();
		window.print();
	} );

	delegateDocument(
		'click',
		'[data-hw-share-order]',
		async ( event, button ) => {
			event.preventDefault();

			const url =
				button.getAttribute( 'data-hw-share-order' ) ||
				window.location.href;
			const title = document.title;

			if ( navigator.share ) {
				try {
					await navigator.share( { title, url } );
				} catch ( error ) {
					if ( error?.name !== 'AbortError' ) {
						copyOrderUrl( url );
					}
				}
				return;
			}

			copyOrderUrl( url );
		},
	);
}

function copyOrderUrl( url ) {
	if ( ! navigator.clipboard?.writeText ) {
		return;
	}

	navigator.clipboard.writeText( url );
}
