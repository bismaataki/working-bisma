import { useBlockProps } from '@wordpress/block-editor';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes } ) {
	const blockProps = useBlockProps( {
		className: 'hw-testimonials-editor',
	} );

	return (
		<div { ...blockProps }>
			<ServerSideRender
				block="happywood/testimonials"
				attributes={ attributes }
			/>
		</div>
	);
}
