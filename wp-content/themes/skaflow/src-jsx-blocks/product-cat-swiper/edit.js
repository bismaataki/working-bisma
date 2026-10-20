import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	PanelBody,
	Placeholder,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import CategoryTermPicker from './term-picker';
import {
	isValidSelectedTerm,
	getProductCatSwiperOrderByOptions,
	getProductCatSwiperOrderOptions,
} from './term-utils';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		terms = [],
		showArrowsDesktop = true,
		showArrowsMobile = false,
		showProgressBar = true,
		showLabels = true,
		orderBy = 'manual',
		order = 'asc',
	} = attributes;

	const selectedTerms = Array.isArray( terms )
		? terms.filter( isValidSelectedTerm )
		: [];

	const blockProps = useBlockProps( {
		className: 'sk-product-cat-swiper-editor',
	} );

	const previewAttributes = {
		terms: selectedTerms,
		showArrowsDesktop,
		showArrowsMobile,
		showProgressBar,
		showLabels,
		orderBy,
		order,
		previewMode: true,
		className: blockProps.className,
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Categories', 'skaflow' ) }
					initialOpen={ true }
				>
					<CategoryTermPicker
						terms={ selectedTerms }
						onChange={ ( nextTerms ) =>
							setAttributes( { terms: nextTerms } )
						}
					/>
					<SelectControl
						label={ __( 'Order by', 'skaflow' ) }
						value={ orderBy }
						options={ getProductCatSwiperOrderByOptions() }
						onChange={ ( value ) =>
							setAttributes( { orderBy: value || 'manual' } )
						}
						help={ __(
							'As selected preserves the token order above. Other options use WordPress term query ordering.',
							'skaflow',
						) }
					/>
					{ orderBy !== 'manual' && (
						<SelectControl
							label={ __( 'Order', 'skaflow' ) }
							value={ order }
							options={ getProductCatSwiperOrderOptions() }
							onChange={ ( value ) =>
								setAttributes( { order: value || 'asc' } )
							}
						/>
					) }
				</PanelBody>
				<PanelBody
					title={ __( 'Swiper display', 'skaflow' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show arrows on desktop', 'skaflow' ) }
						checked={ showArrowsDesktop }
						onChange={ ( value ) =>
							setAttributes( { showArrowsDesktop: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show arrows on mobile', 'skaflow' ) }
						checked={ showArrowsMobile }
						onChange={ ( value ) =>
							setAttributes( { showArrowsMobile: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show progress bar', 'skaflow' ) }
						checked={ showProgressBar }
						onChange={ ( value ) =>
							setAttributes( { showProgressBar: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show category labels', 'skaflow' ) }
						checked={ showLabels }
						onChange={ ( value ) =>
							setAttributes( { showLabels: value } )
						}
						help={ __(
							'Thumbnails: label below the image. Category cards: title inside the overlay.',
							'skaflow',
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ selectedTerms.length > 0 ? (
					<ServerSideRender
						block="skaflow/product-cat-swiper"
						attributes={ previewAttributes }
					/>
				) : (
					<Placeholder
						icon="slides"
						label={ __( 'Product Category Swiper', 'skaflow' ) }
						instructions={ __(
							'Select product categories in the block sidebar to build the swiper.',
							'skaflow',
						) }
					>
						<p className="sk-product-cat-swiper-editor__hint">
							{ __(
								'Use the Categories panel to search and add terms.',
								'skaflow',
							) }
						</p>
					</Placeholder>
				) }
			</div>
		</>
	);
}
