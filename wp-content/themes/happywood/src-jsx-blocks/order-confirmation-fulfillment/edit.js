import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'hw-order-confirmation-placeholder',
	} );

	return (
		<div { ...blockProps }>
			<p>
				<strong>
					{ __( 'Delivery / Pickup Information', 'happywood' ) }
				</strong>
				{ ' — ' }
				{ __(
					'Shows the WooCommerce address or warehouse, payment, method, and the order answers.',
					'happywood',
				) }
			</p>
		</div>
	);
}
