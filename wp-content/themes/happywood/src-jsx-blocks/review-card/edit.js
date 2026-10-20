import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit( { attributes } ) {
	const { commentId = 0 } = attributes;

	const blockProps = useBlockProps( {
		className: 'hw-review-card-editor',
	} );

	const urlQueryArgs =
		commentId > 0 ? { comment_id: Number( commentId ) } : undefined;

	return (
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ metadata.name }
					attributes={ attributes }
					urlQueryArgs={ urlQueryArgs }
					LoadingResponsePlaceholder={ () => (
						<div className="hw-review-card-editor__placeholder">
							<Spinner />
							<p>
								{ __(
									'Loading review card preview…',
									'happywood',
								) }
							</p>
						</div>
					) }
					ErrorResponsePlaceholder={ () => (
						<div className="hw-review-card-editor__placeholder hw-review-card-editor__placeholder--error">
							<p>
								{ __(
									'Unable to load the review card preview.',
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
