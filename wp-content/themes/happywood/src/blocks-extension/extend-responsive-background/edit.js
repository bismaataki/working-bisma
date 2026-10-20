import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { isResponsiveBackgroundBlock } from './helpers';

export const withResponsiveBackgroundControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( ! isResponsiveBackgroundBlock( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const {
			hwHideBackgroundOnSmall,
			hwHideBackgroundOnMedium,
			hwHideBackgroundOnLarge,
		} = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Responsive background', 'happywood' ) }
						initialOpen={ false }
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide background on small', 'happywood' ) }
							help={ __(
								'Removes the background below the medium breakpoint (781px).',
								'happywood',
							) }
							checked={ !! hwHideBackgroundOnSmall }
							onChange={ ( value ) =>
								setAttributes( {
									hwHideBackgroundOnSmall: value,
								} )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Hide background on medium',
								'happywood',
							) }
							help={ __(
								'Removes the background from 781px up to the large breakpoint (1024px).',
								'happywood',
							) }
							checked={ !! hwHideBackgroundOnMedium }
							onChange={ ( value ) =>
								setAttributes( {
									hwHideBackgroundOnMedium: value,
								} )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide background on large', 'happywood' ) }
							help={ __(
								'Removes the background from the large breakpoint (1024px) and up.',
								'happywood',
							) }
							checked={ !! hwHideBackgroundOnLarge }
							onChange={ ( value ) =>
								setAttributes( {
									hwHideBackgroundOnLarge: value,
								} )
							}
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	},
	'withResponsiveBackgroundControls',
);
