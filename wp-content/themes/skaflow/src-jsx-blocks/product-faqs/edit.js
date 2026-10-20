import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, RangeControl, ToggleControl } from '@wordpress/components';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { initialVisibleCount = 5, includeFaqSchema = true } = attributes;

	const blockProps = useBlockProps( {
		className: 'sk-accordion sk-product-faqs-editor',
	} );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'FAQ display', 'skaflow' ) }
					initialOpen={ true }
				>
					<RangeControl
						label={ __( 'FAQs shown before Load More', 'skaflow' ) }
						help={ __(
							'When a product has more FAQs than this number, the rest are hidden until Load More is clicked.',
							'skaflow',
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
						label={ __( 'Include FAQ schema', 'skaflow' ) }
						help={ __(
							'Output FAQPage JSON-LD in the page footer when this block renders FAQs.',
							'skaflow',
						) }
						checked={ includeFaqSchema }
						onChange={ ( value ) =>
							setAttributes( { includeFaqSchema: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps } data-sk-accordion>
				<div className="sk-accordion__list" aria-hidden="true">
					<div className="sk-accordion__item is-open">
						<button
							type="button"
							className="sk-accordion__trigger"
							tabIndex={ -1 }
						>
							<span className="sk-accordion__label">
								{ __(
									'How long does delivery take?',
									'skaflow',
								) }
							</span>
							<span
								className="sk-accordion__icon"
								aria-hidden="true"
							/>
						</button>
						<div
							className="sk-accordion__panel"
							aria-hidden="false"
						>
							<div className="sk-accordion__content">
								<p>
									{ __(
										'FAQ accordion renders from FAQ Configurations and product settings on the frontend.',
										'skaflow',
									) }
								</p>
							</div>
						</div>
					</div>
					<div className="sk-accordion__item">
						<button
							type="button"
							className="sk-accordion__trigger"
							tabIndex={ -1 }
						>
							<span className="sk-accordion__label">
								{ __( 'Is assembly required?', 'skaflow' ) }
							</span>
							<span
								className="sk-accordion__icon"
								aria-hidden="true"
							/>
						</button>
						<div className="sk-accordion__panel" aria-hidden="true">
							<div className="sk-accordion__content">
								<p>{ __( 'Preview answer.', 'skaflow' ) }</p>
							</div>
						</div>
					</div>
				</div>
				<p className="sk-product-faqs-editor__note">
					{ __(
						'Resolves FAQ post IDs for the current product, then renders the shared accordion component.',
						'skaflow',
					) }
				</p>
				<p className="sk-product-faqs-editor__note">
					{ sprintf(
						/* translators: %d: number of FAQs shown before Load More */
						__(
							'Load More shows after %d FAQs on the frontend when more are assigned.',
							'skaflow',
						),
						initialVisibleCount,
					) }
				</p>
			</div>
		</>
	);
}
