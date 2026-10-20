import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	PanelBody,
	SelectControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import ShowroomPicker from './showroom-picker';
import {
	getShowroomCardCtaOptions,
	isValidSelectedShowroom,
} from './showroom-utils';
import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		showrooms = [],
		ctaType = 'directions',
		showImage = false,
		showAddress = true,
		showTime = true,
		showContact = true,
	} = attributes;

	const selectedShowrooms = Array.isArray( showrooms )
		? showrooms.filter( isValidSelectedShowroom )
		: [];

	const blockProps = useBlockProps( {
		className: 'sk-showroom-contact-details-editor',
	} );

	const previewAttributes = {
		showrooms: selectedShowrooms,
		ctaType,
		showImage,
		showAddress,
		showTime,
		showContact,
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Showrooms', 'skaflow' ) }
					initialOpen={ true }
				>
					<ShowroomPicker
						showrooms={ selectedShowrooms }
						onChange={ ( nextShowrooms ) =>
							setAttributes( { showrooms: nextShowrooms } )
						}
					/>
				</PanelBody>
				<PanelBody
					title={ __( 'Card display', 'skaflow' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'CTA button', 'skaflow' ) }
						value={ ctaType }
						options={ getShowroomCardCtaOptions() }
						onChange={ ( value ) =>
							setAttributes( { ctaType: value || 'directions' } )
						}
					/>
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
						label={ __( 'Show contact details', 'skaflow' ) }
						checked={ showContact }
						onChange={ ( value ) =>
							setAttributes( { showContact: value } )
						}
					/>
				</PanelBody>
			</InspectorControls>

			<div { ...blockProps }>
				<Disabled>
					<ServerSideRender
						block={ metadata.name }
						attributes={ previewAttributes }
						LoadingResponsePlaceholder={ () => (
							<div className="sk-showroom-contact-details-editor__placeholder">
								<Spinner />
								<p>
									{ __(
										'Loading showroom contact details preview…',
										'skaflow',
									) }
								</p>
							</div>
						) }
						ErrorResponsePlaceholder={ ( { response } ) => (
							<div className="sk-showroom-contact-details-editor__placeholder sk-showroom-contact-details-editor__placeholder--error">
								<p>
									{ __(
										'Unable to load the showroom contact details preview.',
										'skaflow',
									) }
								</p>
								{ response?.message ? (
									<p>{ response.message }</p>
								) : null }
							</div>
						) }
					/>
				</Disabled>
			</div>
		</>
	);
}
