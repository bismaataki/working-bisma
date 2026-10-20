/**
 * Checkout questions for the block checkout.
 *
 * WooCommerce's block checkout takes no PHP hooks for adding fields beside a
 * shipping choice, so questions are React rendered into WooCommerce's own
 * SlotFills. Two slots carry all of ours, and each only mounts when its half of
 * the Ship / Pickup toggle is active — so the conditional visibility the design
 * needs comes from the slot, not from a rule we evaluate:
 *
 *   ExperimentalOrderShippingPackages     beside the delivery rates
 *   ExperimentalOrderLocalPickupPackages  under the warehouse radios
 *
 * Every control is a WooCommerce component rather than our own markup. That is
 * deliberate: hand-rolled inputs land in the checkout carrying the theme's field
 * styles, which fight the block stylesheet — labels printing over placeholders
 * was the visible symptom. Borrowing the components means inheriting
 * `wc-block-components-*` classes, and the questions look native for free.
 *
 * Answers travel two ways, so the cart popups and checkout share one session:
 * `setExtensionData` puts them on the checkout request (place-order), and a
 * debounced `applyExtensionCartUpdate` writes the same keys to the cart
 * session. A refresh or a return to the cart therefore sees the last edit,
 * not only the last cart-popup Confirm.
 *
 * Delivery and pickup answers use the cart address card: a one-line
 * summary with Edit, and Done to collapse the fields again. See
 * `context/kb/plugin-wc-checkout-questions.md`.
 */

import { registerPlugin } from '@wordpress/plugins';
import { useCallback, useEffect, useState } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Namespace for Store API extension data. Must match the PHP side.
 */
const NAMESPACE = 'skaflow';

/**
 * Answers already written from a cart popup, if any.
 *
 * Localised from the cart session so the fields can seed before the checkout
 * store has anything. Empty object when the shopper has not used a popup.
 */
const storedAnswers = window.skaflowCheckoutQuestions?.answers || {};

/**
 * WooCommerce Blocks is read off its runtime globals rather than imported.
 *
 * The theme's wp-scripts build has no WooCommerce dependency-extraction plugin,
 * so an `@woocommerce/*` import would be bundled into our output instead of
 * externalised to the handles WooCommerce already enqueues. The PHP side
 * declares `wc-blocks-checkout`, `wc-blocks-components`, and
 * `wc-blocks-data-store`, which guarantees the globals exist by the time
 * this module runs.
 */
const {
	ExperimentalOrderLocalPickupPackages,
	ExperimentalOrderShippingPackages,
} = window.wc?.blocksCheckout || {};

const {
	CheckboxControl,
	RadioControl,
	RadioControlAccordion,
	Textarea,
	TextInput,
	Title,
} = window.wc?.blocksComponents || {};

const { CART_STORE_KEY } = window.wc?.wcBlocksData || {};

/**
 * Writes answers to the cart session, debounced so a textarea does not POST
 * on every keystroke.
 *
 * @param {Object} answers Partial answers to merge.
 *
 * @return {void}
 */
const persistToCart = ( () => {
	let timer;

	return ( answers ) => {
		if ( ! CART_STORE_KEY ) {
			return;
		}

		window.clearTimeout( timer );
		timer = window.setTimeout( () => {
			dispatch( CART_STORE_KEY ).applyExtensionCartUpdate( {
				namespace: NAMESPACE,
				data: answers,
			} );
		}, 400 );
	};
} )();

/**
 * Sends answers to the checkout request and, shortly after, to the cart session.
 *
 * `setExtensionData` is immediate and is what place-order reads. The cart
 * write is the same door the popups use, so a refresh or a return to the
 * cart sees this edit. Merges rather than replaces; PHP still stores only
 * the group matching the method the order actually used.
 *
 * @param {Object} answers Partial answers to merge.
 *
 * @return {void}
 */
const pushAnswers = ( answers ) => {
	dispatch( 'wc/store/checkout' ).setExtensionData( NAMESPACE, answers );
	persistToCart( answers );
};

/**
 * Tracks a group's answers and pushes each change to the server.
 *
 * @param {Object} initial Starting answers.
 *
 * @return {Array} `[ answers, update ]`.
 */
