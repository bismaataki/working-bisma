import { addFilter } from '@wordpress/hooks';
import { columnsWithInspectorControls } from './edit';

const addColumnsAttributes = ( settings, name ) => {
	if ( name !== 'core/columns' ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			isStackedOnTablet: {
				type: 'boolean',
				default: false,
			},
			isPhoneReversed: {
				type: 'boolean',
				default: false,
			},
		},
	};
};

const addColumnsSaveProps = ( extraProps, blockType, attributes ) => {
	if ( blockType.name !== 'core/columns' ) {
		return extraProps;
	}

	const classNames = [];

	if ( attributes.isStackedOnTablet ) {
		classNames.push( 'is-stacked-on-tablet' );
	}

	if ( attributes.isPhoneReversed ) {
		classNames.push( 'hw-columns-reverse-on-mobile' );
	}

	if ( classNames.length ) {
		extraProps.className = [ extraProps.className, ...classNames ]
			.filter( Boolean )
			.join( ' ' );
	}

	return extraProps;
};

addFilter(
	'blocks.registerBlockType',
	'happywood/columns/layout',
	addColumnsAttributes,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'happywood/columns/layout',
	addColumnsSaveProps,
);

addFilter(
	'editor.BlockEdit',
	'happywood/columns/layout',
	columnsWithInspectorControls,
);
