/**
 * Single-product variation swatches: proxy clicks into WooCommerce selects and
 * swap the main gallery when a variation has a multi-image gallery.
 *
 * Gallery HTML comes from WooCommerce's native gallery_images_html field, which
 * is populated by the Variation Gallery feature (Settings → Advanced → Features).
 * Data is sourced from _product_image_gallery on each variation post, migrated
 * from the legacy Woodmart meta by the BatchPress normalisation job.
 *
 * Swatch clicks use vanilla delegation (3b convention). found_variation,
 * reset_data, and woocommerce_update_variation_values use a scoped jQuery
 * exception — those events are jQuery-only in WooCommerce core.
 *
 * @see context/kb/plugin-wc-shop-archive.md
 */

import jQuery from 'jquery';

import { delegateDocument } from './shared/delegated-events';

/** @type {WeakMap<Element, { defaultGalleryHtml: string, galleryReplaced: boolean }>} */
const formState = new WeakMap();

let swatchListenersBound = false;
let singleProductSwatchesInitialized = false;

/**
 * @param {Element} form
 */
function getFormState( form ) {
	if ( ! formState.has( form ) ) {
		const gallery = getGalleryForForm( form );

		formState.set( form, {
			defaultGalleryHtml: gallery?.outerHTML || '',
			galleryReplaced: false,
		} );
	}

	return formState.get( form );
}

/**
 * @param {Element} form
 * @return {boolean} Whether the form lives inside a quick-view panel.
 */
function isQuickViewForm( form ) {
	return Boolean( form.closest( '.hw-quick-view__content' ) );
}

/**
 * Notify quick-view gallery module that gallery markup changed.
 *
 * @param {Element} form
 */
function dispatchQuickViewGalleryChanged( form ) {
	if ( ! isQuickViewForm( form ) ) {
		return;
	}

	form.dispatchEvent(
		new CustomEvent( 'cf:quick-view-gallery-changed', { bubbles: true } ),
	);
}

/**
 * @param {Element} form
 * @return {Element|null} Gallery element tied to the form, if present.
 */
function getGalleryForForm( form ) {
	const productRoot =
		form.closest( '.product' ) ||
		form.closest( '.wp-block-woocommerce-add-to-cart-form' )?.parentElement;

	if ( productRoot ) {
		const scoped = productRoot.querySelector(
			'.woocommerce-product-gallery',
		);
		if ( scoped ) {
			return scoped;
		}
	}

	return document.querySelector( '.woocommerce-product-gallery' );
}

/**
 * @param {Element}                       form
 * @param {string}                        galleryHtml
 * @param {Record<string, unknown>|false} variation
 * @return {boolean} Whether the gallery markup was replaced.
 */
function replaceProductGallery( form, galleryHtml, variation ) {
	const $form = jQuery( form );

	if (
		typeof $form.wc_variations_gallery_replace === 'function' &&
		galleryHtml
	) {
		const replaced = Boolean(
			$form.wc_variations_gallery_replace( galleryHtml, variation ),
		);

		if ( replaced ) {
			dispatchQuickViewGalleryChanged( form );
			return true;
		}
	}

	const gallery = getGalleryForForm( form );

	if ( ! gallery || ! galleryHtml ) {
		return false;
	}

	const template = document.createElement( 'template' );
	template.innerHTML = galleryHtml.trim();
	const newGallery = template.content.querySelector(
		'.woocommerce-product-gallery',
	);

	if ( ! newGallery ) {
		return false;
	}

	gallery.replaceWith( newGallery );

	if ( ! isQuickViewForm( form ) ) {
		jQuery( newGallery ).wc_product_gallery();
	}

	window.dispatchEvent( new Event( 'resize' ) );

	if ( variation && variation.image_id ) {
		$form.attr( 'current-image', String( variation.image_id ) );
		$form.attr( 'data-product_gallery_active', 'yes' );
	} else {
		$form.removeAttr( 'data-product_gallery_active' );
		$form.attr( 'current-image', '' );
	}

	dispatchQuickViewGalleryChanged( form );

	return true;
}

/**
 * @param {Element} form
 */
function syncActiveSwatches( form ) {
	form.querySelectorAll( '.hw-swatches-single' ).forEach( ( group ) => {
		const selectId = group.dataset.id;

		if ( ! selectId ) {
			return;
		}

		const select = form.querySelector( `select#${ CSS.escape( selectId ) }` );

		if ( ! select ) {
			return;
		}

		const selectedValue = select.value;

		group.querySelectorAll( '.hw-swatch' ).forEach( ( swatch ) => {
			swatch.classList.toggle(
				'hw-swatch--active',
				swatch.dataset.value === selectedValue,
			);
		} );
	} );
}

/**
 * @param {Element} form
 */
