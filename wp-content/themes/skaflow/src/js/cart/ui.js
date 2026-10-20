/**
 * Cart chooser — shared dialog chrome and address fields.
 */

import { useEffect, useRef, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	TextInput,
	Title,
	countryHasStates,
	formatCardAddress,
	hasSavedAddress,
	shippingCountries,
	showCountryField,
	statesForCountry,
} from './data';

/**
 * A labelled radio group. Same shape as checkout.js — the thing being named
 * is a set of radios, so a fieldset rather than a label with no `for`.
 *
 * @param {Object}  props
 * @param {string}  props.label    Question text.
 * @param {Element} props.children The radio group.
 *
 * @return {Element} The labelled question.
 */
export const Question = ( { label, children } ) => (
	<fieldset className="sk-checkout-question">
		<legend className="sk-checkout-question__label">{ label }</legend>
		{ children }
	</fieldset>
);

/**
 * Native dialog wrapper. WooCommerce does not export a Modal we can reuse
 * from `wc.blocksComponents`, and pulling `@wordpress/components` would
 * bundle a second copy. `<dialog>` is enough for a spike.
 *
 * @param {Object}   props
 * @param {boolean}  props.open     Whether the dialog should be showing.
 * @param {Function} props.onClose  Called when the shopper dismisses it.
 * @param {string}   props.title    Dialog heading.
 * @param {Element}  props.children Body.
 * @param {Element}  props.actions  Footer buttons (Back / Continue).
 *
 * @return {Element} The dialog.
 */
export const Popup = ( { open, onClose, title, children, actions } ) => {
	const ref = useRef( null );

	useEffect( () => {
		const node = ref.current;

		if ( ! node ) {
			return;
		}

		if ( open && ! node.open ) {
			node.showModal();
		} else if ( ! open && node.open ) {
			node.close();
		}
	}, [ open ] );

	return (
		<dialog
			ref={ ref }
			className="sk-cart-popup"
			onClose={ onClose }
		>
			<div className="sk-cart-popup__inner">
				<div className="sk-cart-popup__header">
					<Title headingLevel="2">{ title }</Title>
					<button
						type="button"
						className="sk-cart-popup__close"
						onClick={ onClose }
					>
						<span
							className="sk-icon-preview sk-icon-x"
							aria-hidden="true"
						/>
						<span className="screen-reader-text">
							{ __( 'Close', 'skaflow' ) }
						</span>
					</button>
				</div>
				{ children }
				{ actions && (
					<div className="sk-cart-popup__actions">{ actions }</div>
				) }
			</div>
		</dialog>
	);
};

export const ConfirmButton = ( { disabled, children, onClick } ) => (
	<button
		type="button"
		className="wc-block-components-button wp-element-button contained"
		disabled={ disabled }
		onClick={ onClick }
	>
		<span className="wc-block-components-button__text">{ children }</span>
	</button>
);

export const BackButton = ( { onClick } ) => (
	<button type="button" className="sk-cart-popup__back" onClick={ onClick }>
		<span
			className="sk-icon-preview sk-icon-chevron-left"
			aria-hidden="true"
		/>
		{ __( 'Back', 'skaflow' ) }
	</button>
);

/**
 * Country / state select that reuses WooCommerce's floating-label chrome.
 *
 * The Store API only accepts those codes. A typed name ("Queens",
 * "Australia") is rejected as an invalid shipping_address.
 *
 * @param {Object}                 props
 * @param {string}                 props.id       Field id.
 * @param {string}                 props.label    Floating label, same as TextInput.
 * @param {string}                 props.value    Selected code.
 * @param {Function}               props.onChange Called with the code.
 * @param {Object<string, string>} props.options  Code => name.
 *
 * @return {Element} The select.
 */
export const AddressSelect = ( { id, label, value, onChange, options } ) => (
	<div
		className={
			'wc-block-components-text-input' + ( value ? ' is-active' : '' )
		}
	>
		<select
			id={ id }
			value={ value }
			onChange={ ( event ) => onChange( event.target.value ) }
		>
			<option value="" />
			{ Object.entries( options ).map( ( [ code, name ] ) => (
				<option key={ code } value={ code }>
					{ name }
				</option>
			) ) }
		</select>
		<label htmlFor={ id }>{ label }</label>
	</div>
);

