import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'sk-quick-view-button-editor',
	} );

	return (
		<div { ...blockProps }>
			<button type="button" className="sk-quick-view-trigger" disabled>
				<span className="screen-reader-text">
					{ __( 'Quick view', 'skaflow' ) }
				</span>
			</button>
		</div>
	);
}
