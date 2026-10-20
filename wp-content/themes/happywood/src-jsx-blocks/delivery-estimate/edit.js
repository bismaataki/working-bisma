import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'hw-delivery-estimate hw-delivery-estimate-editor',
	} );

	return (
		<div { ...blockProps }>
			<div className="hw-delivery-estimate__inner">
				<div className="hw-delivery-estimate__header">
					<span
						className="hw-delivery-estimate__header-icon"
						aria-hidden="true"
					/>
					<span className="hw-delivery-estimate__title">
						{ __( 'Delivery Estimate', 'happywood' ) }
					</span>
				</div>
				<div className="hw-delivery-estimate__fields">
					<input
						type="text"
						className="hw-delivery-estimate__input hw-delivery-estimate__input--postcode"
						placeholder={ __( 'Enter postcode', 'happywood' ) }
						disabled
					/>
					<input
						type="text"
						className="hw-delivery-estimate__input hw-delivery-estimate__input--quantity"
						placeholder={ __( 'Qty', 'happywood' ) }
						disabled
					/>
					<button
						type="button"
						className="hw-delivery-estimate__submit"
						disabled
					>
						{ __( 'Check', 'happywood' ) }
					</button>
				</div>
			</div>
			<p className="hw-delivery-estimate-editor__note">
				{ __(
					'Looks up freight for the current product on the frontend. Renders a price, or the quote fallback when the postcode has no automatic rate.',
					'happywood',
				) }
			</p>
		</div>
	);
}
