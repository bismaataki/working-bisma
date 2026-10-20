import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes, context } ) {
	const blockProps = useBlockProps( {
		className: 'sk-showroom-location-details-editor',
	} );

	const urlQueryArgs = context?.postId
		? { post_id: Number( context.postId ) }
		: undefined;

	return (
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
					LoadingResponsePlaceholder={ () => (
						<div className="sk-showroom-location-details-editor__placeholder">
							<Spinner />
							<p>
								{ __(
									'Loading showroom location details preview…',
									'skaflow',
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ ( { response } ) => (
						<div className="sk-showroom-location-details-editor__placeholder sk-showroom-location-details-editor__placeholder--error">
							<p>
								{ __(
									'Unable to load the showroom block preview.',
									'skaflow',
								) }
							</p>
							{ response?.message ? (
								<p>{ response.message }</p>
							) : null }
						</div>
					) }
					EmptyResponsePlaceholder={ () => (
						<div className="sk-showroom-location-details-editor__placeholder">
							<p>
								{ __(
									'No showroom block output was returned.',
									'skaflow',
								) }
							</p>
						</div>
					) }
				/>
			</Disabled>
		</div>
	);
}
