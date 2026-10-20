import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { reviewsPerPage, displayReviewsSummary } = attributes;

	const blockProps = useBlockProps( {
		className: 'sk-product-reviews sk-product-reviews-editor',
	} );

	const listColumn = (
		<div className="sk-product-reviews__inner">
			<h2 className="sk-product-reviews__title">
				{ __( '203 Reviews', 'skaflow' ) }
			</h2>
			<ol className="sk-product-reviews__list" aria-hidden="true">
				<li className="sk-product-reviews__item">
					<div className="sk-product-reviews__item-header">
						<span className="sk-product-reviews__author-name">
							{ __( 'Sarah M.', 'skaflow' ) }
						</span>
						<span className="sk-product-reviews__date">
							{ __( 'March 15, 2026', 'skaflow' ) }
						</span>
					</div>
					<p className="sk-product-reviews__content">
						{ __(
							'Review cards render from WooCommerce product reviews on the frontend.',
							'skaflow',
						) }
					</p>
				</li>
			</ol>
			{ ! displayReviewsSummary && (
				<p className="sk-product-reviews-editor__cta-note">
					{ __(
						'Write a Review button renders here when summary is off.',
						'skaflow',
					) }
				</p>
			) }
		</div>
	);

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Reviews', 'skaflow' ) }>
					<ToggleControl
						label={ __( 'Display reviews summary', 'skaflow' ) }
						help={ __(
							'When on, shows average rating and histogram beside the list. When off, a Write a Review button appears in this column instead.',
							'skaflow',
						) }
						checked={ displayReviewsSummary }
						onChange={ ( value ) =>
							setAttributes( { displayReviewsSummary: value } )
						}
					/>
					<RangeControl
						label={ __( 'Reviews per page', 'skaflow' ) }
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
							<p className="sk-product-reviews-summary-editor">
								{ __(
									'Summary column (average + histogram + Write a Review)',
									'skaflow',
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
