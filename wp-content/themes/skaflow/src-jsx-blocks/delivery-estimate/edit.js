import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

export default function Edit() {
	const blockProps = useBlockProps( {
		className: 'sk-delivery-estimate sk-delivery-estimate-editor',
	} );

	return (
		<div { ...blockProps }>
			<div className="sk-delivery-estimate__inner">
				<div className="sk-delivery-estimate__header">
					<span
						className="sk-delivery-estimate__header-icon"
						aria-hidden="true"
					/>
					<span className="sk-delivery-estimate__title">
						{ __( 'Delivery Estimate', 'skaflow' ) }
					</span>
				</div>
				<div className="sk-delivery-estimate__fields">
					<input
						type="text"
						className="sk-delivery-estimate__input sk-delivery-estimate__input--postcode"
						placeholder={ __( 'Enter postcode', 'skaflow' ) }
						disabled
					/>
					<input
						type="text"
						className="sk-delivery-estimate__input sk-delivery-estimate__input--quantity"
						placeholder={ __( 'Qty', 'skaflow' ) }
						disabled
					/>
					<button
						type="button"
						className="sk-delivery-estimate__submit"
						disabled
					>
						{ __( 'Check', 'skaflow' ) }
					</button>
				</div>
			</div>
			<p className="sk-delivery-estimate-editor__note">
				{ __(
					'Looks up freight for the current product on the frontend. Renders a price, or the quote fallback when the postcode has no automatic rate.',
					'skaflow',
				) }
			</p>
		</div>
	);
}
