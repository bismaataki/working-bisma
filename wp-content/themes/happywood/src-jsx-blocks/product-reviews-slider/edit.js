import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	RangeControl,
	SelectControl,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import CategoryTermPicker from '../product-cat-swiper/term-picker';
import ProductPicker from './product-picker';
import { isValidSelectedTerm } from '../product-cat-swiper/term-utils';
import metadata from './block.json';

import './editor.scss';

const ORDER_BY_OPTIONS = [
	{ label: __( 'Highest rating', 'happywood' ), value: 'rating' },
	{ label: __( 'Most recent', 'happywood' ), value: 'date' },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		reviewsToShow = 8,
		orderBy = 'rating',
		productId = 0,
		categoryIds = [],
		verifiedOnly = true,
		showSummary = true,
		showReviewRating = true,
		showReviewerName = true,
		showReviewContent = true,
		showProductName = true,
		showReviewDate = false,
		showArrowsDesktop = true,
		showArrowsMobile = false,
		showProgressBar = true,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-product-reviews-slider-editor',
	} );

	const selectedTerms = Array.isArray( categoryIds )
		? categoryIds.filter( isValidSelectedTerm )
		: [];

	const previewAttributes = {
		reviewsToShow,
		orderBy,
		productId,
		categoryIds: selectedTerms,
		verifiedOnly,
		showSummary,
		showReviewRating,
		showReviewerName,
		showReviewContent,
		showProductName,
		showReviewDate,
		showArrowsDesktop,
		showArrowsMobile,
		showProgressBar,
		previewMode: true,
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Reviews', 'happywood' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Reviews to show', 'happywood' ) }
						value={ reviewsToShow }
						onChange={ ( value ) =>
							setAttributes( {
								reviewsToShow: value ?? 8,
							} )
						}
						min={ 4 }
						max={ 20 }
					/>
					<SelectControl
						label={ __( 'Order by', 'happywood' ) }
						value={ orderBy }
						options={ ORDER_BY_OPTIONS }
						onChange={ ( value ) =>
							setAttributes( { orderBy: value || 'rating' } )
						}
					/>
					<ProductPicker
						productId={ productId }
						onChange={ ( value ) =>
							setAttributes( { productId: value } )
						}
					/>
					<CategoryTermPicker
						terms={ selectedTerms }
						onChange={ ( nextTerms ) =>
							setAttributes( { categoryIds: nextTerms } )
						}
					/>
					<ToggleControl
						label={ __( 'Verified reviews only', 'happywood' ) }
						checked={ verifiedOnly }
						onChange={ ( value ) =>
							setAttributes( { verifiedOnly: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Card content', 'happywood' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show summary', 'happywood' ) }
						checked={ showSummary }
						onChange={ ( value ) =>
							setAttributes( { showSummary: value } )
						}
						help={ __(
							'Shows aggregate rating and verified review count in the slider header.',
							'happywood',
						) }
					/>
					<ToggleControl
						label={ __( 'Product rating', 'happywood' ) }
						checked={ showReviewRating }
						onChange={ ( value ) =>
							setAttributes( { showReviewRating: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Reviewer name', 'happywood' ) }
						checked={ showReviewerName }
						onChange={ ( value ) =>
							setAttributes( { showReviewerName: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Review content', 'happywood' ) }
						checked={ showReviewContent }
						onChange={ ( value ) =>
							setAttributes( { showReviewContent: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Product name', 'happywood' ) }
						checked={ showProductName }
						onChange={ ( value ) =>
							setAttributes( { showProductName: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Review date', 'happywood' ) }
						checked={ showReviewDate }
						onChange={ ( value ) =>
							setAttributes( { showReviewDate: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Slider display', 'happywood' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show arrows on desktop', 'happywood' ) }
						checked={ showArrowsDesktop }
						onChange={ ( value ) =>
							setAttributes( { showArrowsDesktop: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show arrows on mobile', 'happywood' ) }
						checked={ showArrowsMobile }
						onChange={ ( value ) =>
							setAttributes( { showArrowsMobile: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show progress bar', 'happywood' ) }
						checked={ showProgressBar }
						onChange={ ( value ) =>
							setAttributes( { showProgressBar: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						attributes={ previewAttributes }
					/>
				</Disabled>
			</div>
		</>
	);
}
