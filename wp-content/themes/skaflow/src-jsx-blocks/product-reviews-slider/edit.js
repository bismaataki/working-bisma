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
	{ label: __( 'Highest rating', 'skaflow' ), value: 'rating' },
	{ label: __( 'Most recent', 'skaflow' ), value: 'date' },
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
		className: 'sk-product-reviews-slider-editor',
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
					title={ __( 'Reviews', 'skaflow' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'Reviews to show', 'skaflow' ) }
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
						label={ __( 'Order by', 'skaflow' ) }
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
						label={ __( 'Verified reviews only', 'skaflow' ) }
						checked={ verifiedOnly }
						onChange={ ( value ) =>
							setAttributes( { verifiedOnly: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Card content', 'skaflow' ) }
					initialOpen={ false }
				>
					<ToggleControl
						label={ __( 'Show summary', 'skaflow' ) }
						checked={ showSummary }
						onChange={ ( value ) =>
							setAttributes( { showSummary: value } )
						}
						help={ __(
							'Shows aggregate rating and verified review count in the slider header.',
							'skaflow',
						) }
					/>
					<ToggleControl
						label={ __( 'Product rating', 'skaflow' ) }
						checked={ showReviewRating }
						onChange={ ( value ) =>
							setAttributes( { showReviewRating: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Reviewer name', 'skaflow' ) }
						checked={ showReviewerName }
						onChange={ ( value ) =>
							setAttributes( { showReviewerName: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Review content', 'skaflow' ) }
						checked={ showReviewContent }
						onChange={ ( value ) =>
							setAttributes( { showReviewContent: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Product name', 'skaflow' ) }
						checked={ showProductName }
						onChange={ ( value ) =>
							setAttributes( { showProductName: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Review date', 'skaflow' ) }
						checked={ showReviewDate }
						onChange={ ( value ) =>
							setAttributes( { showReviewDate: value } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Slider display', 'skaflow' ) }
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
