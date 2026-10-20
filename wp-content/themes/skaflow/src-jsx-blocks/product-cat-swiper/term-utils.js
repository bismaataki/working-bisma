import { __ } from '@wordpress/i18n';

/**
 * @return {Array<{ label: string, value: string }>} Order-by choices for the category swiper inspector.
 */
export function getProductCatSwiperOrderByOptions() {
	return [
		{
			label: __( 'As selected', 'skaflow' ),
			value: 'manual',
		},
		{
			label: __( 'Menu order', 'skaflow' ),
			value: 'menu_order',
		},
		{
			label: __( 'Name', 'skaflow' ),
			value: 'name',
		},
		{
			label: __( 'Slug', 'skaflow' ),
			value: 'slug',
		},
		{
			label: __( 'Term ID', 'skaflow' ),
			value: 'term_id',
		},
		{
			label: __( 'Count', 'skaflow' ),
			value: 'count',
		},
		{
			label: __( 'Description', 'skaflow' ),
			value: 'description',
		},
		{
			label: __( 'Term group', 'skaflow' ),
			value: 'term_group',
		},
	];
}

/**
 * @return {Array<{ label: string, value: string }>} Sort-direction choices for the category swiper inspector.
 */
export function getProductCatSwiperOrderOptions() {
	return [
		{
			label: __( 'Ascending', 'skaflow' ),
			value: 'asc',
		},
		{
			label: __( 'Descending', 'skaflow' ),
			value: 'desc',
		},
	];
}

/**
 * Build hierarchical option labels for product_cat terms.
 *
 * @param {Array<{ id: number, name: string, parent?: number }>} terms
 * @param {number}                                               parent
 * @param {number}                                               depth
 * @return {Array<{ id: number, name: string, label: string }>} Flattened terms with hierarchical labels.
 */
export function buildHierarchicalTermOptions( terms, parent = 0, depth = 0 ) {
	if ( ! Array.isArray( terms ) || terms.length === 0 ) {
		return [];
	}

	return terms
		.filter( ( term ) => ( term.parent || 0 ) === parent )
		.flatMap( ( term ) => [
			{
				id: term.id,
				name: term.name,
				label: `${ '— '.repeat( depth ) }${ term.name }`,
			},
			...buildHierarchicalTermOptions( terms, term.id, depth + 1 ),
		] );
}

/**
 * @param {{ id: number, name: string, parent?: number }} term
 * @param {Map<number, object>}                           termsById
 * @return {number} Nesting depth of the term in the tree.
 */
export function getTermDepth( term, termsById ) {
	let depth = 0;
	let parentId = term.parent || 0;

	while ( parentId > 0 && termsById.has( parentId ) ) {
		depth += 1;
		parentId = termsById.get( parentId ).parent || 0;
	}

	return depth;
}

/**
 * @param {{ id: number, name: string, parent?: number }} term
 * @param {Map<number, object>}                           termsById
 * @return {string} Indented label for the term picker.
 */
export function getTermPickerLabel( term, termsById ) {
	const depth = getTermDepth( term, termsById );

	return `${ '— '.repeat( depth ) }${ term.name }`;
}

/**
 * @param {unknown} term
 * @return {term is { id: number, name: string }} Whether the value is a selected term object.
 */
export function isValidSelectedTerm( term ) {
	return (
		!! term &&
		typeof term === 'object' &&
		Number.isFinite( term.id ) &&
		term.id > 0 &&
		typeof term.name === 'string' &&
		term.name.length > 0
	);
}
