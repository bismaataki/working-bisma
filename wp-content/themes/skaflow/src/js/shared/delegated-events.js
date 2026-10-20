/**
 * Load-More-safe event delegation utilities.
 *
 * Every interactive handler for repeatable grid/card elements must bind once
 * on a stable ancestor (document by default) with a selector string — never
 * directly on elements that AJAX pagination may append later.
 */

/** @type {string} Fired after AJAX/Load More/filtering appends new grid nodes. */
export const CONTENT_UPDATED_EVENT = 'skaflow:content-updated';

/**
 * Whether a value can be used as a `querySelectorAll` root.
 *
 * A query root is an Element, Document, or DocumentFragment. Never write
 * `instanceof ParentNode`; that mixin is not a constructor and throws.
 *
 * @param {unknown} node
 * @return {node is (Element|Document|DocumentFragment)} Whether the node can receive querySelector.
 */
export function isQueryRoot( node ) {
	return (
		node instanceof Element ||
		node instanceof Document ||
		node instanceof DocumentFragment
	);
}

/**
 * Notify features that genuinely need to scan newly appended DOM (e.g. future
 * carousel re-init). Pure delegated click/hover handlers do not need this.
 *
 * @param {Record<string, unknown>} [detail] Optional payload for listeners.
 */
export function dispatchContentUpdated( detail = {} ) {
	document.dispatchEvent(
		new CustomEvent( CONTENT_UPDATED_EVENT, {
			bubbles: true,
			detail,
		} ),
	);
}

/**
 * Register a delegated handler on `document`.
 *
 * @param {string}                                                                               eventType Event name (e.g. `click`, `mouseenter`).
 * @param {string}                                                                               selector  CSS selector matched via `Element.closest()`.
 * @param {Function}                                                                             handler   `( event, matchedElement ) => void`.
 * @param {{capture?: boolean, once?: boolean, passive?: boolean, signal?: AbortSignal}|boolean} [options] Passed to addEventListener.
 * @return {Function} Unsubscribe function.
 */
export function delegateDocument( eventType, selector, handler, options ) {
	const listener = ( event ) => {
		const target = event.target?.closest?.( selector );

		if ( ! target || ! document.contains( target ) ) {
			return;
		}

		handler( event, target );
	};

	document.addEventListener( eventType, listener, options );

	return () => {
		document.removeEventListener( eventType, listener, options );
	};
}

/**
 * Register a delegated handler on a stable ancestor other than document.
 *
 * @param {EventTarget|null}                                                                     root      Stable ancestor that is never replaced.
 * @param {string}                                                                               eventType Event name.
 * @param {string}                                                                               selector  CSS selector matched via `Element.closest()`.
 * @param {Function}                                                                             handler   `( event, matchedElement ) => void`.
 * @param {{capture?: boolean, once?: boolean, passive?: boolean, signal?: AbortSignal}|boolean} [options] Passed to addEventListener.
 * @return {Function|null} Unsubscribe function, or null when root is missing.
 */
export function delegateOn( root, eventType, selector, handler, options ) {
	if ( ! root ) {
		return null;
	}

	const listener = ( event ) => {
		const target = event.target?.closest?.( selector );

		if ( ! target || ! root.contains( target ) ) {
			return;
		}

		handler( event, target );
	};

	root.addEventListener( eventType, listener, options );

	return () => {
		root.removeEventListener( eventType, listener, options );
	};
}
