import { useBlockProps, InnerBlocks } from '@wordpress/block-editor';

const ALLOWED_BLOCKS = [ 'skaflow/tag' ];

const TEMPLATE = [ [ 'skaflow/tag' ] ];

export default function Edit() {
	const blockProps = useBlockProps();

	return (
		<div { ...blockProps }>
			<InnerBlocks
				allowedBlocks={ ALLOWED_BLOCKS }
				template={ TEMPLATE }
				templateLock={ false }
			/>
		</div>
	);
}
