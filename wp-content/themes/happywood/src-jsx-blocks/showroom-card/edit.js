import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	BaseControl,
	Button,
	ButtonGroup,
	Disabled,
	PanelBody,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes, setAttributes, context } ) {
	const { ctaType, showImage, showAddress, showTime, showContact } =
		attributes;

	const blockProps = useBlockProps( {
		className: 'hw-showroom-card-editor',
	} );

	// ServerSideRender REST preview does not forward block context reliably.
	// Pass the loop showroom post explicitly when available.
	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Card content', 'happywood' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show image', 'happywood' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show address', 'happywood' ) }
						checked={ showAddress }
						onChange={ ( value ) =>
							setAttributes( { showAddress: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show opening hours', 'happywood' ) }
						checked={ showTime }
						onChange={ ( value ) => setAttributes( { showTime: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show phone and email', 'happywood' ) }
						checked={ showContact }
						onChange={ ( value ) =>
							setAttributes( { showContact: value } )
						}
					/>
					<BaseControl
						className="hw-showroom-card-editor__cta-control"
						label={ __( 'CTA button', 'happywood' ) }
						id="hw-showroom-card-cta-type"
						help={ __(
							'Get Directions opens Google Maps for this showroom address. Learn More links to the showroom page.',
							'happywood',
						) }
					>
						<ButtonGroup id="hw-showroom-card-cta-type">
							<Button
								variant={
									ctaType === 'directions'
										? 'primary'
										: 'secondary'
								}
								onClick={ () =>
									setAttributes( { ctaType: 'directions' } )
								}
							>
								{ __( 'Get Directions', 'happywood' ) }
							</Button>
							<Button
								variant={
									ctaType === 'learn-more'
										? 'primary'
										: 'secondary'
								}
								onClick={ () =>
									setAttributes( { ctaType: 'learn-more' } )
								}
							>
								{ __( 'Learn More', 'happywood' ) }
							</Button>
						</ButtonGroup>
					</BaseControl>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						urlQueryArgs={ urlQueryArgs }
						LoadingResponsePlaceholder={ () => (
							<div className="hw-showroom-card-editor__placeholder">
								<Spinner />
								<p>
									{ __(
										'Loading showroom card preview…',
										'happywood',
									) }
								</p>
							</div>
						) }
						ErrorResponsePlaceholder={ ( { response } ) => (
							<div className="hw-showroom-card-editor__placeholder hw-showroom-card-editor__placeholder--error">
								<p>
									{ __(
										'Unable to load the showroom card preview.',
										'happywood',
									) }
								</p>
								{ response?.message ? (
									<p>{ response.message }</p>
								) : null }
							</div>
						) }
						EmptyResponsePlaceholder={ () => (
							<div className="hw-showroom-card-editor__placeholder">
								<p>
									{ __(
										'No showroom card output was returned.',
										'happywood',
									) }
								</p>
							</div>
						) }
					/>
				</Disabled>
			</div>
		</>
	);
}
