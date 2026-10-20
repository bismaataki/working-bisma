import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const { titleTag = 'h2' } = attributes;

	const blockProps = useBlockProps();

	// ServerSideRender REST preview does not forward block context reliably.
	// It defaults post_id to the edited page; pass the loop product explicitly.
	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Product title', 'skaflow' ) }
					initialOpen={ true }
				>
					<SelectControl
						label={ __( 'Title heading tag', 'skaflow' ) }
						help={ __(
							'Use H3 inside related or upsell sections so section headings stay the primary H2.',
							'skaflow',
						) }
						value={ titleTag }
						options={ [
							{
								label: __( 'H2 (default)', 'skaflow' ),
								value: 'h2',
							},
							{ label: __( 'H3', 'skaflow' ), value: 'h3' },
						] }
						onChange={ ( value ) =>
							setAttributes( { titleTag: value || 'h2' } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender
					block="skaflow/product-card"
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
				/>
			</div>
		</>
	);
}