function syncSwatchDisabledState( form ) {
	form.querySelectorAll( '.hw-swatches-single' ).forEach( ( group ) => {
		const selectId = group.dataset.id;

		if ( ! selectId ) {
			return;
		}

		const select = form.querySelector( `select#${ CSS.escape( selectId ) }` );

		if ( ! select ) {
			return;
		}

		group.querySelectorAll( '.hw-swatch' ).forEach( ( swatch ) => {
			const value = swatch.dataset.value || '';
			const option = Array.from( select.options ).find(
				( opt ) => opt.value === value,
			);
			const isEnabled =
				option &&
				! option.disabled &&
				! option.classList.contains( 'disabled' );

			swatch.classList.toggle( 'hw-swatch--out-of-stock', ! isEnabled );
			swatch.disabled = ! isEnabled;
		} );
	} );
}

/**
 * @param {Event}   event
 * @param {Element} swatch
 */
function handleSwatchClick( event, swatch ) {
	event.preventDefault();

	const group = swatch.closest( '.hw-swatches-single' );
	const form = swatch.closest( '.variations_form' );

	if ( ! group || ! form || swatch.disabled ) {
		return;
	}

	const selectId = group.dataset.id;

	if ( ! selectId ) {
		return;
	}

	const select = form.querySelector( `select#${ CSS.escape( selectId ) }` );

	if ( ! select ) {
		return;
	}

	select.value = swatch.dataset.value || '';
	select.dispatchEvent( new Event( 'change', { bubbles: true } ) );

	group.querySelectorAll( '.hw-swatch--active' ).forEach( ( active ) => {
		active.classList.remove( 'hw-swatch--active' );
	} );
	swatch.classList.add( 'hw-swatch--active' );
}

/**
 * Runs after WooCommerce's onFoundVariation (document bubble) so the full
 * gallery root is replaced via wc_variations_gallery_replace().
 *
 * Prefers WC's native gallery_images_html (available when the Variation Gallery
 * feature is enabled). Falls back to hw_variation_gallery_html, which is always
 * generated by add_variation_gallery_html() reading from _product_image_gallery
 * (WC native, post-migration) or wd_additional_variation_images_data (Woodmart).
 *
 * @param {Event}                   event
 * @param {Record<string, unknown>} variation
 */


/**
 * Return variation data supplied by WooCommerce.
 */
function getProductVariationData( form ) {
	const $form = jQuery( form );
	let variations = $form.data( 'product_variations' );

	if ( ! Array.isArray( variations ) && form.dataset.product_variations ) {
		try {
			variations = JSON.parse( form.dataset.product_variations );
		} catch ( error ) {
			variations = [];
		}
	}

	return Array.isArray( variations ) ? variations : [];
}

/**
 * Find the best matching variation price for a color swatch.
 * Blank variation attributes are treated as WooCommerce wildcards.
 */
function getColorVariationPrice( form, colorValue ) {
	const variations = getProductVariationData( form );
	const selects = Array.from(
		form.querySelectorAll( 'select[name^="attribute_"]' )
	);

	const matches = variations
		.map( ( variation ) => {
			let score = 0;

			for ( const select of selects ) {
				const name = select.name;
				const expected = name === 'attribute_pa_color'
					? colorValue
					: select.value;

				if ( ! expected ) {
					continue;
				}

				const actual = variation.attributes?.[ name ] || '';

				if ( actual && actual !== expected ) {
					return null;
				}

				if ( actual ) {
					score++;
				}
			}

			if ( ! variation.price_html ) {
				return null;
			}

			return { variation, score };
		} )
		.filter( Boolean )
		.sort( ( a, b ) => b.score - a.score );

	if ( ! matches.length ) {
		return '';
	}

	const temp = document.createElement( 'div' );
	temp.innerHTML = matches[0].variation.price_html;

	return (
		temp.querySelector( 'ins .woocommerce-Price-amount' ) ||
		temp.querySelector( '.woocommerce-Price-amount' )
	)?.outerHTML || '';
}

/**
 * Update each color swatch using its matching variation.
 */
function syncColorSwatchPrices( form ) {
	form.querySelectorAll(
		'.hw-swatches-single[data-id="pa_color"] .hw-swatch'
	).forEach( ( swatch ) => {
		const price = swatch.querySelector( '.hw-swatch__price' );

		if ( ! price ) {
			return;
		}

		price.innerHTML = getColorVariationPrice(
			form,
			swatch.dataset.value || ''
		);
	} );
}

/**
 * Select the first available option for attributes that have no selection.
 * Preserve any existing WooCommerce default selection.
 */
function initializeDefaultSwatches( form ) {
	const attributeOrder = [
		'attribute_pa_size',
		'attribute_pa_material',
		'attribute_pa_color',
	];

	attributeOrder.forEach( ( name ) => {
		const select = form.querySelector(
			`select[name="${ name }"]`
		);

		if ( ! select || select.value ) {
			return;
		}

		const firstAvailable = Array.from( select.options ).find(
			( option ) =>
				option.value &&
				! option.disabled &&
				! option.classList.contains( 'disabled' )
		);

		if ( firstAvailable ) {
			select.value = firstAvailable.value;
			select.dispatchEvent(
				new Event( 'change', { bubbles: true } )
			);
		}
	} );

	jQuery( form ).trigger( 'check_variations' );
	syncActiveSwatches( form );
	syncMaterialLabels( form );
	syncSwatchDisabledState( form );
}

