import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import { withResponsiveBackgroundControls } from './edit';
import {
	isResponsiveBackgroundBlock,
	mergeResponsiveBackgroundClassName,
	RESPONSIVE_BACKGROUND_ATTRIBUTES,
} from './helpers';

const addResponsiveBackgroundAttributes = ( settings, name ) => {
	if ( ! isResponsiveBackgroundBlock( name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			...RESPONSIVE_BACKGROUND_ATTRIBUTES,
		},
	};
};

const addResponsiveBackgroundSaveProps = (
	extraProps,
	blockType,
	attributes,
) => {
	if ( ! isResponsiveBackgroundBlock( blockType.name ) ) {
		return extraProps;
	}

	return {
		...extraProps,
		className: mergeResponsiveBackgroundClassName(
			extraProps.className,
			attributes,
		),
	};
};

const withResponsiveBackgroundBlockListClasses = createHigherOrderComponent(
	( BlockListBlock ) => ( props ) => {
		if ( ! isResponsiveBackgroundBlock( props.name ) ) {
			return <BlockListBlock { ...props } />;
		}

		return (
			<BlockListBlock
				{ ...props }
				className={ mergeResponsiveBackgroundClassName(
					props.className,
					props.attributes,
				) }
			/>
		);
	},
	'withResponsiveBackgroundBlockListClasses',
);

addFilter(
	'blocks.registerBlockType',
	'skaflow/responsive-background/attributes',
	addResponsiveBackgroundAttributes,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'skaflow/responsive-background/save-props',
	addResponsiveBackgroundSaveProps,
);

addFilter(
	'editor.BlockEdit',
	'skaflow/responsive-background/controls',
	withResponsiveBackgroundControls,
);

addFilter(
	'editor.BlockListBlock',
	'skaflow/responsive-background/editor-classes',
	withResponsiveBackgroundBlockListClasses,
);
