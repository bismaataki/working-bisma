import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export const columnsWithInspectorControls = createHigherOrderComponent(
	( BlockEdit ) => ( props ) => {
		if ( props.name !== 'core/columns' ) {
			return <BlockEdit { ...props } />;
		}

		const { attributes, setAttributes } = props;
		const { isStackedOnTablet, isPhoneReversed } = attributes;

		return (
			<>
				<BlockEdit { ...props } />
				<InspectorControls>
					<PanelBody
						title={ __( 'Skaflow layout', 'skaflow' ) }
						initialOpen={ false }
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Stack on tablet', 'skaflow' ) }
							help={ __(
								'Keep columns stacked until the large breakpoint (1024px).',
								'skaflow',
							) }
							checked={ !! isStackedOnTablet }
							onChange={ ( value ) =>
								setAttributes( { isStackedOnTablet: value } )
							}
						/>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __(
								'Reverse columns on mobile',
								'skaflow',
							) }
							help={ __(
								'Flip column order on small screens.',
								'skaflow',
							) }
							checked={ !! isPhoneReversed }
							onChange={ ( value ) =>
								setAttributes( { isPhoneReversed: value } )
							}
						/>
					</PanelBody>
				</InspectorControls>
			</>
		);
	},
	'columnsWithInspectorControls',
);
