import { HAPPYWOOD_LUCIDE_ICON_OPTIONS } from '../../js-admin/lucide-icon-options';
import {
	getThemeColorHexBySlug,
	getThemeColorSlugByHex,
} from '../../js-admin/icon-color-options';

/**
 * List block icon picker — block-level only (one icon for the whole list).
 *
 * Reuses the same `hw-has-icon` / `hw-icon-left` / `hw-icon-{slug}` classes
 * as the Button block, plus `hw-icon-has-color-{slug}` for theme palette
 * icon color. When no color is selected, Sass falls back to tertiary; an
 * explicit palette pick adds `hw-icon-has-color-{slug}` only.
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement } = wp.element;
const { InspectorControls, PanelColorSettings } = wp.blockEditor || wp.editor;
const { PanelBody, ComboboxControl } = wp.components;
const { useSelect } = wp.data;

const isCoreListBlock = ( blockName ) => blockName === 'core/list';

const getListIconClassNames = ( attributes = {} ) => {
	if ( ! attributes.happywoodIcon ) {
		return '';
	}

	const classes = [
		'hw-has-icon',
		'hw-icon-left',
		`hw-icon-${ attributes.happywoodIcon }`,
	];

	if ( attributes.happywoodIconColor ) {
		classes.push( `hw-icon-has-color-${ attributes.happywoodIconColor }` );
	}

	return classes.join( ' ' );
};

const renderIconPreviewSwatch = ( iconSlug ) => {
	if ( ! iconSlug ) {
		return createElement( 'span', {
			className: 'hw-icon-picker__preview-empty',
		} );
	}

	return createElement( 'span', {
		className: `hw-icon-preview hw-icon-${ iconSlug }`,
		'aria-hidden': 'true',
	} );
};

const renderIconComboboxOption = ( { item } ) =>
	createElement(
		'span',
		{ className: 'hw-icon-picker__option' },
		renderIconPreviewSwatch( item.value ),
		createElement( 'span', null, item.label ),
	);

addFilter(
	'blocks.registerBlockType',
	'happywood/list-icon-attributes',
	( settings, blockName ) => {
		if ( ! isCoreListBlock( blockName ) ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				happywoodIcon: {
					type: 'string',
					default: '',
				},
				happywoodIconColor: {
					type: 'string',
					default: '',
				},
			},
		};
	},
);

const withHappywoodListIconControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( ! isCoreListBlock( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			const { attributes, setAttributes } = props;
			const themeColors = useSelect(
				( select ) =>
					select( 'core/block-editor' ).getSettings().colors || [],
				[],
			);
			const iconOptions = HAPPYWOOD_LUCIDE_ICON_OPTIONS.map( ( icon ) => ( {
				value: icon.slug,
				label: icon.label,
			} ) );
			const iconColorValue = getThemeColorHexBySlug(
				attributes.happywoodIconColor,
				themeColors,
			);

			return createElement(
				Fragment,
				null,
				createElement( BlockEdit, props ),
				createElement(
					InspectorControls,
					null,
					createElement(
						PanelBody,
						{
							title: __( 'List Icon', 'happywood' ),
							initialOpen: false,
						},
						createElement(
							'div',
							{ className: 'hw-icon-picker' },
							createElement(
								'span',
								{ className: 'hw-icon-picker__preview' },
								renderIconPreviewSwatch(
									attributes.happywoodIcon,
								),
							),
							createElement( ComboboxControl, {
								__next40pxDefaultSize: true,
								className: 'hw-icon-picker__combobox',
								label: __( 'Icon', 'happywood' ),
								placeholder: __( 'Search icons…', 'happywood' ),
								value: attributes.happywoodIcon || null,
								options: iconOptions,
								__experimentalRenderItem:
									renderIconComboboxOption,
								onChange: ( iconSlug ) => {
									setAttributes( {
										happywoodIcon: iconSlug || '',
									} );
								},
							} ),
						),
						attributes.happywoodIcon &&
							createElement( PanelColorSettings, {
								title: __( 'Icon color', 'happywood' ),
								colorSettings: [
									{
										label: __( 'Icon', 'happywood' ),
										value: iconColorValue,
										onChange: ( nextColor ) => {
											setAttributes( {
												happywoodIconColor:
													getThemeColorSlugByHex(
														nextColor,
														themeColors,
													),
											} );
										},
									},
								],
							} ),
					),
				),
			);
		};
	},
	'withHappywoodListIconControls',
);

addFilter(
	'editor.BlockEdit',
	'happywood/list-icon-controls',
	withHappywoodListIconControls,
);

const withHappywoodListBlockListClasses = createHigherOrderComponent(
	( BlockListBlock ) => {
		return ( props ) => {
			if ( ! isCoreListBlock( props.name ) ) {
				return createElement( BlockListBlock, props );
			}

			const iconClassNames = getListIconClassNames( props.attributes );
			const className = [ props.className, iconClassNames ]
				.filter( Boolean )
				.join( ' ' );

			return createElement( BlockListBlock, { ...props, className } );
		};
	},
	'withHappywoodListBlockListClasses',
);

addFilter(
	'editor.BlockListBlock',
	'happywood/list-icon-editor-classes',
	withHappywoodListBlockListClasses,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'happywood/list-icon-save-classes',
	( extraProps, blockType, attributes ) => {
		if ( ! isCoreListBlock( blockType.name ) ) {
			return extraProps;
		}

		const iconClassNames = getListIconClassNames( attributes );

		if ( ! iconClassNames ) {
			return extraProps;
		}

		return {
			...extraProps,
			className: [ extraProps.className, iconClassNames ]
				.filter( Boolean )
				.join( ' ' ),
		};
	},
);
