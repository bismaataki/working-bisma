import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

import './editor.scss';

const PREVIEW_ITEMS = [
	{ label: __( 'Cafe Chairs', 'skaflow' ) },
	{ label: __( 'Office Chairs', 'skaflow' ) },
	{ label: __( 'Dining Chairs', 'skaflow' ) },
	{ label: __( 'Outdoor Chairs', 'skaflow' ) },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		showArrowsDesktop = true,
		showArrowsMobile = false,
		showProgressBar = true,
		showLabels = true,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'sk-category-swiper sk-product-cat-child-swiper-editor',
	} );

	const isCardStyle = blockProps.className?.includes( 'is-style-card' );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Swiper display', 'skaflow' ) }
					initialOpen={ true }
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
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div
					className="sk-category-swiper__viewport"
					data-sk-category-swiper
					aria-hidden="true"
				>
					{ isCardStyle && ( showArrowsDesktop || showArrowsMobile ) && (
						<div className="sk-category-swiper__header">
							<div className="sk-category-swiper__nav">
								<button
									type="button"
									className="sk-category-swiper__arrow sk-category-swiper__arrow--prev"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
								<button
									type="button"
									className="sk-category-swiper__arrow sk-category-swiper__arrow--next"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
							</div>
						</div>
					) }
					<div className="sk-category-swiper__track">
						<div className="sk-category-swiper__swiper">
							<div className="sk-category-swiper__slides">
								{ PREVIEW_ITEMS.map( ( item ) => (
									<div
										key={ item.label }
										className="sk-category-swiper__slide sk-category-swiper__slide--preview"
									>
										<span
											className={
												isCardStyle
													? 'sk-category-swiper__card sk-category-swiper__card--overlay'
													: 'sk-category-swiper__card'
											}
										>
											<span className="sk-category-swiper__media">
												<span className="sk-category-swiper__image sk-category-swiper__image--placeholder" />
												{ isCardStyle && (
													<span className="sk-category-swiper__overlay">
														{ showLabels && (
															<span className="sk-category-swiper__title">
																{ item.label }
															</span>
														) }
														<span className="sk-category-swiper__cta">
															{ __(
																'Shop now',
																'skaflow',
															) }
														</span>
													</span>
												) }
											</span>
											{ ! isCardStyle && showLabels && (
												<span className="sk-category-swiper__label">
													{ item.label }
												</span>
											) }
										</span>
									</div>
								) ) }
							</div>
							{ showProgressBar && (
								<div className="sk-category-swiper__scrollbar sk-category-swiper__scrollbar--preview" />
							) }
						</div>
						{ ! isCardStyle &&
							( showArrowsDesktop || showArrowsMobile ) && (
							<div className="sk-category-swiper__nav">
								<button
									type="button"
									className="sk-category-swiper__arrow sk-category-swiper__arrow--prev"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
								<button
									type="button"
									className="sk-category-swiper__arrow sk-category-swiper__arrow--next"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
							</div>
						) }
					</div>
				</div>
				<p className="sk-product-cat-child-swiper-editor__note">
					{ __(
						'On product category archives, renders immediate child categories with images and links. Leaf categories output nothing.',
						'skaflow',
					) }
				</p>
			</div>
		</>
	);
}