const useAnswers = ( initial ) => {
	const [ answers, setAnswers ] = useState( initial );

	const update = useCallback( ( key, value ) => {
		setAnswers( ( previous ) => {
			const next = { ...previous, [ key ]: value };
			pushAnswers( next );
			return next;
		} );
	}, [] );

	return [ answers, update ];
};

/**
 * A labelled radio group.
 *
 * `RadioControl` renders the options but no heading for them, and the other
 * controls need nothing from here: text inputs float their own label, and the
 * textareas are deliberately bare, as the checkout's order note is.
 *
 * A fieldset rather than a label, because the thing being named is a set of
 * radios rather than one control — a `<label>` with no `for` names nothing.
 *
 * @param {Object}  props
 * @param {string}  props.label    Question text.
 * @param {Element} props.children The radio group.
 *
 * @return {Element} The labelled question.
 */
const Question = ( { label, children } ) => (
	<fieldset className="sk-checkout-question">
		<legend className="sk-checkout-question__label">{ label }</legend>
		{ children }
	</fieldset>
);

/**
 * An optional note, built the way the checkout's own order note is built.
 *
 * A checkbox reveals a bare textarea — no label, the placeholder carries the
 * prompt. Unticking clears the answer so the order records only what the form
 * was showing, and the cleared text is kept locally so re-ticking restores it.
 * That is WooCommerce's behaviour for its order note, copied deliberately.
 *
 * @param {Object}   props
 * @param {string}   props.id          Id for the checkbox.
 * @param {string}   props.label       Checkbox label.
 * @param {string}   props.placeholder Textarea placeholder.
 * @param {string}   props.value       Current answer.
 * @param {Function} props.onChange    Receives the new answer.
 *
 * @return {Element} The note field.
 */
const NoteField = ( { id, label, placeholder, value, onChange } ) => {
	const [ wanted, setWanted ] = useState( '' !== value );
	const [ stashed, setStashed ] = useState( '' );

	const toggle = ( checked ) => {
		setWanted( checked );

		if ( checked ) {
			if ( value !== stashed ) {
				onChange( stashed );
			}

			return;
		}

		onChange( '' );
		setStashed( value );
	};

	return (
		<div className="wc-block-checkout__add-note">
			<CheckboxControl
				id={ id }
				label={ label }
				checked={ wanted }
				onChange={ toggle }
			/>
			{ wanted && (
				<Textarea
					value={ value }
					onTextChange={ onChange }
					placeholder={ placeholder }
				/>
			) }
		</div>
	);
};

/**
 * Cart-style summary card: one line + Edit, or Done + the fields.
 *
 * @param {Object}   props
 * @param {string}   props.icon        Lucide slug for the leading icon.
 * @param {string}   [props.title]     Heading shown beside Done while editing.
 * @param {string}   props.summary     Collapsed sentence.
 * @param {boolean}  props.editing     Whether the fields are showing.
 * @param {boolean}  props.canCollapse Whether Done can close the card.
 * @param {Function} props.onEdit      Opens the fields.
 * @param {Function} props.onDone      Collapses the fields.
 * @param {Element}  props.children    Fields shown while editing.
 *
 * @return {Element} Closed card or open fields.
 */
