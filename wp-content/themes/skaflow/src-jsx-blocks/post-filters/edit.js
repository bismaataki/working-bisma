import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

import EditorPlaceholderNotice from '../editor-placeholder/notice';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { showSearch, showCategoryDropdown, showSort, showPills, showCount } =
		attributes;

	const blockProps = useBlockProps( {
		className: 'sk-post-filters-editor',
	} );

	const postsPageId = window?.skaflowEditor?.pageForPosts || 0;
	const previewUrl = postsPageId
		? `${ window.location.origin }/?page_id=${ postsPageId }`
		: `${ window.location.origin }/blogs/`;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Filter controls', 'skaflow' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show search', 'skaflow' ) }
						checked={ showSearch }
						onChange={ ( value ) =>
							setAttributes( { showSearch: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show category dropdown', 'skaflow' ) }
						checked={ showCategoryDropdown }
						onChange={ ( value ) =>
							setAttributes( { showCategoryDropdown: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show sort', 'skaflow' ) }
						checked={ showSort }
						onChange={ ( value ) => setAttributes( { showSort: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show category pills', 'skaflow' ) }
						checked={ showPills }
						onChange={ ( value ) =>
							setAttributes( { showPills: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show article count', 'skaflow' ) }
						checked={ showCount }
						onChange={ ( value ) =>
							setAttributes( { showCount: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<EditorPlaceholderNotice
					title={ __( 'Post Filters', 'skaflow' ) }
					description={ __(
						'Renders on the blog listing page only. Uses full-page GET URLs with category_name, s, orderby, and order.',
						'skaflow',
					) }
					displayLinks={ [
						{
							label: __( 'Preview blog listing', 'skaflow' ),
							url: previewUrl,
						},
					] }
				/>
			</div>
		</>
	);
}
