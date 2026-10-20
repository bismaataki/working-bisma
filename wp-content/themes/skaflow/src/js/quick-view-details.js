/**
 * Quick View short description — clamp to ~3 lines with fade, expand on click.
 *
 * Dimensions mode is plain text with no collapse behaviour.
 */

const DESCRIPTION_CONTENT_SELECTOR =
	'.sk-quick-view-details--description .sk-quick-view-details__content';

/**
 * @param {HTMLElement} content
 */
function initQuickViewDescriptionElement( content ) {
	if ( content.dataset.skQuickViewDetailsInit === '1' ) {
		return;
	}

	content.dataset.skQuickViewDetailsInit = '1';

	const originalHtml = content.innerHTML;
	const inner = document.createElement( 'div' );
	inner.className = 'sk-quick-view-details__inner';
	inner.innerHTML = originalHtml;
	content.replaceChildren( inner );

	window.requestAnimationFrame( () => {
		content.classList.add( 'sk-quick-view-details__content--measuring' );

		const isOverflowing = inner.scrollHeight > inner.clientHeight + 2;

		content.classList.remove( 'sk-quick-view-details__content--measuring' );

		if ( ! isOverflowing ) {
			return;
		}

		content.classList.add( 'sk-quick-view-details__content--collapsible' );
		content.setAttribute( 'role', 'button' );
		content.setAttribute( 'tabindex', '0' );
		content.setAttribute( 'aria-expanded', 'false' );
	} );

	const expand = () => {
		if (
			! content.classList.contains(
				'sk-quick-view-details__content--collapsible',
			)
		) {
			return;
		}

		if ( content.classList.contains( 'is-expanded' ) ) {
			return;
		}

		content.classList.add( 'is-expanded' );
		content.setAttribute( 'aria-expanded', 'true' );
		content.removeAttribute( 'tabindex' );
		content.removeAttribute( 'role' );
	};

	content.addEventListener( 'click', ( event ) => {
		if ( event.target instanceof HTMLAnchorElement ) {
			return;
		}

		expand();
	} );

	content.addEventListener( 'keydown', ( event ) => {
		if ( event.key !== 'Enter' && event.key !== ' ' ) {
			return;
		}

		event.preventDefault();
		expand();
	} );
}

/**
 * @param {Element|Document|DocumentFragment|null|undefined} root
 */
export function initQuickViewDetails( root ) {
	if ( ! root ) {
		return;
	}

	root.querySelectorAll( DESCRIPTION_CONTENT_SELECTOR ).forEach( ( content ) => {
		if ( content instanceof HTMLElement ) {
			initQuickViewDescriptionElement( content );
		}
	} );
}
