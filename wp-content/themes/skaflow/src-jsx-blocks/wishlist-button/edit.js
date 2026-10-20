import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { context } = attributes;

	const blockProps = useBlockProps( {
		className:
			context === 'summary'
				? 'sk-wishlist-button sk-wishlist-button--summary sk-wishlist-button-editor'
				: 'sk-wishlist-button sk-wishlist-button-editor',
	} );

	const label =
		context === 'summary'
			? __( 'Add to wishlist', 'skaflow' )
			: __( 'Wishlist', 'skaflow' );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Wishlist', 'skaflow' ) }>
					<SelectControl
						label={ __( 'Display context', 'skaflow' ) }
						value={ context }
						options={ [
							{
								label: __( 'Product card', 'skaflow' ),
								value: 'card',
							},
							{
								label: __(
									'Single product summary',
									'skaflow',
								),
								value: 'summary',
							},
						] }
						onChange={ ( value ) => setAttributes( { context: value } ) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<button
					type="button"
					className="sk-wishlist-trigger"
					disabled
					aria-pressed="false"
				>
					{ context === 'summary' ? (
						<span className="sk-wishlist-trigger__label">
							{ label }
						</span>
					) : (
						<span className="screen-reader-text">{ label }</span>
					) }
				</button>
			</div>
		</>
	);
}
