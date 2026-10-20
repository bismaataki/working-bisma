import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes } ) {
	const blockProps = useBlockProps( {
		className: 'hw-showroom-locator-full-editor',
	} );

	return (
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					LoadingResponsePlaceholder={ () => (
						<div className="hw-showroom-locator-full-editor__placeholder">
							<Spinner />
							<p>
								{ __(
									'Loading showroom locator preview…',
									'happywood',
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ ( { response } ) => (
						<div className="hw-showroom-locator-full-editor__placeholder hw-showroom-locator-full-editor__placeholder--error">
							<p>
								{ __(
									'Unable to load the showroom locator preview.',
									'happywood',
								) }
							</p>
							{ response?.message ? (
								<p>{ response.message }</p>
							) : null }
						</div>
					) }
					EmptyResponsePlaceholder={ () => (
						<div className="hw-showroom-locator-full-editor__placeholder">
							<p>
								{ __(
									'No showroom locator output was returned.',
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
