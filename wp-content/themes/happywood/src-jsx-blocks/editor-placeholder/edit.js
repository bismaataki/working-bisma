import { useBlockProps } from '@wordpress/block-editor';

import EditorPlaceholderNotice from './notice';
import { getEditorPlaceholderLinks } from './utils';

export default function Edit( { attributes } ) {
	const { title, description, links } = attributes;
	const blockProps = useBlockProps( {
		className: 'hw-editor-placeholder',
	} );
	const displayLinks = getEditorPlaceholderLinks( links );

	return (
		<div { ...blockProps }>
			<EditorPlaceholderNotice
				title={ title }
				description={ description }
				displayLinks={ displayLinks }
			/>
		</div>
	);
}
