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
						title={ __( 'Happy Wood layout', 'happywood' ) }
						initialOpen={ false }
					>
						<ToggleControl
							__nextHasNoMarginBottom
							label={ __( 'Stack on tablet', 'happywood' ) }
							help={ __(
								'Keep columns stacked until the large breakpoint (1024px).',
								'happywood',
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
								'happywood',
							) }
							help={ __(
								'Flip column order on small screens.',
								'happywood',
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
