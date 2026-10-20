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
		className: 'sk-showroom-card-editor',
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
					title={ __( 'Card content', 'skaflow' ) }
					initialOpen={ true }
				>
					<ToggleControl
						label={ __( 'Show image', 'skaflow' ) }
						checked={ showImage }
						onChange={ ( value ) =>
							setAttributes( { showImage: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show address', 'skaflow' ) }
						checked={ showAddress }
						onChange={ ( value ) =>
							setAttributes( { showAddress: value } )
						}
					/>
					<ToggleControl
						label={ __( 'Show opening hours', 'skaflow' ) }
						checked={ showTime }
						onChange={ ( value ) => setAttributes( { showTime: value } ) }
					/>
					<ToggleControl
						label={ __( 'Show phone and email', 'skaflow' ) }
						checked={ showContact }
						onChange={ ( value ) =>
							setAttributes( { showContact: value } )
						}
					/>
					<BaseControl
						className="sk-showroom-card-editor__cta-control"
						label={ __( 'CTA button', 'skaflow' ) }
						id="sk-showroom-card-cta-type"
						help={ __(
							'Get Directions opens Google Maps for this showroom address. Learn More links to the showroom page.',
							'skaflow',
						) }
					>
						<ButtonGroup id="sk-showroom-card-cta-type">
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
								{ __( 'Get Directions', 'skaflow' ) }
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
								{ __( 'Learn More', 'skaflow' ) }
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
							<div className="sk-showroom-card-editor__placeholder">
								<Spinner />
								<p>
									{ __(
										'Loading showroom card preview…',
										'skaflow',
									) }
								</p>
							</div>
						) }
						ErrorResponsePlaceholder={ ( { response } ) => (
							<div className="sk-showroom-card-editor__placeholder sk-showroom-card-editor__placeholder--error">
								<p>
									{ __(
										'Unable to load the showroom card preview.',
										'skaflow',
									) }
								</p>
								{ response?.message ? (
									<p>{ response.message }</p>
								) : null }
							</div>
						) }
						EmptyResponsePlaceholder={ () => (
							<div className="sk-showroom-card-editor__placeholder">
								<p>
									{ __(
										'No showroom card output was returned.',
										'skaflow',
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
