/**
 * Cart chooser — delivery address popup and postcode estimate.
 */

import { useEffect, useState } from '@wordpress/element';
import { dispatch, select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	CART_STORE_KEY,
	RadioControl,
	Textarea,
	emptyAddress,
	estimate,
	formatCardAddress,
	formatEstimatePrice,
	getStoredAnswers,
	hasSavedAddress,
	isLoggedIn,
	isPickupRate,
	localizedAnswers,
	pushCartAnswers,
	resolveShippingCountry,
} from './data';
import { AddressCard, BackButton, ConfirmButton, Popup, Question } from './ui';

/**
 * Rate card or the plugin's quote-only message for the typed postcode.
 *
 * Cart path (`chk_page=2`): the plugin weighs the real cart. Branch on
 * `status`. Unpriced copy is the plugin's `message`; `reason` only picks
 * a class. Confirm stays available either way — a quote is a valid order.
 *
 * @param {Object} props
 * @param {string} props.postcode Shipping postcode from the popup.
 *
 * @return {Element|null} The estimate panel, or null when idle.
 */
const DeliveryEstimate = ( { postcode } ) => {
	const [ result, setResult ] = useState( null );

	useEffect( () => {
		if ( ! estimate.enabled || ! estimate.ajaxUrl ) {
			return undefined;
		}

		const code = ( postcode || '' ).trim();

		if ( ! code ) {
			setResult( null );
			return undefined;
		}

		let cancelled = false;
		const timer = window.setTimeout( async () => {
			setResult( { loading: true } );

			try {
				const response = await fetch( estimate.ajaxUrl, {
					method: 'POST',
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
					},
					body: new URLSearchParams( {
						action: estimate.action || 'cf_postcode_estimate',
						zone_data: code,
						chk_page: '2',
					} ),
				} );
				const payload = await response.json();

				if ( ! cancelled ) {
					setResult( { httpCode: response.status, ...payload } );
				}
			} catch {
				if ( ! cancelled ) {
					setResult( {
						status: 'error',
						message: estimate.failure || '',
					} );
				}
			}
		}, 400 );

		return () => {
			cancelled = true;
			window.clearTimeout( timer );
		};
	}, [ postcode ] );

	if ( ! estimate.enabled || ! result ) {
		return null;
	}

	if ( result.loading ) {
		return (
			<div className="hw-cart-estimate hw-cart-estimate--loading">
				<p className="hw-cart-estimate__loading">{ estimate.loading }</p>
			</div>
		);
	}

	if ( 'priced' === result.status && 'number' === typeof result.price ) {
		return (
			<div className="hw-cart-estimate hw-cart-estimate--priced">
				<span className="hw-cart-estimate__mark" aria-hidden="true" />
				<div className="hw-cart-estimate__body">
					<span className="hw-cart-estimate__price">
						{ formatEstimatePrice( result.price ) }
					</span>
					<div className="hw-cart-estimate__rate-head">
						<span className="hw-cart-estimate__title">
							{ estimate.pricedTitle }
						</span>
						<p className="hw-cart-estimate__meta">
							{ estimate.pricedTransit }
						</p>
						<p className="hw-cart-estimate__meta">
							{ estimate.pricedNote }
						</p>
					</div>
				</div>
			</div>
		);
	}

	if ( 'unpriced' === result.status ) {
		const reasonClass = result.reason
			? ` hw-cart-estimate--${ String( result.reason ).replace( /_/g, '-' ) }`
			: '';

		return (
			<div
				className={ `hw-cart-estimate hw-cart-estimate--unpriced${ reasonClass }` }
			>
				<span className="hw-cart-estimate__icon" aria-hidden="true" />
				<p className="hw-cart-estimate__message">{ result.message }</p>
			</div>
		);
	}

	if ( 400 === result.httpCode && result.message ) {
		return (
			<div className="hw-cart-estimate hw-cart-estimate--notice">
				<span className="hw-cart-estimate__icon" aria-hidden="true" />
				<p className="hw-cart-estimate__message">{ result.message }</p>
			</div>
		);
	}

	if ( 403 === result.httpCode ) {
		return null;
	}

	return (
		<div className="hw-cart-estimate hw-cart-estimate--notice">
			<span className="hw-cart-estimate__icon" aria-hidden="true" />
			<p className="hw-cart-estimate__message">{ estimate.failure }</p>
		</div>
	);
};

