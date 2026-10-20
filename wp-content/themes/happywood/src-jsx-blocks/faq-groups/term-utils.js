import { __ } from '@wordpress/i18n';

/**
 * @return {Array<{ label: string, value: string }>} Order-by choices for the FAQ groups inspector.
 */
export function getFaqGroupsOrderByOptions() {
	return [
		{
			label: __( 'As selected', 'happywood' ),
			value: 'manual',
		},
		{
			label: __( 'Menu order', 'happywood' ),
			value: 'menu_order',
		},
		{
			label: __( 'Name', 'happywood' ),
			value: 'name',
		},
		{
			label: __( 'Slug', 'happywood' ),
			value: 'slug',
		},
		{
			label: __( 'Term ID', 'happywood' ),
			value: 'term_id',
		},
		{
			label: __( 'Count', 'happywood' ),
			value: 'count',
		},
		{
			label: __( 'Description', 'happywood' ),
			value: 'description',
		},
		{
			label: __( 'Term group', 'happywood' ),
			value: 'term_group',
		},
	];
}

/**
 * @return {Array<{ label: string, value: string }>} Sort-direction choices for the FAQ groups inspector.
 */
export function getFaqGroupsOrderOptions() {
	return [
		{
			label: __( 'Ascending', 'happywood' ),
			value: 'asc',
		},
		{
			label: __( 'Descending', 'happywood' ),
			value: 'desc',
		},
	];
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
