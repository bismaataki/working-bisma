import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'sk-order-confirmation-placeholder',
	} );

	return (
		<div { ...blockProps }>
			<p>
				<strong>
					{ __( 'Delivery / Pickup Information', 'skaflow' ) }
				</strong>
				{ ' — ' }
				{ __(
					'Shows the WooCommerce address or warehouse, payment, method, and the order answers.',
					'skaflow',
				) }
			</p>
		</div>
	);
}