const AnswersCard = ( {
	icon,
	title,
	summary,
	editing,
	canCollapse,
	onEdit,
	onDone,
	children,
} ) => {
	if ( ! editing && summary ) {
		return (
			<div className="sk-cart-address-card">
				<span
					className={
						'sk-cart-address-card__icon sk-icon-preview sk-icon-' +
						icon
					}
					aria-hidden="true"
				/>
				<p className="sk-cart-address-card__line">{ summary }</p>
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
			<div className="sk-cart-address-card__toolbar">
				{ title ? <Title headingLevel="3">{ title }</Title> : null }
				{ canCollapse && (
					<button
						type="button"
						className="sk-cart-address-card__edit"
						onClick={ onDone }
					>
						{ __( 'Done', 'skaflow' ) }
					</button>
				) }
			</div>
			{ children }
		</div>
	);
};

/**
 * One sentence from the two delivery radios, for the collapsed card.
 *
 * @param {string} forklift yes|no|''.
 * @param {string} premises commercial|residential|''.
 *
 * @return {string} Summary, or empty until both are answered.
 */
const formatDeliverySummary = ( forklift, premises ) => {
	if ( 'yes' === forklift && 'commercial' === premises ) {
		return __( 'I have a forklift on a commercial site', 'skaflow' );
	}

	if ( 'yes' === forklift && 'residential' === premises ) {
		return __( 'I have a forklift on a residential site', 'skaflow' );
	}

	if ( 'no' === forklift && 'commercial' === premises ) {
		return __( "I don't have a forklift on a commercial site", 'skaflow' );
	}

	if ( 'no' === forklift && 'residential' === premises ) {
		return __(
			"I don't have a forklift on a residential site",
			'skaflow',
		);
	}

	return '';
};

/**
 * Questions asked beside the delivery rates.
 *
 * Neither radio group carries a default. The design marks none of these
 * required, and recording "No forklift" because nobody touched the control
 * would be inventing an answer. Once both radios have a value the fields
 * collapse behind that sentence, same Edit / Done card as the cart address.
 *
 * @return {Element} The delivery questions.
 */
const DeliveryQuestions = () => {
	const [ answers, update ] = useAnswers( {
		forklift: storedAnswers.forklift || '',
		premises: storedAnswers.premises || '',
		delivery_instructions: storedAnswers.delivery_instructions || '',
	} );
	const summary = formatDeliverySummary( answers.forklift, answers.premises );
	const canCollapse = Boolean( summary );
	const [ editing, setEditing ] = useState( ! canCollapse );

	useEffect( () => {
		if (
			! storedAnswers.forklift &&
			! storedAnswers.premises &&
			! storedAnswers.delivery_instructions
		) {
			return;
		}

		pushAnswers( {
			forklift: storedAnswers.forklift || '',
			premises: storedAnswers.premises || '',
			delivery_instructions: storedAnswers.delivery_instructions || '',
		} );
	}, [] );

	return (
		<div className="sk-checkout-questions sk-checkout-questions--delivery">
			<AnswersCard
				icon="package"
				summary={ summary }
				editing={ editing }
				canCollapse={ canCollapse }
				onEdit={ () => setEditing( true ) }
				onDone={ () => setEditing( false ) }
			>
				{ /*
				 * Two to a row on wider viewports; see the Sass. Both groups are
				 * short enough that a full-width card each would be mostly empty.
				 */ }
				<div className="sk-checkout-questions__row">
					<Question
						label={ __(
							'Do you have a forklift on site?',
							'skaflow',
						) }
					>
						<RadioControl
							id="sk-forklift"
							highlightChecked
							selected={ answers.forklift }
							onChange={ ( value ) => update( 'forklift', value ) }
							options={ [
								{
									value: 'yes',
									label: __( 'Yes', 'skaflow' ),
								},
								{ value: 'no', label: __( 'No', 'skaflow' ) },
							] }
						/>
					</Question>

					<Question
						label={ __(
							'Is this a commercial or residential address?',
							'skaflow',
						) }
					>
						<RadioControl
							id="sk-premises"
							highlightChecked
							selected={ answers.premises }
							onChange={ ( value ) => update( 'premises', value ) }
							options={ [
								{
									value: 'commercial',
									label: __( 'Commercial', 'skaflow' ),
								},
								{
									value: 'residential',
									label: __( 'Residential', 'skaflow' ),
								},
							] }
						/>
					</Question>
				</div>

				<NoteField
					id="sk-delivery-instructions-toggle"
					label={ __(
						'Add instructions for opening hours and time',
						'skaflow',
					) }
					value={ answers.delivery_instructions }
					onChange={ ( value ) => update( 'delivery_instructions', value ) }
					placeholder={ __(
						'Special Notes For delivery please include your opening hours and any special restrictions on delivery time.',
						'skaflow',
					) }
				/>
			</AnswersCard>
		</div>
	);
};

/**
 * Carrier details, shown once the shopper says a carrier is collecting.
 *
 * Nothing here is enforced. The design marks Carrier name with an asterisk, but
 * the block checkout gates submission through its own validation store rather
 * than native form validity, so a `required` attribute alone is decoration and
 * making it bite means opting into that store. An order with a blank carrier
 * name is a phone call, not a broken order, so it is not worth the machinery —
 * see the plan for the trade.
 *
 * These are the carrier option's accordion content, so there is no wrapper or
 * conditional here: `RadioControlAccordion` renders content for the selected
 * option only, and gives it the boxed surface and the `aria-describedby` tying
 * it to the radio that revealed it.
 *
 * @param {Object}   props
 * @param {Object}   props.answers Current answers.
 * @param {Function} props.update  Answer setter.
 *
 * @return {Element} The carrier fields.
 */
const CarrierFields = ( { answers, update } ) => (
	<>
		<p className="sk-checkout-questions__intro">
			{ __(
				"Arrange your own shipping? Simply provide your carrier details and we'll do the rest.",
				'skaflow',
			) }
		</p>

		{ /*
		 * WooCommerce has no reusable two-column field grid to borrow.
		 * `wc-block-components-address-form` looks like one, but the flex
		 * declarations are scoped to `.wc-block-checkout__billing-fields`
		 * and `.wc-block-checkout__shipping-fields`, so the class does
		 * nothing outside those blocks. The Sass mirrors its measurements.
		 */ }
		<div className="sk-carrier-fields">
			<TextInput
				id="sk-carrier-name"
				className="sk-carrier-fields__wide"
				label={ __( 'Carrier name', 'skaflow' ) }
				value={ answers.carrier_name }
				required
				onChange={ ( value ) => update( 'carrier_name', value ) }
			/>
			<TextInput
				id="sk-carrier-account"
				label={ __( 'Account Number', 'skaflow' ) }
				value={ answers.carrier_account }
				onChange={ ( value ) => update( 'carrier_account', value ) }
			/>
			<TextInput
				id="sk-carrier-phone"
				type="tel"
				label={ __( 'Phone', 'skaflow' ) }
				value={ answers.carrier_phone }
				onChange={ ( value ) => update( 'carrier_phone', value ) }
			/>
		</div>

		<NoteField
			id="sk-carrier-note-toggle"
			label={ __( 'Add pickup notes', 'skaflow' ) }
			value={ answers.carrier_note }
			onChange={ ( value ) => update( 'carrier_note', value ) }
			placeholder={ __( 'Enter special pick-up notes here…', 'skaflow' ) }
		/>
	</>
);

/**
 * One sentence from the collector choice, for the collapsed card.
 * Carrier needs a name before it can collapse; notes stay off the line.
 *
 * @param {string} collectedBy myself|carrier|''.
 * @param {string} carrierName Typed carrier name.
 *
 * @return {string} Summary, or empty until the path is complete.
 */
const formatCollectionSummary = ( collectedBy, carrierName ) => {
	if ( 'myself' === collectedBy ) {
		return __( 'I am picking up the items myself', 'skaflow' );
	}

	const name = typeof carrierName === 'string' ? carrierName.trim() : '';

	if ( 'carrier' === collectedBy && name ) {
		return sprintf(
			/* translators: %s: carrier name */
			__( 'Courier %s is picking up the items', 'skaflow' ),
			name,
		);
	}

	return '';
};

/**
 * Questions asked beneath the warehouse the shopper picked.
 *
 * Defaults to collecting in person, which is both the design's initial state
 * and the answer that needs no follow-up questions. That default is enough
 * to collapse the card; a carrier path needs a name first, same gate as
 * the cart popup. Notes stay behind Edit.
 *
 * The collector choice is an accordion because that is how the checkout already
 * asks a question whose answer brings its own fields: the payment method list is
 * this component with `highlightChecked`, and each gateway's form is its option's
 * content. The alternative was borrowing the Ship / Pickup toggle's classes,
 * which WooCommerce builds inline in the checkout block and exports as nothing —
 * a component's props survive a release in a way another block's markup does not.
 *
 * @return {Element} The collection questions.
 */
const CollectionQuestions = () => {
	const [ answers, update ] = useAnswers( {
		collected_by: storedAnswers.collected_by || 'myself',
		carrier_name: storedAnswers.carrier_name || '',
		carrier_account: storedAnswers.carrier_account || '',
		carrier_phone: storedAnswers.carrier_phone || '',
		carrier_note: storedAnswers.carrier_note || '',
	} );
	const summary = formatCollectionSummary(
		answers.collected_by,
		answers.carrier_name,
	);
	const canCollapse = Boolean( summary );
	const [ editing, setEditing ] = useState( ! canCollapse );
	const [ showAllLocations, setShowAllLocations ] = useState( false );

	// Record the default (or the cart-popup value) so an untouched control
	// still reaches the order.
	useEffect( () => {
		pushAnswers( {
			collected_by: storedAnswers.collected_by || 'myself',
			carrier_name: storedAnswers.carrier_name || '',
			carrier_account: storedAnswers.carrier_account || '',
			carrier_phone: storedAnswers.carrier_phone || '',
			carrier_note: storedAnswers.carrier_note || '',
		} );
	}, [] );

	// The location radios live in WooCommerce's wrapper, not in this
	// fill, so the expanded class has to sit on that node.
	useEffect( () => {
		const root = document.querySelector(
			'.wc-block-components-local-pickup-select',
		);

		if ( ! root ) {
			return undefined;
		}

		root.classList.toggle(
			'sk-pickup-locations--expanded',
			showAllLocations,
		);

		return () => {
			root.classList.remove( 'sk-pickup-locations--expanded' );
		};
	}, [ showAllLocations ] );

	return (
		<div className="sk-checkout-questions sk-checkout-questions--collection">
			<button
				type="button"
				className="sk-pickup-locations-toggle"
				onClick={ () => setShowAllLocations( ( current ) => ! current ) }
			>
				{ showAllLocations
					? __( 'Show fewer pickup points', 'skaflow' )
					: __( 'Show all pickup points', 'skaflow' ) }
			</button>
			<AnswersCard
				icon={ 'carrier' === answers.collected_by ? 'file-text' : 'user' }
				title={ __( 'Who is picking up the items', 'skaflow' ) }
				summary={ summary }
				editing={ editing }
				canCollapse={ canCollapse }
				onEdit={ () => setEditing( true ) }
				onDone={ () => setEditing( false ) }
			>
				<RadioControlAccordion
					id="sk-collected-by"
					highlightChecked
					selected={ answers.collected_by }
					onChange={ ( value ) => update( 'collected_by', value ) }
					options={ [
						{ value: 'myself', label: __( 'Myself', 'skaflow' ) },
						{
							value: 'carrier',
							label: __( 'My Custom Carrier', 'skaflow' ),
							content: (
								<CarrierFields
									answers={ answers }
									update={ update }
								/>
							),
						},
					] }
				/>
			</AnswersCard>
		</div>
	);
};

/**
 * Everything read off WooCommerce's globals, so a missing one can be named.
 *
 * The two slots are prefixed `Experimental`, which WooCommerce documents as
 * subject to rename or removal — on graduation the prefix is simply dropped.
 * Nothing here would throw if that happened: the guard below would fail, the
 * checkout would render perfectly, and orders would quietly stop carrying
 * answers. That silence is the reason for the message.
 */
const DEPENDENCIES = {
	ExperimentalOrderLocalPickupPackages,
	ExperimentalOrderShippingPackages,
	CheckboxControl,
	RadioControl,
	RadioControlAccordion,
	Textarea,
	TextInput,
	Title,
};

const missing = Object.keys( DEPENDENCIES ).filter( ( name ) => ! DEPENDENCIES[ name ] );

if ( missing.length ) {
	// eslint-disable-next-line no-console
	console.error(
		'Skaflow checkout questions did not render: WooCommerce no longer ' +
			`exports ${ missing.join( ', ' ) }. Orders placed now will not ` +
			'record delivery or collection answers. Migration path: ' +
			'context/kb/plugin-wc-checkout-questions.md.',
	);
} else {
	// Fills mount through the checkout's own PluginArea, which is rendered with
	// the `woocommerce-checkout` scope. Registering a plugin is the only way an
	// external script can get a component into that React tree.
	registerPlugin( 'skaflow-checkout-questions', {
		scope: 'woocommerce-checkout',
		render: () => (
			<>
				<ExperimentalOrderShippingPackages>
					<DeliveryQuestions />
				</ExperimentalOrderShippingPackages>
				<ExperimentalOrderLocalPickupPackages>
					<CollectionQuestions />
				</ExperimentalOrderLocalPickupPackages>
			</>
		),
	} );
}
