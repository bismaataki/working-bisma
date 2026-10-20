/**
 * Cart tiles that open a delivery popup or a pickup popup.
 *
 * The block cart has no slot beside the line items, so this module mounts
 * itself at the top of `.wc-block-cart__main` once that node exists. Each
 * tile opens one dialog. Confirm writes a delivery address or a warehouse
 * rate into the Store API cart, and the eight answers into
 * `POST /wc/store/v1/cart/extensions` so checkout can show them. The
 * delivery popup also asks the postcode plugin for a rate or its
 * quote-only `message` once a postcode is typed.
 *
 * Delivery is two steps (address, then rate / quote). Pickup stays one
 * screen. The address uses a closed card + Edit / Done, not AddressFinder.
 * Shipping Continue needs first name, last name, street, suburb, postcode,
 * phone (and state when the country has them). Country is only a field when
 * WooCommerce ships to more than one country; a single ship-to is posted
 * hidden. Pickup Continue needs a warehouse,
 * and a carrier name when the carrier is collecting. A guest Confirm also
 * writes the same address to billing so checkout opens with "Use same
 * address for billing" checked. Logged-in billing is left alone.
 *
 * AddressFinder is inactive on this box and only binds to checkout field
 * ids. The delivery inputs reuse those ids (`#shipping-address_1` and
 * neighbours) so the plugin can attach when it is on; until then the fields
 * are typed by hand. Do not invent a second autocomplete.
 *
 * Visual fidelity is not the job — native WooCommerce controls, same
 * rule as checkout.js. See `context/kb/plugin-wc-checkout-questions.md`.
 */

import { createRoot, useCallback, useState } from '@wordpress/element';
import { dispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import {
	CART_STORE_KEY,
	RadioControl,
	RadioControlAccordion,
	Textarea,
	TextInput,
	Title,
	formatAddressLine,
	isPickupRate,
	pickupLocations,
} from './js/cart/data';
import { DeliveryPopup } from './js/cart/delivery-popup';
import { PickupPopup } from './js/cart/pickup-popup';

const CartChooser = () => {
	const [ open, setOpen ] = useState( null );
	const { shippingAddress, shippingRates } = useSelect( ( selectStore ) => {
		const store = selectStore( CART_STORE_KEY );

		return {
			shippingAddress: store.getCustomerData()?.shippingAddress || {},
			shippingRates: store.getShippingRates() || [],
		};
	}, [] );

	const selectedRate = shippingRates
		.flatMap( ( item ) => item.shipping_rates || [] )
		.find( ( rate ) => rate.selected );
	const selectedIsPickup = isPickupRate( selectedRate?.rate_id );
	const selectedPickup = pickupLocations.find(
		( location ) => location.rate_id === selectedRate?.rate_id,
	);
	const deliveryLine = formatAddressLine( shippingAddress );

	const close = useCallback( () => setOpen( null ), [] );

	return (
		<div className="hw-cart-chooser">
			<div className="hw-cart-chooser__header">
				<h2 className="hw-cart-chooser__title">
					{ __( 'Deliver / Pick up options', 'happywood' ) }
				</h2>
			</div>
			<div className="hw-cart-chooser__tiles">
				<button
					type="button"
					className={
						'hw-cart-tile' +
						( selectedRate && ! selectedIsPickup
							? ' hw-cart-tile--active'
							: '' )
					}
					onClick={ () => setOpen( 'delivery' ) }
				>
					<span
						className="hw-cart-tile__icon hw-icon-preview hw-icon-truck"
						aria-hidden="true"
					/>
					<span className="hw-cart-tile__body">
						<span className="hw-cart-tile__title">
							{ __( 'Shipping', 'happywood' ) }
						</span>
						<span className="hw-cart-tile__meta">
							<span className="hw-cart-tile__meta-label">
								{ __( 'Deliver to:', 'happywood' ) }
							</span>{ ' ' }
							<span className="hw-cart-tile__meta-value">
								{ deliveryLine ||
									__( 'Enter Address', 'happywood' ) }
							</span>
						</span>
					</span>
				</button>
				<button
					type="button"
					className={
						'hw-cart-tile' +
						( selectedIsPickup ? ' hw-cart-tile--active' : '' )
					}
					onClick={ () => setOpen( 'pickup' ) }
				>
					<span
						className="hw-cart-tile__icon hw-icon-preview hw-icon-package"
						aria-hidden="true"
					/>
					<span className="hw-cart-tile__body">
						<span className="hw-cart-tile__title">
							{ __( 'Pick Up/Carrier Collect', 'happywood' ) }
						</span>
						<span className="hw-cart-tile__meta">
							<span className="hw-cart-tile__meta-label">
								{ __( 'From:', 'happywood' ) }
							</span>{ ' ' }
							<span className="hw-cart-tile__meta-value">
								{ selectedPickup?.name ||
									__( 'Select Warehouse', 'happywood' ) }
							</span>
						</span>
					</span>
				</button>
			</div>
			<DeliveryPopup
				open={ open === 'delivery' }
				onClose={ close }
				shippingAddress={ shippingAddress }
			/>
			<PickupPopup
				open={ open === 'pickup' }
				onClose={ close }
				selectedRateId={ selectedIsPickup ? selectedRate.rate_id : '' }
			/>
		</div>
	);
};

const DEPENDENCIES = {
	CART_STORE_KEY,
	RadioControl,
	RadioControlAccordion,
	Textarea,
	TextInput,
	Title,
	applyExtensionCartUpdate: CART_STORE_KEY
		? dispatch( CART_STORE_KEY )?.applyExtensionCartUpdate
		: undefined,
};

const missing = Object.keys( DEPENDENCIES ).filter( ( name ) => ! DEPENDENCIES[ name ] );

const mountChooser = () => {
	const main = document.querySelector( '.wc-block-cart__main' );

	if ( ! main || main.querySelector( '.hw-cart-chooser-root' ) ) {
		return Boolean( main );
	}

	const node = document.createElement( 'div' );
	node.className = 'hw-cart-chooser-root';
	main.prepend( node );
	createRoot( node ).render( <CartChooser /> );
	return true;
};

const start = () => {
	if ( missing.length ) {
		// eslint-disable-next-line no-console
		console.error(
			'Happywood cart questions did not render: WooCommerce no longer ' +
				`exports ${ missing.join( ', ' ) }.`,
		);
		return;
	}

	if ( mountChooser() ) {
		return;
	}

	const observer = new MutationObserver( () => {
		if ( mountChooser() ) {
			observer.disconnect();
		}
	} );

	observer.observe( document.body, { childList: true, subtree: true } );
};

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', start );
} else {
	start();
}
