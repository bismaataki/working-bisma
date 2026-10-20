import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	TextControl,
	TextareaControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import ProductsPicker from './products-picker';
import { isValidSelectedProduct } from './product-utils';
import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const {
		fallbackHeading,
		fallbackMessage,
		fallbackProducts = [],
		showViewAll,
		viewAllLabel,
		viewAllUrl,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-shop-the-look-editor',
	} );

	const selectedProducts = Array.isArray( fallbackProducts )
		? fallbackProducts.filter( isValidSelectedProduct )
		: [];

	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Fallback content', 'happywood' ) }
					initialOpen={ true }
				>
					<TextControl
						__next40pxDefaultSize
						label={ __( 'Fallback heading', 'happywood' ) }
						help={ __(
							'Used when the post Shop the Look heading is empty.',
							'happywood',
						) }
						value={ fallbackHeading }
						onChange={ ( value ) =>
							setAttributes( { fallbackHeading: value } )
						}
					/>
					<TextareaControl
						label={ __( 'Fallback message', 'happywood' ) }
						help={ __(
							'Used when the post Shop the Look message is empty.',
							'happywood',
						) }
						value={ fallbackMessage }
						onChange={ ( value ) =>
							setAttributes( { fallbackMessage: value } )
						}
					/>
					<ProductsPicker
						products={ selectedProducts }
						onChange={ ( nextProducts ) =>
							setAttributes( { fallbackProducts: nextProducts } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'View All button', 'happywood' ) }
					initialOpen={ false }
				>
					<ToggleControl
						__nextHasNoMarginBottom
						label={ __( 'Show View All button', 'happywood' ) }
						checked={ !! showViewAll }
						onChange={ ( value ) =>
							setAttributes( { showViewAll: value } )
						}
					/>
					{ showViewAll && (
						<>
							<TextControl
								__next40pxDefaultSize
								label={ __( 'Button label', 'happywood' ) }
								value={ viewAllLabel }
								onChange={ ( value ) =>
									setAttributes( { viewAllLabel: value } )
								}
							/>
							<TextControl
								__next40pxDefaultSize
								label={ __( 'Button URL', 'happywood' ) }
								help={ __(
									'Leave empty to use the WooCommerce shop page URL.',
									'happywood',
								) }
								value={ viewAllUrl }
								onChange={ ( value ) =>
									setAttributes( { viewAllUrl: value } )
								}
							/>
						</>
					) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<ServerSideRender
					block="happywood/shop-the-look"
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
					EmptyResponsePlaceholder={ () => (
						<p className="hw-shop-the-look-editor__empty">
							{ __(
								'No products to display yet. Add Shop the Look products on the post or configure fallback products in the block settings.',
								'happywood',
							) }
						</p>
					) }
				/>
			</div>
		</>
	);
}
