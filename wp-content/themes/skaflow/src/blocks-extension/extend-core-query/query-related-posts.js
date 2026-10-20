/**
 * Extend core/query with Related Posts query mode.
 *
 * @see context/kb/query.md
 */

const { __ } = wp.i18n;
const { addFilter } = wp.hooks;
const { createHigherOrderComponent } = wp.compose;
const { Fragment, createElement } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { PanelBody, ToggleControl, SelectControl } = wp.components;
const { useSelect } = wp.data;

const BLOCK_NAME = 'core/query';

const withRelatedPostsQueryControls = createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			if ( BLOCK_NAME !== props.name ) {
				return createElement( BlockEdit, props );
			}

			const { attributes, setAttributes } = props;
			const query = attributes.query || {};
			const postType = query.postType || 'post';
			const isRelated = !! query.isRelated;
			const relatedFallbackToDefault = !! query.relatedFallbackToDefault;

			const taxonomyOptions = useSelect(
				( select ) => {
					const { getTaxonomies } = select( 'core' );
					const taxonomies =
						getTaxonomies( { type: postType, per_page: -1 } ) || [];

					return taxonomies
						.filter( ( taxonomy ) => taxonomy?.visibility?.public )
						.map( ( taxonomy ) => ( {
							label: taxonomy.name,
							value: taxonomy.slug,
						} ) );
				},
				[ postType ],
			);

			const updateQuery = ( changes ) => {
				setAttributes( {
					query: {
						...query,
						...changes,
					},
				} );
			};

			const relatedTaxonomy =
				query.relatedTaxonomy ||
				( taxonomyOptions.find( ( option ) => 'category' === option.value )
					? 'category'
					: taxonomyOptions[ 0 ]?.value || 'category' );

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
							title: __( 'Related Posts', 'skaflow' ),
							initialOpen: false,
						},
						createElement( ToggleControl, {
							__nextHasNoMarginBottom: true,
							label: __( 'Show related posts', 'skaflow' ),
							help: __(
								'On singular templates, loads posts that share terms with the current post. Frontend only — the editor preview uses the REST API and may differ until extended separately.',
								'skaflow',
							),
							checked: isRelated,
							onChange: ( nextIsRelated ) => {
								const nextQuery = {
									isRelated: nextIsRelated,
								};

								if ( nextIsRelated ) {
									nextQuery.inherit = false;
									nextQuery.relatedTaxonomy = relatedTaxonomy;
									nextQuery.relatedFallbackToDefault = true;
								}

								updateQuery( nextQuery );
							},
						} ),
						isRelated &&
							createElement( SelectControl, {
								__next40pxDefaultSize: true,
								__nextHasNoMarginBottom: true,
								label: __( 'Match by taxonomy', 'skaflow' ),
								value: relatedTaxonomy,
								options:
									taxonomyOptions.length > 0
										? taxonomyOptions
										: [
											{
												label: __(
													'Category',
													'skaflow',
												),
												value: 'category',
											},
										],
								onChange: ( nextTaxonomy ) =>
									updateQuery( {
										relatedTaxonomy: nextTaxonomy,
									} ),
							} ),
						isRelated &&
							createElement( ToggleControl, {
								__nextHasNoMarginBottom: true,
								label: __(
									'Fallback to default query when empty',
									'skaflow',
								),
								help: __(
									'When no related posts match, use this Query Loop’s normal settings instead of showing an empty grid.',
									'skaflow',
								),
								checked: relatedFallbackToDefault,
								onChange: ( nextFallback ) =>
									updateQuery( {
										relatedFallbackToDefault: nextFallback,
									} ),
							} ),
					),
				),
			);
		};
	},
	'withRelatedPostsQueryControls',
);

addFilter(
	'editor.BlockEdit',
	'skaflow/query-related-posts-controls',
	withRelatedPostsQueryControls,
);
