/**
 * Cart chooser — warehouse / carrier pickup popup.
 */

import { useEffect, useState } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	CART_STORE_KEY,
	RadioControlAccordion,
	TextInput,
	Textarea,
	formatAddressLine,
	getStoredAnswers,
	hasCompletePickupPath,
	localizedAnswers,
	pickupLocations,
	pushCartAnswers,
} from './data';
import { BackButton, ConfirmButton, Popup, Question } from './ui';

/**
 * Selected warehouse: pin + address, details on the next line.
 *
 * @param {Object} props
 * @param {Object} props.location Pickup location from PHP.
 *
 * @return {Element|null} The location block, or nothing.
 */
const PickupLocationDetails = ( { location } ) => {
	if ( ! location ) {
		return null;
	}

	const address = formatAddressLine( location.address );
	const details = location.details;

	if ( ! address && ! details ) {
		return null;
	}

	return (
		<div className="hw-cart-location">
			<span
				className="hw-cart-location__icon hw-icon-preview hw-icon-map-pin"
				aria-hidden="true"
			/>
			<div className="hw-cart-location__body">
				{ address && (
					<p className="hw-cart-location__address">{ address }</p>
				) }
				{ details && (
					<p className="hw-cart-location__details">{ details }</p>
				) }
			</div>
		</div>
	);
};

const CarrierFields = ( { answers, update } ) => (
	<>
		<p className="hw-checkout-questions__intro">
			{ __(
				"Arrange your own shipping? Simply provide your carrier details and we'll do the rest.",
				'happywood',
			) }
		</p>
		<div className="hw-carrier-fields">
			<TextInput
				id="hw-cart-carrier-name"
				className="hw-carrier-fields__wide"
				label={ __( 'Carrier name', 'happywood' ) }
				value={ answers.carrier_name }
				required
				onChange={ ( value ) => update( 'carrier_name', value ) }
			/>
			<TextInput
				id="hw-cart-carrier-account"
				label={ __( 'Account Number', 'happywood' ) }
				value={ answers.carrier_account }
				onChange={ ( value ) => update( 'carrier_account', value ) }
			/>
			<TextInput
				id="hw-cart-carrier-phone"
				type="tel"
				label={ __( 'Phone', 'happywood' ) }
				value={ answers.carrier_phone }
				onChange={ ( value ) => update( 'carrier_phone', value ) }
			/>
		</div>
		<Question label={ __( 'Pickup notes', 'happywood' ) }>
			<Textarea
				value={ answers.carrier_note }
				onTextChange={ ( value ) => update( 'carrier_note', value ) }
				placeholder={ __(
					'Enter special pick-up notes here…',
					'happywood',
				) }
			/>
		</Question>
	</>
);

export const PickupPopup = ( { open, onClose, selectedRateId } ) => {
	const [ rateId, setRateId ] = useState( selectedRateId || '' );
	const [ answers, setAnswers ] = useState( {
		collected_by: localizedAnswers.collected_by || 'myself',
		carrier_name: localizedAnswers.carrier_name || '',
		carrier_account: localizedAnswers.carrier_account || '',
		carrier_phone: localizedAnswers.carrier_phone || '',
		carrier_note: localizedAnswers.carrier_note || '',
	} );
	const [ busy, setBusy ] = useState( false );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		if ( ! open ) {
			return;
		}

		const stored = getStoredAnswers();

		setRateId( selectedRateId || '' );
		setAnswers( {
			collected_by: stored.collected_by || 'myself',
			carrier_name: stored.carrier_name || '',
			carrier_account: stored.carrier_account || '',
			carrier_phone: stored.carrier_phone || '',
			carrier_note: stored.carrier_note || '',
		} );
		setError( '' );
	}, [ open, selectedRateId ] );

	const updateAnswer = ( key, value ) => {
		setAnswers( ( previous ) => ( { ...previous, [ key ]: value } ) );
	};

	const chosen = pickupLocations.find(
		( location ) => location.rate_id === rateId,
	);

	const confirm = async () => {
		if ( ! hasCompletePickupPath( rateId, answers ) ) {
			setError(
				rateId
					? __(
						'Enter the carrier name before continuing.',
						'happywood',
					)
					: __( 'Select a pickup location first.', 'happywood' ),
			);
			return;
		}

		setBusy( true );
		setError( '' );

		try {
			const packages = select( CART_STORE_KEY ).getShippingRates();
			const firstPackage = packages?.[ 0 ];

			if ( ! firstPackage ) {
				throw new Error(
					__(
						'The cart has no shipping package yet. Add an item and try again.',
						'happywood',
					),
				);
			}

			await dispatch( CART_STORE_KEY ).selectShippingRate(
				rateId,
				firstPackage.package_id,
			);
			await pushCartAnswers( answers );
			onClose();
		} catch ( thrown ) {
			setError(
				thrown?.message ||
					__(
						'Could not select that warehouse. Try again, or pick it at checkout.',
						'happywood',
					),
			);
		} finally {
			setBusy( false );
		}
	};

	return (
		<Popup
			open={ open }
			onClose={ onClose }
			title={ __( 'Select Showroom/Warehouse', 'happywood' ) }
			actions={
				<>
					<BackButton onClick={ onClose } />
					<ConfirmButton
						disabled={
							busy || ! hasCompletePickupPath( rateId, answers )
						}
						onClick={ confirm }
					>
						{ busy
							? __( 'Saving…', 'happywood' )
							: __( 'Continue', 'happywood' ) }
					</ConfirmButton>
				</>
			}
		>
			<Question label={ __( 'Warehouse', 'happywood' ) }>
				<select
					id="hw-cart-warehouse"
					className="hw-cart-popup__select"
					value={ rateId }
					onChange={ ( event ) => setRateId( event.target.value ) }
				>
					<option value="">
						{ __( 'Select Warehouse', 'happywood' ) }
					</option>
					{ pickupLocations.map( ( location ) => (
						<option key={ location.rate_id } value={ location.rate_id }>
							{ location.name }
						</option>
					) ) }
				</select>
			</Question>
			<PickupLocationDetails location={ chosen } />
			<Question label={ __( 'Who is picking up the items', 'happywood' ) }>
				<p className="hw-checkout-questions__intro">
					{ __(
						"Unless you pick them up in person you can provide details and we'll let the warehouse know.",
						'happywood',
					) }
				</p>
				<RadioControlAccordion
					id="hw-cart-collected-by"
					highlightChecked
					selected={ answers.collected_by }
					onChange={ ( value ) => updateAnswer( 'collected_by', value ) }
					options={ [
						{
							value: 'myself',
							label: __( 'Myself', 'happywood' ),
						},
						{
							value: 'carrier',
							label: __( 'My Custom Carrier', 'happywood' ),
							content: (
								<CarrierFields
									answers={ answers }
									update={ updateAnswer }
								/>
							),
						},
					] }
				/>
			</Question>
			{ error && <p className="hw-cart-popup__error">{ error }</p> }
		</Popup>
	);
};
