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
		<div className="sk-cart-location">
			<span
				className="sk-cart-location__icon sk-icon-preview sk-icon-map-pin"
				aria-hidden="true"
			/>
			<div className="sk-cart-location__body">
				{ address && (
					<p className="sk-cart-location__address">{ address }</p>
				) }
				{ details && (
					<p className="sk-cart-location__details">{ details }</p>
				) }
			</div>
		</div>
	);
};

const CarrierFields = ( { answers, update } ) => (
	<>
		<p className="sk-checkout-questions__intro">
			{ __(
				"Arrange your own shipping? Simply provide your carrier details and we'll do the rest.",
				'skaflow',
			) }
		</p>
		<div className="sk-carrier-fields">
			<TextInput
				id="sk-cart-carrier-name"
				className="sk-carrier-fields__wide"
				label={ __( 'Carrier name', 'skaflow' ) }
				value={ answers.carrier_name }
				required
				onChange={ ( value ) => update( 'carrier_name', value ) }
			/>
			<TextInput
				id="sk-cart-carrier-account"
				label={ __( 'Account Number', 'skaflow' ) }
				value={ answers.carrier_account }
				onChange={ ( value ) => update( 'carrier_account', value ) }
			/>
			<TextInput
				id="sk-cart-carrier-phone"
				type="tel"
				label={ __( 'Phone', 'skaflow' ) }
				value={ answers.carrier_phone }
				onChange={ ( value ) => update( 'carrier_phone', value ) }
			/>
		</div>
		<Question label={ __( 'Pickup notes', 'skaflow' ) }>
			<Textarea
				value={ answers.carrier_note }
				onTextChange={ ( value ) => update( 'carrier_note', value ) }
				placeholder={ __(
					'Enter special pick-up notes here…',
					'skaflow',
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
						'skaflow',
					)
					: __( 'Select a showroom or warehouse first.', 'skaflow' ),
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
						'skaflow',
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
						'skaflow',
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
			title={ __( 'Select Showroom/Warehouse', 'skaflow' ) }
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
							? __( 'Saving…', 'skaflow' )
							: __( 'Continue', 'skaflow' ) }
					</ConfirmButton>
				</>
			}
		>
			<Question label={ __( 'Warehouse', 'skaflow' ) }>
				<select
					id="sk-cart-warehouse"
					className="sk-cart-popup__select"
					value={ rateId }
					onChange={ ( event ) => setRateId( event.target.value ) }
				>
					<option value="">
						{ __( 'Select Warehouse', 'skaflow' ) }
					</option>
					{ pickupLocations.map( ( location ) => (
						<option key={ location.rate_id } value={ location.rate_id }>
							{ location.name }
						</option>
					) ) }
				</select>
			</Question>
			<PickupLocationDetails location={ chosen } />
			<Question label={ __( 'Who is picking up the items', 'skaflow' ) }>
				<p className="sk-checkout-questions__intro">
					{ __(
						"Unless you pick them up in person you can provide details and we'll let the warehouse know.",
						'skaflow',
					) }
				</p>
				<RadioControlAccordion
					id="sk-cart-collected-by"
					highlightChecked
					selected={ answers.collected_by }
					onChange={ ( value ) => updateAnswer( 'collected_by', value ) }
					options={ [
						{
							value: 'myself',
							label: __( 'Myself', 'skaflow' ),
						},
						{
							value: 'carrier',
							label: __( 'My Custom Carrier', 'skaflow' ),
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
			{ error && <p className="sk-cart-popup__error">{ error }</p> }
		</Popup>
	);
};
