/**
 * Extend core/query-pagination with page-1 Load More controls.
 *
 * @see context/kb/plugin-wc-shop-archive.md
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, ToggleControl, TextControl } = wp.components;

const BLOCK_NAME = 'core/query-pagination';

const withLoadMorePaginationAttributes = ( settings, blockName ) => {
	if ( BLOCK_NAME !== blockName ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			loadMore: {
				type: 'boolean',
				default: false,
			},
			loadMoreText: {
				type: 'string',
				default: __( 'Load More', 'happywood' ),
			},
			loadingText: {
				type: 'string',
				default: __( 'Loading…', 'happywood' ),
			},
		},
	};
};

const withLoadMorePaginationControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( BLOCK_NAME !== props.name ) {
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
							title: __( 'Load More', 'happywood' ),
							initialOpen: false,
						},
						createElement( ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Use Load More on page 1', 'happywood' ),
							help: __(
								'Replaces numbered pagination on the first archive page. Page 2 and later keep standard pagination for SEO.',
								'happywood',
							),
							checked: !! attributes.loadMore,
							onChange: ( loadMore ) => setAttributes( { loadMore } ),
						} ),
						attributes.loadMore &&
							createElement( TextControl, {
								__next40pxDefaultSize: true,
								label: __( 'Button label', 'happywood' ),
								value:
									attributes.loadMoreText ||
									__( 'Load More', 'happywood' ),
								onChange: ( loadMoreText ) =>
									setAttributes( { loadMoreText } ),
							} ),
						attributes.loadMore &&
							createElement( TextControl, {
								__next40pxDefaultSize: true,
								label: __( 'Loading label', 'happywood' ),
								value:
									attributes.loadingText ||
									__( 'Loading…', 'happywood' ),
								onChange: ( loadingText ) =>
									setAttributes( { loadingText } ),
							} ),
					),
				),
			);
		};
	},
	'withLoadMorePaginationControls',
);

addFilter(
	'blocks.registerBlockType',
	'happywood/load-more-pagination-attributes',
	withLoadMorePaginationAttributes,
);

addFilter(
	'editor.BlockEdit',
	'happywood/load-more-pagination-controls',
	withLoadMorePaginationControls,
);
