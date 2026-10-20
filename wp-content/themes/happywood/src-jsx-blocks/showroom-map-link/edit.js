import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes, context } ) {
	const blockProps = useBlockProps( {
		className: 'hw-showroom-map-link-editor',
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
						<div className="hw-showroom-map-link-editor__placeholder">
							<Spinner />
							<p>
								{ __(
									'Loading showroom map link preview…',
									'happywood',
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ ( { response } ) => (
						<div className="hw-showroom-map-link-editor__placeholder hw-showroom-map-link-editor__placeholder--error">
							<p>
								{ __(
									'Unable to load the showroom block preview.',
									'happywood',
								) }
							</p>
							{ response?.message ? (
								<p>{ response.message }</p>
							) : null }
						</div>
					) }
					EmptyResponsePlaceholder={ () => (
						<div className="hw-showroom-map-link-editor__placeholder">
							<p>
								{ __(
									'No showroom block output was returned.',
									'happywood',
								) }
							</p>
						</div>
					) }
				/>
			</Disabled>
		</div>
	);
}
