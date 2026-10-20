import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { initialVisibleCount = 5, includeFaqSchema = true } = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-accordion hw-product-faqs-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'FAQ display', 'happywood' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'FAQs shown before Load More', 'happywood' ) }
						help={ __(
							'When a product has more FAQs than this number, the rest are hidden until Load More is clicked.',
							'happywood',
						) }
						value={ initialVisibleCount }
						onChange={ ( value ) =>
							setAttributes( {
								initialVisibleCount: value ?? 5,
							} )
						}
						min={ 1 }
						max={ 20 }
					/>
					<ToggleControl
						label={ __( 'Include FAQ schema', 'happywood' ) }
						help={ __(
							'Output FAQPage JSON-LD in the page footer when this block renders FAQs.',
							'happywood',
						) }
						checked={ includeFaqSchema }
						onChange={ ( value ) =>
							setAttributes( { includeFaqSchema: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps } data-hw-accordion>
				<div className="hw-accordion__list" aria-hidden="true">
					<div className="hw-accordion__item is-open">
						<button
							type="button"
							className="hw-accordion__trigger"
							tabIndex={ -1 }
						>
							<span className="hw-accordion__label">
								{ __(
									'How long does delivery take?',
									'happywood',
								) }
							</span>
							<span
								className="hw-accordion__icon"
								aria-hidden="true"
							/>
						</button>
						<div
							className="hw-accordion__panel"
							aria-hidden="false"
						>
							<div className="hw-accordion__content">
								<p>
									{ __(
										'FAQ accordion renders from FAQ Configurations and product settings on the frontend.',
										'happywood',
									) }
								</p>
							</div>
						</div>
					</div>
					<div className="hw-accordion__item">
						<button
							type="button"
							className="hw-accordion__trigger"
							tabIndex={ -1 }
						>
							<span className="hw-accordion__label">
								{ __( 'Is assembly required?', 'happywood' ) }
							</span>
							<span
								className="hw-accordion__icon"
								aria-hidden="true"
							/>
						</button>
						<div className="hw-accordion__panel" aria-hidden="true">
							<div className="hw-accordion__content">
								<p>{ __( 'Preview answer.', 'happywood' ) }</p>
							</div>
						</div>
					</div>
				</div>
				<p className="hw-product-faqs-editor__note">
					{ __(
						'Resolves FAQ post IDs for the current product, then renders the shared accordion component.',
						'happywood',
					) }
				</p>
				<p className="hw-product-faqs-editor__note">
					{ sprintf(
						/* translators: %d: number of FAQs shown before Load More */
						__(
							'Load More shows after %d FAQs on the frontend when more are assigned.',
							'happywood',
						),
						initialVisibleCount,
					) }
				</p>
			</div>
		</>
	);
}
