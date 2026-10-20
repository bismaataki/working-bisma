import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { context } = attributes;

	const blockProps = useBlockProps( {
		className:
			context === 'summary'
				? 'hw-wishlist-button hw-wishlist-button--summary hw-wishlist-button-editor'
				: 'hw-wishlist-button hw-wishlist-button-editor',
	} );

	const label =
		context === 'summary'
			? __( 'Add to wishlist', 'happywood' )
			: __( 'Wishlist', 'happywood' );

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Wishlist', 'happywood' ) }>
					<SelectControl
						label={ __( 'Display context', 'happywood' ) }
						value={ context }
						options={ [
							{
								label: __( 'Product card', 'happywood' ),
								value: 'card',
							},
							{
								label: __(
									'Single product summary',
									'happywood',
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
					className="hw-wishlist-trigger"
					disabled
					aria-pressed="false"
				>
					{ context === 'summary' ? (
						<span className="hw-wishlist-trigger__label">
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
