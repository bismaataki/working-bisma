import { __ } from '@wordpress/i18n';
import { useBlockProps } from '@wordpress/block-editor';

import './editor.scss';

const PLACEHOLDER_BARS = [ 5, 4, 3, 2, 1 ];

export default function Edit() {
	const blockProps = useBlockProps( {
		className:
			'sk-product-reviews-summary sk-product-reviews-summary-editor',
	} );

	return (
		<div { ...blockProps }>
			<div className="sk-product-reviews-summary__inner">
				<div className="sk-product-reviews-summary__score-row">
					<span className="sk-product-reviews-summary__average">
						4.9
					</span>
					<span
						className="sk-product-reviews-summary__stars-placeholder"
						aria-hidden="true"
					/>
				</div>
				<p className="sk-product-reviews-summary__count">
					{ __( 'Based on 203 reviews', 'skaflow' ) }
				</p>
				<ul
					className="sk-product-reviews-summary__bars"
					aria-hidden="true"
				>
					{ PLACEHOLDER_BARS.map( ( star ) => (
						<li
							key={ star }
							className="sk-product-reviews-summary__bar-row"
						>
							<span className="sk-product-reviews-summary__bar-label">
								{ star } { __( 'stars', 'skaflow' ) }
							</span>
							<span className="sk-product-reviews-summary__bar-track">
								<span className="sk-product-reviews-summary__bar-fill" />
							</span>
							<span className="sk-product-reviews-summary__bar-count">
								0
							</span>
						</li>
					) ) }
				</ul>
			</div>
			<p className="sk-product-reviews-summary-editor__note">
				{ __(
					'Rating summary renders from WooCommerce product reviews on the frontend.',
					'skaflow',
				) }
			</p>
		</div>
	);
}