/**
 * Checkout-style address: a closed summary with Edit, or the typed fields.
 *
 * This is the store address *look*, not AddressFinder. WooCommerce's own
 * card lives inside the checkout block and is not exported. The fields
 * still use the block-shipping ids so that plugin can attach later
 * without a custom bind.
 *
 * @param {Object}   props
 * @param {Object}   props.address  Current shipping address.
 * @param {Function} props.onChange (key, value) updater.
 * @param {boolean}  props.editing  Whether the fields are showing.
 * @param {Function} props.onEdit   Opens the fields.
 * @param {Function} props.onDone   Collapses once street + postcode exist.
 *
 * @return {Element} Closed card or open fields.
 */
export const AddressCard = ( { address, onChange, editing, onEdit, onDone } ) => {
	const [ showAddress2, setShowAddress2 ] = useState(
		Boolean( address.address_2 ),
	);

	useEffect( () => {
		setShowAddress2( Boolean( address.address_2 ) );
	}, [ address.address_2 ] );

	const line = formatCardAddress( address );
	const canCollapse = hasSavedAddress( address );

	if ( ! editing && line ) {
		return (
			<div className="sk-cart-address-card">
				<span
					className="sk-cart-address-card__icon sk-icon-preview sk-icon-map-pin"
					aria-hidden="true"
				/>
				<p className="sk-cart-address-card__line">{ line }</p>
				<button
					type="button"
					className="sk-cart-address-card__edit"
					onClick={ onEdit }
				>
					{ __( 'Edit', 'skaflow' ) }
				</button>
			</div>
		);
	}

	return (
		<div className="sk-cart-address-card sk-cart-address-card--editing">
			{ canCollapse && (
				<button
					type="button"
					className="sk-cart-address-card__edit"
					onClick={ onDone }
				>
					{ __( 'Done', 'skaflow' ) }
				</button>
			) }
			<div className="sk-cart-popup__fields">
				{ showCountryField && (
					<AddressSelect
						id="shipping-country"
						label={ __( 'Country / Region', 'skaflow' ) }
						value={ address.country }
						options={ shippingCountries }
						onChange={ ( value ) => {
							onChange( 'country', value );
							if ( ! statesForCountry( value )[ address.state ] ) {
								onChange( 'state', '' );
							}
						} }
					/>
				) }
				<div className="sk-carrier-fields">
					<TextInput
						id="shipping-first_name"
						label={ __( 'First name', 'skaflow' ) }
						value={ address.first_name }
						required
						onChange={ ( value ) => onChange( 'first_name', value ) }
					/>
					<TextInput
						id="shipping-last_name"
						label={ __( 'Last name', 'skaflow' ) }
						value={ address.last_name }
						required
						onChange={ ( value ) => onChange( 'last_name', value ) }
					/>
				</div>
				<TextInput
					id="shipping-address_1"
					label={ __( 'Address', 'skaflow' ) }
					value={ address.address_1 }
					required
					onChange={ ( value ) => onChange( 'address_1', value ) }
				/>
				{ showAddress2 || address.address_2 ? (
					<TextInput
						id="shipping-address_2"
						label={ __( 'Apartment, suite, etc.', 'skaflow' ) }
						value={ address.address_2 }
						onChange={ ( value ) => onChange( 'address_2', value ) }
					/>
				) : (
					<button
						type="button"
						className="sk-cart-address-card__add"
						onClick={ () => setShowAddress2( true ) }
					>
						{ __( '+ Add flat, suite, etc.', 'skaflow' ) }
					</button>
				) }
				<div className="sk-carrier-fields">
					<TextInput
						id="shipping-city"
						label={ __( 'Suburb', 'skaflow' ) }
						value={ address.city }
						required
						onChange={ ( value ) => onChange( 'city', value ) }
					/>
					{ countryHasStates( address.country ) && (
						<AddressSelect
							id="shipping-state"
							label={ __( 'State', 'skaflow' ) }
							value={ address.state }
							options={ statesForCountry( address.country ) }
							onChange={ ( value ) => onChange( 'state', value ) }
						/>
					) }
				</div>
				<div className="sk-carrier-fields">
					<TextInput
						id="shipping-postcode"
						label={ __( 'Postcode', 'skaflow' ) }
						value={ address.postcode }
						required
						onChange={ ( value ) => onChange( 'postcode', value ) }
					/>
					<TextInput
						id="shipping-phone"
						type="tel"
						label={ __( 'Phone', 'skaflow' ) }
						value={ address.phone }
						required
						onChange={ ( value ) => onChange( 'phone', value ) }
					/>
				</div>
			</div>
		</div>
	);
};
