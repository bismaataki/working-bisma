import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import { Disabled, Spinner } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import metadata from './block.json';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'hw-verified-reviews-summary-editor',
	} );

	return (
		<div { ...blockProps }>
			<Disabled>
				<ServerSideRender
					block={ metadata.name }
					attributes={ {} }
					LoadingResponsePlaceholder={ () => (
						<div className="hw-verified-reviews-summary-editor__placeholder">
							<Spinner />
							<p>
								{ __(
									'Loading verified reviews summary…',
									'happywood',
								) }
							</p>
						</div>
					) }
					EmptyResponsePlaceholder={ () => (
						<p className="hw-verified-reviews-summary-editor__placeholder">
							{ __(
								'No verified reviews yet — this block will not render on the frontend.',
								'happywood',
							) }
						</p>
					) }
				/>
			</Disabled>
		</div>
	);
}
