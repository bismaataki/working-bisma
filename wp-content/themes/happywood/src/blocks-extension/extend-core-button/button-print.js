/**
 * Inspector toggle on core/button that prints the current page.
 *
 * Writes `data-hw-print-order` onto the saved <a>. Frontend JS in
 * src/js/order-confirmation.js calls window.print() on that attribute.
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, ToggleControl } = wp.components;

const isCoreButtonBlock = ( blockName ) => blockName === 'core/button';

addFilter(
	'blocks.registerBlockType',
	'happywood/button-print-attribute',
	( settings, blockName ) => {
		if ( ! isCoreButtonBlock( blockName ) ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				happywoodPrint: {
					type: 'boolean',
					default: false,
				},
			},
		};
	},
);

const withHappywoodButtonPrintControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( ! isCoreButtonBlock( props.name ) ) {
				return createElement( BlockEdit, props );
			}

			const { attributes, setAttributes } = props;

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
							title: __( 'Print page', 'happywood' ),
							initialOpen: false,
						},
						createElement( ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Print this page', 'happywood' ),
							help: __(
								'Clicking this button prints the current page. The link URL is ignored.',
								'happywood',
							),
							checked: !! attributes.happywoodPrint,
							onChange: ( happywoodPrint ) => {
								const nextAttributes = { happywoodPrint };

								if ( happywoodPrint && ! attributes.url ) {
									nextAttributes.url = '#';
								}

								setAttributes( nextAttributes );
							},
						} ),
					),
				),
			);
		};
	},
	'withHappywoodButtonPrintControls',
);

addFilter(
	'editor.BlockEdit',
	'happywood/button-print-controls',
	withHappywoodButtonPrintControls,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'happywood/button-print-save-props',
	( extraProps, blockType, attributes ) => {
		if ( ! isCoreButtonBlock( blockType.name ) || ! attributes.happywoodPrint ) {
			return extraProps;
		}

		const next = {
			...extraProps,
			'data-hw-print-order': '1',
		};

		if ( ! extraProps.href && ! attributes.url ) {
			next.href = '#';
		}

		return next;
	},
);
