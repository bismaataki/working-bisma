import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	SelectControl,
	Spinner,
	TextControl,
	ToggleControl,
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const { taxonomy, limit, isLink } = attributes;

	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	const taxonomies = useSelect( ( select ) => {
		const records = select( 'core' ).getEntityRecords( 'root', 'taxonomy', {
			per_page: -1,
		} );

		if ( ! records ) {
			return [];
		}

		return records.filter( ( record ) => record?.visibility?.show_ui );
	}, [] );

	const taxonomyOptions = taxonomies.map( ( record ) => ( {
		label: record.labels?.singular_name || record.name,
		value: record.slug,
	} ) );

	const blockProps = useBlockProps( {
		className: 'hw-taxonomy-terms-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Taxonomy Terms', 'happywood' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Taxonomy', 'happywood' ) }
						value={ taxonomy || 'category' }
						options={ taxonomyOptions }
						onChange={ ( value ) =>
							setAttributes( { taxonomy: value || 'category' } )
						}
					/>
					<TextControl
						__next40pxDefaultSize
						__nextHasNoMarginBottom
						type="number"
						label={ __( 'Limit', 'happywood' ) }
						help={ __(
							'Leave blank to show all assigned terms. Enter a number to cap how many are shown (Yoast or Rank Math primary term first).',
							'happywood',
						) }
						value={ limit ?? '' }
						min={ 0 }
						onChange={ ( value ) => {
							if (
								value === '' ||
								value === undefined ||
								value === null
							) {
								setAttributes( { limit: undefined } );
								return;
							}

							setAttributes( { limit: Number( value ) } );
						} }
					/>
					<ToggleControl
						label={ __( 'Link terms', 'happywood' ) }
						help={ __(
							'When enabled, publicly viewable terms link to their archive.',
							'happywood',
						) }
						checked={ isLink }
						onChange={ ( value ) => setAttributes( { isLink: value } ) }
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
					LoadingResponsePlaceholder={ () => (
						<div className="hw-taxonomy-terms-editor__placeholder">
							<Spinner />
							<p>{ __( 'Loading taxonomy terms…', 'happywood' ) }</p>
						</div>
					) }
					ErrorResponsePlaceholder={ ( { response } ) => (
						<div className="hw-taxonomy-terms-editor__placeholder hw-taxonomy-terms-editor__placeholder--error">
							<p>
								{ __(
									'Unable to load taxonomy terms preview.',
									'happywood',
								) }
							</p>
							{ response?.message ? (
								<p>{ response.message }</p>
							) : null }
						</div>
					) }
					EmptyResponsePlaceholder={ () => null }
				/>
			</div>
		</>
	);
}
