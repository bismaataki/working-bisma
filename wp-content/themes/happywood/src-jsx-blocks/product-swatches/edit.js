import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'hw-product-swatches-editor',
	} );

	return (
		<div { ...blockProps }>
			<p>{ __( 'Product swatches', 'happywood' ) }</p>
			<p className="hw-product-swatches-editor__hint">
				{ __(
					'Rendered dynamically for each variable product in the grid.',
					'happywood',
				) }
			</p>
		</div>
	);
}