/**
 * Update material labels inside all color swatches from the selected material.
 *
 * @param {Element} form
 */
function syncMaterialLabels( form ) {
	const materialSelect = form.querySelector(
		'select[name="attribute_pa_material"], select#pa_material'
	);

	const selectedMaterial = materialSelect?.selectedOptions?.[0];
	const materialLabel = materialSelect?.value
		? selectedMaterial?.textContent.trim() || ''
		: '';

	form.querySelectorAll(
		'.hw-swatches-single[data-id="pa_color"] .hw-swatch__material'
	).forEach( ( label ) => {
		label.textContent = materialLabel ? `${ materialLabel } -` : '';
	} );
}


function handleFoundVariation( event, variation ) {
	const form = event.currentTarget;

	if ( ! form ) {
		return;
	}

	syncMaterialLabels( form );

syncColorSwatchPrices( form );

	const galleryHtml =
		variation?.hw_variation_gallery_html;

	if ( ! galleryHtml ) {
		syncActiveSwatches( form );
		syncSwatchDisabledState( form );
		return;
	}

	const state = getFormState( form );
	const replaced = replaceProductGallery(
		form,
		String( galleryHtml ),
		variation,
	);

	state.galleryReplaced = replaced;
	syncActiveSwatches( form );
	syncSwatchDisabledState( form );
}


/**
 * @param {Event} event
 */
function handleResetData( event ) {
	const form = event.currentTarget;

	if ( ! form ) {
		return;
	}

	syncMaterialLabels( form );

form.querySelectorAll( '.hw-swatch__price' ).forEach( ( price ) => {
	price.innerHTML = '';
} );

	if ( ! form ) {
		return;
	}

	const state = getFormState( form );

	if (
		state.defaultGalleryHtml &&
		( state.galleryReplaced || isQuickViewForm( form ) )
	) {
		replaceProductGallery( form, state.defaultGalleryHtml, false );
		state.galleryReplaced = false;
	} else if ( isQuickViewForm( form ) ) {
		dispatchQuickViewGalleryChanged( form );
	}

	form.querySelectorAll( '.hw-swatch--active' ).forEach( ( swatch ) => {
		swatch.classList.remove( 'hw-swatch--active' );
	} );

	syncSwatchDisabledState( form );
}

/**
 * @param {Event} event
 */
function handleVariationValuesUpdated( event ) {
	const form = event.currentTarget;

	if ( ! form ) {
		return;
	}

	syncMaterialLabels( form );
	syncActiveSwatches( form );
	syncSwatchDisabledState( form );
}

/**
 * @param {Event} event
 */
function handleResetVariationsClick( event ) {
	const link = event.currentTarget;
	const form = link.closest( '.variations_form' );

	if ( ! form ) {
		return;
	}

	form.querySelectorAll( '.hw-swatch--active' ).forEach( ( swatch ) => {
		swatch.classList.remove( 'hw-swatch--active' );
	} );
}

/**
 * Bind delegated swatch + jQuery variation handlers once (Load-More-safe).
 */
export function bindSingleProductSwatchListeners() {
	if ( swatchListenersBound ) {
		return;
	}

	swatchListenersBound = true;

	delegateDocument(
		'click',
		'.hw-swatches-single .hw-swatch',
		handleSwatchClick,
	);

	jQuery( document ).on(
		'found_variation',
		'.variations_form',
		handleFoundVariation,
	);
	jQuery( document ).on( 'reset_data', '.variations_form', handleResetData );
	jQuery( document ).on(
		'change',
		'.variations_form select[name="attribute_pa_material"], .variations_form select#pa_material',
		function() {
			syncMaterialLabels( this.closest( '.variations_form' ) );
		}
	);
	jQuery( document ).on(
		'woocommerce_update_variation_values',
		'.variations_form',
		handleVariationValuesUpdated,
	);
	jQuery( document ).on(
		'click',
		'.variations_form .reset_variations',
		handleResetVariationsClick,
	);
}

/**
 * Cache default gallery markup for variation forms within `root`.
 * Safe to call again after AJAX/quick-view injects new forms.
 *
 * @param {Element|Document|DocumentFragment} [root]
 */
export function primeVariationForms( root = document ) {
	const forms = root.querySelectorAll( '.variations_form' );

	if ( ! forms.length ) {
		return;
	}

	forms.forEach( ( form ) => {
		getFormState( form );
		syncMaterialLabels( form );
	} );

	// WC fires the first availability pass during its own init — defer so we
	// register listeners first, then sync once its option classes are settled.
	jQuery( function() {
		window.setTimeout( () => {
			forms.forEach( ( form ) => {
				initializeDefaultSwatches( form );
				syncColorSwatchPrices( form );
				syncSwatchDisabledState( form );
			} );
		}, 0 );
	} );
}

/**
 * One-time init for page load — binds listeners and primes existing forms.
 */
export function initSingleProductSwatches() {
	if ( singleProductSwatchesInitialized ) {
		return;
	}

	singleProductSwatchesInitialized = true;
	bindSingleProductSwatchListeners();
	primeVariationForms( document );
}
