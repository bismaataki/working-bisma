import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { reviewsPerPage, displayReviewsSummary } = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-product-reviews hw-product-reviews-editor',
	} );

	const listColumn = (
		<div className="hw-product-reviews__inner">
			<h2 className="hw-product-reviews__title">
				{ __( '203 Reviews', 'happywood' ) }
			</h2>
			<ol className="hw-product-reviews__list" aria-hidden="true">
				<li className="hw-product-reviews__item">
					<div className="hw-product-reviews__item-header">
						<span className="hw-product-reviews__author-name">
							{ __( 'Sarah M.', 'happywood' ) }
						</span>
						<span className="hw-product-reviews__date">
							{ __( 'March 15, 2026', 'happywood' ) }
						</span>
					</div>
					<p className="hw-product-reviews__content">
						{ __(
							'Review cards render from WooCommerce product reviews on the frontend.',
							'happywood',
						) }
					</p>
				</li>
			</ol>
			{ ! displayReviewsSummary && (
				<p className="hw-product-reviews-editor__cta-note">
					{ __(
						'Write a Review button renders here when summary is off.',
						'happywood',
					) }
				</p>
			) }
		</div>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Reviews', 'happywood' ) }>
					<ToggleControl
						label={ __( 'Display reviews summary', 'happywood' ) }
						help={ __(
							'When on, shows average rating and histogram beside the list. When off, a Write a Review button appears in this column instead.',
							'happywood',
						) }
						checked={ displayReviewsSummary }
						onChange={ ( value ) =>
							setAttributes( { displayReviewsSummary: value } )
						}
					/>
					<RangeControl
						label={ __( 'Reviews per page', 'happywood' ) }
						value={ reviewsPerPage }
						onChange={ ( value ) =>
							setAttributes( { reviewsPerPage: value } )
						}
						min={ 1 }
						max={ 20 }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps } id="reviews">
				{ displayReviewsSummary ? (
					<div className="wp-block-columns alignwide">
						<div
							className="wp-block-column"
							style={ { flexBasis: '25%' } }
						>
							<p className="hw-product-reviews-summary-editor">
								{ __(
									'Summary column (average + histogram + Write a Review)',
									'happywood',
								) }
							</p>
						</div>
						<div className="wp-block-column">{ listColumn }</div>
					</div>
				) : (
					<div className="wp-block-columns alignwide">
						<div className="wp-block-column">{ listColumn }</div>
					</div>
				) }
			</div>
		</>
	);
}
