import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'sk-product-cat-banner-editor',
	} );

	return (
		<div { ...blockProps }>
			<ServerSideRender block="skaflow/product-cat-banner" />
			<p className="sk-product-cat-banner-editor__note">
				{ __(
					'On product category archives, renders the banner mapped in Banner Configurations. Unmapped categories output nothing on the frontend.',
					'skaflow',
				) }
			</p>
		</div>
	);
}
