import { HAPPYWOOD_LUCIDE_ICON_OPTIONS } from '../../js-admin/lucide-icon-options';
/**
 * The icon picker only ever needs to add a `hw-icon-{slug}` class (plus
 * `hw-has-icon`/`hw-icon-left`/`hw-icon-right`) to the Button block - both
 * the editor preview (incl. inside its `<iframe name="editor-canvas">`)
 * and the front end render the glyph purely from CSS via that class,
 * using the Lucide icon font. No DOM/element injection is needed on
 * either side. See assets/css/button-icon-font.css (enqueued globally by
 * Happywood\Lucide_Icons in lib/class-lucide-icons.php) for the font
 * and the `::before` rules that do the actual rendering.
 *
 * All classes added here use a `hw-` prefix (rather than generic names
 * like `has-icon`/`icon-left`) to avoid colliding with classes from other
 * plugins/themes that use similarly-generic icon-related class names.
 *
 * The picker itself (below) reuses that same font/CSS via a generic
 * `.hw-icon-preview.hw-icon-{slug}` class to show real glyph previews -
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
	if ( ! attributes.happywoodIcon ) {
		return '';
	}

	const iconPosition =
		attributes.happywoodIconPosition === 'right'
			? 'hw-icon-right'
			: 'hw-icon-left';

	return [
		'hw-has-icon',
		iconPosition,
		`hw-icon-${ attributes.happywoodIcon }`,
	].join( ' ' );
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
	'happywood/button-icon-attributes',
	( settings, blockName ) => {
		if ( ! isCoreButtonBlock( blockName ) ) {
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
				happywoodIconPosition: {
					type: 'string',
					default: 'left',
				},
			},
		};
	},
);

const withHappywoodButtonIconControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( ! isCoreButtonBlock( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			const { attributes, setAttributes } = props;
			const iconOptions = HAPPYWOOD_LUCIDE_ICON_OPTIONS.map( ( icon ) => ( {
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
							title: __( 'Button Icon', 'happywood' ),
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
									const nextAttributes = {
										happywoodIcon: iconSlug || '',
									};

									if (
										iconSlug &&
										! attributes.happywoodIconPosition
									) {
										nextAttributes.happywoodIconPosition =
											'left';
									}

									setAttributes( nextAttributes );
								},
							} ),
						),
						createElement( SelectControl, {
							label: __( 'Icon Position', 'happywood' ),
							value: attributes.happywoodIconPosition || 'left',
							help: __(
								'Default icon position is left.',
								'happywood',
							),
							options: [
								{
									value: 'left',
									label: __( 'Left', 'happywood' ),
								},
								{
									value: 'right',
									label: __( 'Right', 'happywood' ),
								},
							],
							disabled: ! attributes.happywoodIcon,
							onChange: ( nextPosition ) => {
								setAttributes( {
									happywoodIconPosition:
										nextPosition || 'left',
								} );
							},
						} ),
					),
				),
			);
		};
	},
	'withHappywoodButtonIconControls',
);

addFilter(
	'editor.BlockEdit',
	'happywood/button-icon-controls',
	withHappywoodButtonIconControls,
);

const withHappywoodButtonBlockListClasses = createHigherOrderComponent(
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
	'withHappywoodButtonBlockListClasses',
);

addFilter(
	'editor.BlockListBlock',
	'happywood/button-icon-editor-classes',
	withHappywoodButtonBlockListClasses,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'happywood/button-icon-save-classes',
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
