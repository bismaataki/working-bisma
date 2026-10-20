import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

const TITLE_LEVEL_OPTIONS = [
	{ label: 'H2', value: 2 },
	{ label: 'H3', value: 3 },
	{ label: 'H4', value: 4 },
];

export default function Edit( { attributes, setAttributes, context } ) {
	const {
		titleLevel,
		dateFormat,
		showImage,
		showCategory,
		showSingleCategoryTerm,
		showReadingTime,
		showExcerpt,
		showAuthor,
		showDate,
		hasBorder,
	} = attributes;

	const blockProps = useBlockProps();

	// ServerSideRender REST preview does not forward block context reliably.
	// Pass the loop post explicitly for core post-* inner blocks.
	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Card content', 'skaflow' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Title level', 'skaflow' ) }
						value={ titleLevel }
						options={ TITLE_LEVEL_OPTIONS }
						onChange={ ( value ) =>
							setAttributes( { titleLevel: Number( value ) } )
						}
					/>
					<ToggleControl
						label={ __( 'Show featured image', 'skaflow' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show category', 'skaflow' ) }
						checked={ showCategory }
						onChange={ ( value ) =>
							setAttributes( { showCategory: value } )
						}
					/>
					{ showCategory && (
						<ToggleControl
							label={ __(
								'Show only one category term',
								'skaflow',
							) }
							help={ __(
								'Uses the Taxonomy Terms block (all categories by default; optional limit with Yoast or Rank Math primary first). Turn off to show all categories via the core block.',
								'skaflow',
							) }
							checked={ showSingleCategoryTerm }
							onChange={ ( value ) =>
								setAttributes( {
									showSingleCategoryTerm: value,
								} )
							}
						/>
					) }
					<ToggleControl
						label={ __( 'Show reading time', 'skaflow' ) }
						checked={ showReadingTime }
						onChange={ ( value ) =>
							setAttributes( { showReadingTime: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show excerpt', 'skaflow' ) }
						checked={ showExcerpt }
						onChange={ ( value ) =>
							setAttributes( { showExcerpt: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show author', 'skaflow' ) }
						checked={ showAuthor }
						onChange={ ( value ) =>
							setAttributes( { showAuthor: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show date', 'skaflow' ) }
						checked={ showDate }
						onChange={ ( value ) => setAttributes( { showDate: value } ) }
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Formatting', 'skaflow' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Has border', 'skaflow' ) }
						checked={ hasBorder }
						onChange={ ( value ) =>
							setAttributes( { hasBorder: value } )
						}
					/>
					<SelectControl
						label={ __( 'Date format', 'skaflow' ) }
						value={ dateFormat }
						options={ [
							{
								label: __( 'March 15, 2024', 'skaflow' ),
								value: 'F j, Y',
							},
							{
								label: __( 'Mar 15, 2024', 'skaflow' ),
								value: 'M j, Y',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( { dateFormat: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender
					block="skaflow/post-card"
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
				/>
			</div>
		</>
	);
}
