import { __ } from '@wordpress/i18n';

export const ICON_POSITIONS = [ 'left', 'right', 'top' ];
export const ALIGNMENTS = [ 'start', 'center', 'end' ];

export const DEFAULT_ICON_POSITION = 'left';
export const DEFAULT_ALIGNMENT = 'center';

export const ALLOWED_BLOCKS = [ 'outermost/icon-block', 'core/group' ];

export const INNER_BLOCKS_TEMPLATE = [
	[
		'outermost/icon-block',
		{
			hasNoIconFill: true,
			lock: { remove: true, move: true },
		},
	],
	[
		'core/group',
		{
			lock: { remove: true },
			style: { spacing: { blockGap: 'var:preset|spacing|x-small' } },
		},
		[
			[
				'core/heading',
				{
					level: 4,
					content: __( 'Add heading', 'skaflow' ),
				},
			],
			[
				'core/paragraph',
				{
					content: __( 'Add a short description.', 'skaflow' ),
				},
			],
		],
	],
];

export function sanitizeIconPosition( value ) {
	return ICON_POSITIONS.includes( value ) ? value : DEFAULT_ICON_POSITION;
}

export function sanitizeAlignment( value ) {
	return ALIGNMENTS.includes( value ) ? value : DEFAULT_ALIGNMENT;
}

/**
 * Return the layout object that corresponds to an icon position.
 *
 * @param {'left'|'right'|'top'} iconPosition
 * @return {{ type: 'flex', orientation: 'horizontal'|'vertical' }} Flex layout that matches the icon position.
 */
export function getLayoutFromIconPosition( iconPosition ) {
	return {
		type: 'flex',
		orientation: iconPosition === 'top' ? 'vertical' : 'horizontal',
	};
}

/**
 * Alignment options differ depending on whether the layout is horizontal
 * (cross-axis = vertical) or vertical (cross-axis = horizontal).
 *
 * @param {'left'|'right'|'top'} iconPosition
 */
export function getAlignmentOptions( iconPosition ) {
	if ( iconPosition === 'top' ) {
		return [
			{ label: __( 'Left', 'skaflow' ), value: 'start' },
			{ label: __( 'Center', 'skaflow' ), value: 'center' },
			{ label: __( 'Right', 'skaflow' ), value: 'end' },
		];
	}
	return [
		{ label: __( 'Top', 'skaflow' ), value: 'start' },
		{ label: __( 'Middle', 'skaflow' ), value: 'center' },
		{ label: __( 'Bottom', 'skaflow' ), value: 'end' },
	];
}

/**
 * Build the CSS modifier classes for the root block wrapper.
 *
 * @param {{ iconPosition: string, alignment: string }} attrs
 * @return {string} Modifier class string for the block wrapper.
 */
export function buildModifierClasses( { iconPosition, alignment } ) {
	return [
		`is-icon-${ sanitizeIconPosition( iconPosition ) }`,
		`is-align-${ sanitizeAlignment( alignment ) }`,
	].join( ' ' );
}