export const DeliveryPopup = ( { open, onClose, shippingAddress } ) => {
	const [ address, setAddress ] = useState( emptyAddress( shippingAddress ) );
	const [ answers, setAnswers ] = useState( {
		forklift: localizedAnswers.forklift || '',
		premises: localizedAnswers.premises || '',
		delivery_instructions: localizedAnswers.delivery_instructions || '',
	} );
	const [ step, setStep ] = useState( 1 );
	const [ editing, setEditing ] = useState( true );
	const [ busy, setBusy ] = useState( false );
	const [ error, setError ] = useState( '' );

	useEffect( () => {
		if ( ! open ) {
			return;
		}

		const stored = getStoredAnswers();
		const next = emptyAddress( shippingAddress );

		setAddress( next );
		setAnswers( {
			forklift: stored.forklift || '',
			premises: stored.premises || '',
			delivery_instructions: stored.delivery_instructions || '',
		} );
		setStep( 1 );
		setEditing( ! hasSavedAddress( next ) );
		setError( '' );
	}, [ open, shippingAddress ] );

	const updateAddress = ( key, value ) => {
		setAddress( ( previous ) => ( { ...previous, [ key ]: value } ) );
	};

	const updateAnswer = ( key, value ) => {
		setAnswers( ( previous ) => ( { ...previous, [ key ]: value } ) );
	};

	const confirm = async () => {
		setBusy( true );
		setError( '' );

		try {
			const store = dispatch( CART_STORE_KEY );
			const current = select( CART_STORE_KEY ).getCustomerData();
			const nextAddress = {
				...current.shippingAddress,
				...address,
				country: resolveShippingCountry( address.country ),
			};
			const customerData = {
				shipping_address: nextAddress,
			};

			// Guests have no saved billing. Copying shipping there is what
			// makes WooCommerce check "Use same address for billing" — it
			// only defaults on when the two addresses already match.
			if ( ! isLoggedIn ) {
				customerData.billing_address = {
					...current.billingAddress,
					...nextAddress,
					email: current.billingAddress?.email || '',
				};
			}

			await store.updateCustomerData( customerData, true, true );

			const packages = select( CART_STORE_KEY ).getShippingRates();
			const firstPackage = packages?.[ 0 ];
			const deliveryRate = firstPackage?.shipping_rates?.find(
				( rate ) => ! isPickupRate( rate.rate_id ),
			);

			if ( firstPackage && deliveryRate ) {
				await store.selectShippingRate(
					deliveryRate.rate_id,
					firstPackage.package_id,
				);
			}

			await pushCartAnswers( answers );

			onClose();
		} catch ( thrown ) {
			setError(
				thrown?.message ||
					__(
						'Could not save that address. Try again, or enter it at checkout.',
						'happywood',
					),
			);
		} finally {
			setBusy( false );
		}
	};

	const goToRate = () => {
		if ( ! hasSavedAddress( address ) ) {
			setEditing( true );
			setError(
				__(
					'Enter a first name, last name, street address, suburb, postcode, and phone first.',
					'happywood',
				),
			);
			return;
		}

		setError( '' );
		setStep( 2 );
	};

	return (
		<Popup
			open={ open }
			onClose={ onClose }
			title={
				step === 1
					? __( 'Enter your delivery address', 'happywood' )
					: __( 'Confirm delivery', 'happywood' )
			}
			actions={
				step === 1 ? (
					<>
						<BackButton onClick={ onClose } />
						<ConfirmButton
							disabled={ ! hasSavedAddress( address ) }
							onClick={ goToRate }
						>
							{ __( 'Continue', 'happywood' ) }
						</ConfirmButton>
					</>
				) : (
					<>
						<BackButton onClick={ () => setStep( 1 ) } />
						<ConfirmButton disabled={ busy } onClick={ confirm }>
							{ busy
								? __( 'Saving…', 'happywood' )
								: __( 'Confirm', 'happywood' ) }
						</ConfirmButton>
					</>
				)
			}
		>
			{ step === 1 ? (
				<>
					<AddressCard
						address={ address }
						onChange={ updateAddress }
						editing={ editing }
						onEdit={ () => setEditing( true ) }
						onDone={ () => setEditing( false ) }
					/>
					<div className="hw-checkout-questions hw-checkout-questions--delivery">
						<div className="hw-checkout-questions__row">
							<Question
								label={ __(
									'Do you have a forklift on site?',
									'happywood',
								) }
							>
								<RadioControl
									id="hw-cart-forklift"
									highlightChecked
									selected={ answers.forklift }
									onChange={ ( value ) =>
										updateAnswer( 'forklift', value )
									}
									options={ [
										{
											value: 'yes',
											label: __( 'Yes', 'happywood' ),
										},
										{
											value: 'no',
											label: __( 'No', 'happywood' ),
										},
									] }
								/>
							</Question>
							<Question
								label={ __(
									'Is this a commercial or residential address?',
									'happywood',
								) }
							>
								<RadioControl
									id="hw-cart-premises"
									highlightChecked
									selected={ answers.premises }
									onChange={ ( value ) =>
										updateAnswer( 'premises', value )
									}
									options={ [
										{
											value: 'commercial',
											label: __(
												'Commercial',
												'happywood',
											),
										},
										{
											value: 'residential',
											label: __(
												'Residential',
												'happywood',
											),
										},
									] }
								/>
							</Question>
						</div>
						<Question
							label={ __( 'Delivery instructions', 'happywood' ) }
						>
							<Textarea
								value={ answers.delivery_instructions }
								onTextChange={ ( value ) =>
									updateAnswer( 'delivery_instructions', value )
								}
								placeholder={ __(
									'Special Notes For delivery please include your opening hours and any special restrictions on delivery time.',
									'happywood',
								) }
							/>
						</Question>
					</div>
				</>
			) : (
				<>
					<div className="hw-cart-location">
						<span
							className="hw-cart-location__icon hw-icon-preview hw-icon-map-pin"
							aria-hidden="true"
						/>
						<div className="hw-cart-location__body">
							<p className="hw-cart-location__address">
								{ formatCardAddress( address ) }
							</p>
						</div>
					</div>
					<DeliveryEstimate postcode={ address.postcode } />
				</>
			) }

			{ error && <p className="hw-cart-popup__error">{ error }</p> }
		</Popup>
	);
};
