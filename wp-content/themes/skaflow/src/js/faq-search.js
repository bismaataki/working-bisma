const SEARCH_ROOT_SELECTOR = '.sk-faq-search';
const GROUPS_SELECTOR = '[data-sk-faq-groups]';
const SECTION_SELECTOR = '[data-sk-faq-section]';
const ITEM_SELECTOR = '[data-sk-accordion-item]';
const COUNT_SELECTOR = '.sk-faq-groups__count';
const TITLE_SELECTORS = '.sk-accordion__label, .sk-accordion__title';
const HIDDEN_CLASS = 'is-search-hidden';
const DEBOUNCE_MS = 200;

/**
 * @param {number} count Visible FAQ count for a section.
 * @return {string} Count label for the FAQ search results.
 */
function formatFaqCountLabel( count ) {
	return 1 === count ? `${ count } QUESTION` : `${ count } QUESTIONS`;
}

/**
 * @param {HTMLElement} section
 * @param {number}      visibleCount
 * @param {boolean}     isFiltered
 */
function updateFaqSectionCount( section, visibleCount, isFiltered ) {
	const countEl = section.querySelector( COUNT_SELECTOR );

	if ( ! ( countEl instanceof HTMLElement ) ) {
		return;
	}

	const totalCount = Number.parseInt( countEl.dataset.skFaqCount || '0', 10 );
	const fallbackTotal = section.querySelectorAll( ITEM_SELECTOR ).length;
	const resolvedTotal =
		Number.isFinite( totalCount ) && totalCount > 0
			? totalCount
			: fallbackTotal;
	const displayCount = isFiltered ? visibleCount : resolvedTotal;

	countEl.textContent = formatFaqCountLabel( displayCount );
}

/**
 * @param {HTMLElement} groupsRoot
 * @param {string}      query
 */
function filterFaqGroups( groupsRoot, query ) {
	const normalized = query.trim().toLowerCase();
	const isFiltered = '' !== normalized;

	groupsRoot.querySelectorAll( SECTION_SELECTOR ).forEach( ( section ) => {
		if ( ! ( section instanceof HTMLElement ) ) {
			return;
		}

		let visibleCount = 0;

		section.querySelectorAll( ITEM_SELECTOR ).forEach( ( item ) => {
			if ( ! ( item instanceof HTMLElement ) ) {
				return;
			}

			const title = item.querySelector( TITLE_SELECTORS );
			const text = ( title?.textContent || '' ).trim().toLowerCase();
			const matches = ! isFiltered || text.includes( normalized );

			item.classList.toggle( HIDDEN_CLASS, ! matches );

			if ( matches ) {
				visibleCount += 1;
			}
		} );

		section.classList.toggle(
			HIDDEN_CLASS,
			isFiltered && visibleCount === 0,
		);
		updateFaqSectionCount( section, visibleCount, isFiltered );
	} );
}

/**
 * @param {HTMLFormElement} form
 */
function bindFaqSearchForm( form ) {
	const groupsRoot = document.querySelector( GROUPS_SELECTOR );

	if ( ! groupsRoot ) {
		return;
	}

	const input =
		form.querySelector( 'input[type="search"]' ) ||
		form.querySelector( 'input[type="text"]' );

	if ( ! ( input instanceof HTMLInputElement ) ) {
		return;
	}

	let debounceTimer = 0;

	const applyFilter = () => {
		filterFaqGroups( groupsRoot, input.value );
	};

	input.addEventListener( 'input', () => {
		window.clearTimeout( debounceTimer );
		debounceTimer = window.setTimeout( applyFilter, DEBOUNCE_MS );
	} );

	form.addEventListener( 'submit', ( event ) => {
		event.preventDefault();
		applyFilter();
	} );
}

/**
 * Initialize client-side FAQ question search on the archive page.
 */
export function initFaqSearch() {
	document.querySelectorAll( SEARCH_ROOT_SELECTOR ).forEach( ( element ) => {
		const form =
			element instanceof HTMLFormElement
				? element
				: element.querySelector( 'form' );

		if ( form instanceof HTMLFormElement ) {
			bindFaqSearchForm( form );
		}
	} );
}
