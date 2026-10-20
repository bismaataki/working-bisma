import { SKAFLOW_LUCIDE_ICON_OPTIONS } from '../../js-admin/lucide-icon-options';
/**
 * The icon picker only ever needs to add a `sk-icon-{slug}` class (plus
 * `sk-has-icon`/`sk-icon-left`/`sk-icon-right`) to the Button block - both
 * the editor preview (incl. inside its `<iframe name="editor-canvas">`)
 * and the front end render the glyph purely from CSS via that class,
 * using the Lucide icon font. No DOM/element injection is needed on
 * either side. See assets/css/button-icon-font.css (enqueued globally by
 * Skaflow\Lucide_Icons in lib/class-lucide-icons.php) for the font
 * and the `::before` rules that do the actual rendering.
 *
 * All classes added here use a `sk-` prefix (rather than generic names
 * like `has-icon`/`icon-left`) to avoid colliding with classes from other
 * plugins/themes that use similarly-generic icon-related class names.
 *
 * The picker itself (below) reuses that same font/CSS via a generic
 * `.sk-icon-preview.sk-icon-{slug}` class to show real glyph previews -
 * both for the currently selected icon and for each option in the
 * ComboboxControl's dropdown (via `__experimentalRenderItem`).
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, SelectControl, ComboboxControl } = wp.components;

const isCoreButtonBlock = ( blockName ) => blockName === 'core/button';

const getIconClassNames = ( attributes = {} ) => {
	if ( ! attributes.skaflowIcon ) {
		return '';
	}

	const iconPosition =
		attributes.skaflowIconPosition === 'right'
			? 'sk-icon-right'
			: 'sk-icon-left';

	return [
		'sk-has-icon',
		iconPosition,
		`sk-icon-${ attributes.skaflowIcon }`,
	].join( ' ' );
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
	'skaflow/button-icon-attributes',
	( settings, blockName ) => {
		if ( ! isCoreButtonBlock( blockName ) ) {
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
				skaflowIconPosition: {
					type: 'string',
					default: 'left',
				},
			},
		};
	},
);

const withSkaflowButtonIconControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( ! isCoreButtonBlock( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			const { attributes, setAttributes } = props;
			const iconOptions = SKAFLOW_LUCIDE_ICON_OPTIONS.map( ( icon ) => ( {
				value: icon.slug,
				label: icon.label,
			} ) );

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
							title: __( 'Button Icon', 'skaflow' ),
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
									const nextAttributes = {
										skaflowIcon: iconSlug || '',
									};

									if (
										iconSlug &&
										! attributes.skaflowIconPosition
									) {
										nextAttributes.skaflowIconPosition =
											'left';
									}

									setAttributes( nextAttributes );
								},
							} ),
						),
						createElement( SelectControl, {
							label: __( 'Icon Position', 'skaflow' ),
							value: attributes.skaflowIconPosition || 'left',
							help: __(
								'Default icon position is left.',
								'skaflow',
							),
							options: [
								{
									value: 'left',
									label: __( 'Left', 'skaflow' ),
								},
								{
									value: 'right',
									label: __( 'Right', 'skaflow' ),
								},
							],
							disabled: ! attributes.skaflowIcon,
							onChange: ( nextPosition ) => {
								setAttributes( {
									skaflowIconPosition:
										nextPosition || 'left',
								} );
							},
						} ),
					),
				),
			);
		};
	},
	'withSkaflowButtonIconControls',
);

addFilter(
	'editor.BlockEdit',
	'skaflow/button-icon-controls',
	withSkaflowButtonIconControls,
);

const withSkaflowButtonBlockListClasses = createHigherOrderComponent(
	( BlockListBlock ) => {
		return ( props ) => {
			if ( ! isCoreButtonBlock( props.name ) ) {
				return createElement( BlockListBlock, props );
			}

			const iconClassNames = getIconClassNames( props.attributes );
			const className = [ props.className, iconClassNames ]
				.filter( Boolean )
				.join( ' ' );

			return createElement( BlockListBlock, { ...props, className } );
		};
	},
	'withSkaflowButtonBlockListClasses',
);

addFilter(
	'editor.BlockListBlock',
	'skaflow/button-icon-editor-classes',
	withSkaflowButtonBlockListClasses,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'skaflow/button-icon-save-classes',
	( extraProps, blockType, attributes ) => {
		if ( ! isCoreButtonBlock( blockType.name ) ) {
			return extraProps;
		}

		const iconClassNames = getIconClassNames( attributes );

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
