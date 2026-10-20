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
			skHideBackgroundOnSmall,
			skHideBackgroundOnMedium,
			skHideBackgroundOnLarge,
		} = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Responsive background', 'skaflow' ) }
						initialOpen={ false }
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide background on small', 'skaflow' ) }
							help={ __(
								'Removes the background below the medium breakpoint (781px).',
								'skaflow',
							) }
							checked={ !! skHideBackgroundOnSmall }
							onChange={ ( value ) =>
								setAttributes( {
									skHideBackgroundOnSmall: value,
								} )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Hide background on medium',
								'skaflow',
							) }
							help={ __(
								'Removes the background from 781px up to the large breakpoint (1024px).',
								'skaflow',
							) }
							checked={ !! skHideBackgroundOnMedium }
							onChange={ ( value ) =>
								setAttributes( {
									skHideBackgroundOnMedium: value,
								} )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Hide background on large', 'skaflow' ) }
							help={ __(
								'Removes the background from the large breakpoint (1024px) and up.',
								'skaflow',
							) }
							checked={ !! skHideBackgroundOnLarge }
							onChange={ ( value ) =>
								setAttributes( {
									skHideBackgroundOnLarge: value,
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
