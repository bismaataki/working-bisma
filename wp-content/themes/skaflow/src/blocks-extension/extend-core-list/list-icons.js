import { SKAFLOW_LUCIDE_ICON_OPTIONS } from '../../js-admin/lucide-icon-options';
import {
	getThemeColorHexBySlug,
	getThemeColorSlugByHex,
} from '../../js-admin/icon-color-options';

/**
 * List block icon picker — block-level only (one icon for the whole list).
 *
 * Reuses the same `sk-has-icon` / `sk-icon-left` / `sk-icon-{slug}` classes
 * as the Button block, plus `sk-icon-has-color-{slug}` for theme palette
 * icon color. When no color is selected, Sass falls back to tertiary; an
 * explicit palette pick adds `sk-icon-has-color-{slug}` only.
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
	if ( ! attributes.skaflowIcon ) {
		return '';
	}

	const classes = [
		'sk-has-icon',
		'sk-icon-left',
		`sk-icon-${ attributes.skaflowIcon }`,
	];

	if ( attributes.skaflowIconColor ) {
		classes.push( `sk-icon-has-color-${ attributes.skaflowIconColor }` );
	}

	return classes.join( ' ' );
};

const renderIconPreviewSwatch = ( iconSlug ) => {
	if ( ! iconSlug ) {
		return createElement( 'span', {
			className: 'sk-icon-picker__preview-empty',
		} );
	}

	return createElement( 'span', {
		className: `sk-icon-preview sk-icon-${ iconSlug }`,
		'aria-hidden': 'true',
	} );
};

const renderIconComboboxOption = ( { item } ) =>
	createElement(
		'span',
		{ className: 'sk-icon-picker__option' },
		renderIconPreviewSwatch( item.value ),
		createElement( 'span', null, item.label ),
	);

addFilter(
	'blocks.registerBlockType',
	'skaflow/list-icon-attributes',
	( settings, blockName ) => {
		if ( ! isCoreListBlock( blockName ) ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				skaflowIcon: {
					type: 'string',
					default: '',
				},
				skaflowIconColor: {
					type: 'string',
					default: '',
				},
			},
		};
	},
);

const withSkaflowListIconControls = createHigherOrderComponent(
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
			const iconOptions = SKAFLOW_LUCIDE_ICON_OPTIONS.map( ( icon ) => ( {
				value: icon.slug,
				label: icon.label,
			} ) );
			const iconColorValue = getThemeColorHexBySlug(
				attributes.skaflowIconColor,
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
							title: __( 'List Icon', 'skaflow' ),
							initialOpen: false,
						},
						createElement(
							'div',
							{ className: 'sk-icon-picker' },
							createElement(
								'span',
								{ className: 'sk-icon-picker__preview' },
								renderIconPreviewSwatch(
									attributes.skaflowIcon,
								),
							),
							createElement( ComboboxControl, {
								__next40pxDefaultSize: true,
								className: 'sk-icon-picker__combobox',
								label: __( 'Icon', 'skaflow' ),
								placeholder: __( 'Search icons…', 'skaflow' ),
								value: attributes.skaflowIcon || null,
								options: iconOptions,
								__experimentalRenderItem:
									renderIconComboboxOption,
								onChange: ( iconSlug ) => {
									setAttributes( {
										skaflowIcon: iconSlug || '',
									} );
								},
							} ),
						),
						attributes.skaflowIcon &&
							createElement( PanelColorSettings, {
								title: __( 'Icon color', 'skaflow' ),
								colorSettings: [
									{
										label: __( 'Icon', 'skaflow' ),
										value: iconColorValue,
										onChange: ( nextColor ) => {
											setAttributes( {
												skaflowIconColor:
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
	'withSkaflowListIconControls',
);

addFilter(
	'editor.BlockEdit',
	'skaflow/list-icon-controls',
	withSkaflowListIconControls,
);

const withSkaflowListBlockListClasses = createHigherOrderComponent(
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
	'withSkaflowListBlockListClasses',
);

addFilter(
	'editor.BlockListBlock',
	'skaflow/list-icon-editor-classes',
	withSkaflowListBlockListClasses,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'skaflow/list-icon-save-classes',
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
