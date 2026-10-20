import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';

import './editor.scss';

const PREVIEW_ITEMS = [
	{ label: __( 'Cafe Chairs', 'happywood' ) },
	{ label: __( 'Office Chairs', 'happywood' ) },
	{ label: __( 'Dining Chairs', 'happywood' ) },
	{ label: __( 'Outdoor Chairs', 'happywood' ) },
];

export default function Edit( { attributes, setAttributes } ) {
	const {
		showArrowsDesktop = true,
		showArrowsMobile = false,
		showProgressBar = true,
		showLabels = true,
	} = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-category-swiper hw-product-cat-child-swiper-editor',
	} );

	const isCardStyle = blockProps.className?.includes( 'is-style-card' );

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Swiper display', 'happywood' ) }
					initialOpen={ true }
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
					<ToggleControl
						label={ __( 'Show category labels', 'happywood' ) }
						checked={ showLabels }
						onChange={ ( value ) =>
							setAttributes( { showLabels: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				<div
					className="hw-category-swiper__viewport"
					data-hw-category-swiper
					aria-hidden="true"
				>
					{ isCardStyle && ( showArrowsDesktop || showArrowsMobile ) && (
						<div className="hw-category-swiper__header">
							<div className="hw-category-swiper__nav">
								<button
									type="button"
									className="hw-category-swiper__arrow hw-category-swiper__arrow--prev"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
								<button
									type="button"
									className="hw-category-swiper__arrow hw-category-swiper__arrow--next"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
							</div>
						</div>
					) }
					<div className="hw-category-swiper__track">
						<div className="hw-category-swiper__swiper">
							<div className="hw-category-swiper__slides">
								{ PREVIEW_ITEMS.map( ( item ) => (
									<div
										key={ item.label }
										className="hw-category-swiper__slide hw-category-swiper__slide--preview"
									>
										<span
											className={
												isCardStyle
													? 'hw-category-swiper__card hw-category-swiper__card--overlay'
													: 'hw-category-swiper__card'
											}
										>
											<span className="hw-category-swiper__media">
												<span className="hw-category-swiper__image hw-category-swiper__image--placeholder" />
												{ isCardStyle && (
													<span className="hw-category-swiper__overlay">
														{ showLabels && (
															<span className="hw-category-swiper__title">
																{ item.label }
															</span>
														) }
														<span className="hw-category-swiper__cta">
															{ __(
																'Shop now',
																'happywood',
															) }
														</span>
													</span>
												) }
											</span>
											{ ! isCardStyle && showLabels && (
												<span className="hw-category-swiper__label">
													{ item.label }
												</span>
											) }
										</span>
									</div>
								) ) }
							</div>
							{ showProgressBar && (
								<div className="hw-category-swiper__scrollbar hw-category-swiper__scrollbar--preview" />
							) }
						</div>
						{ ! isCardStyle &&
							( showArrowsDesktop || showArrowsMobile ) && (
							<div className="hw-category-swiper__nav">
								<button
									type="button"
									className="hw-category-swiper__arrow hw-category-swiper__arrow--prev"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
								<button
									type="button"
									className="hw-category-swiper__arrow hw-category-swiper__arrow--next"
									tabIndex={ -1 }
									aria-hidden="true"
								/>
							</div>
						) }
					</div>
				</div>
				<p className="hw-product-cat-child-swiper-editor__note">
					{ __(
						'On product category archives, renders immediate child categories with images and links. Leaf categories output nothing.',
						'happywood',
					) }
				</p>
			</div>
		</>
	);
}
