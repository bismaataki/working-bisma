/**
 * Inspector toggle on core/button that prints the current page.
 *
 * Writes `data-sk-print-order` onto the saved <a>. Frontend JS in
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
	'skaflow/button-print-attribute',
	( settings, blockName ) => {
		if ( ! isCoreButtonBlock( blockName ) ) {
			return settings;
		}

		return {
			...settings,
			attributes: {
				...settings.attributes,
				skaflowPrint: {
					type: 'boolean',
					default: false,
				},
			},
		};
	},
);

const withSkaflowButtonPrintControls = createHigherOrderComponent(
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
							title: __( 'Print page', 'skaflow' ),
							initialOpen: false,
						},
						createElement( ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Print this page', 'skaflow' ),
							help: __(
								'Clicking this button prints the current page. The link URL is ignored.',
								'skaflow',
							),
							checked: !! attributes.skaflowPrint,
							onChange: ( skaflowPrint ) => {
								const nextAttributes = { skaflowPrint };

								if ( skaflowPrint && ! attributes.url ) {
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
	'withSkaflowButtonPrintControls',
);

addFilter(
	'editor.BlockEdit',
	'skaflow/button-print-controls',
	withSkaflowButtonPrintControls,
);

addFilter(
	'blocks.getSaveContent.extraProps',
	'skaflow/button-print-save-props',
	( extraProps, blockType, attributes ) => {
		if ( ! isCoreButtonBlock( blockType.name ) || ! attributes.skaflowPrint ) {
			return extraProps;
		}

		const next = {
			...extraProps,
			'data-sk-print-order': '1',
		};

		if ( ! extraProps.href && ! attributes.url ) {
			next.href = '#';
		}

		return next;
	},
);
