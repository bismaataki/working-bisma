import { initProductSearchForms } from './product-search';
import { initProductFilters } from './product-filters';
import { initProductViewSwitcher } from './product-view-switcher';
import { initQuickView } from './quick-view';
import { initLoadMore } from './shared/load-more';
import { initProductCardAddToCart } from './shared/product-card-add-to-cart';
import { initProductGridSwatches } from './shared/product-grid-swatches';
import { initWishlist } from './shared/wishlist';
import { initSingleProductSwatches } from './single-product-swatches';
import { initProductReviewsFormToggle } from './product-reviews-form-toggle';
import { initAccordions } from './accordion';
import {
	bindCategorySwiperListeners,
	initCategorySwipers,
} from './category-swiper';
import { initOrderConfirmation } from './order-confirmation';
import { initSiteHeader } from './site-header';
import { initWooCommerceQuantity } from './woocommerce-quantity';
import { initFaqSearch } from './faq-search';
import { initRequestQuotePage } from './request-quote-page';

export {
	CONTENT_UPDATED_EVENT,
	delegateDocument,
	delegateOn,
	dispatchContentUpdated,
	isQueryRoot,
} from './shared/delegated-events';

document.addEventListener( 'DOMContentLoaded', () => {
	initSiteHeader();
	initOrderConfirmation();
	initProductSearchForms();
	initProductGridSwatches();
	initSingleProductSwatches();
	initProductReviewsFormToggle();
	initAccordions();
	initCategorySwipers();
	bindCategorySwiperListeners();
	initQuickView();
	initProductFilters();
	initProductViewSwitcher();
	initLoadMore();
	initProductCardAddToCart();
	initWishlist();
	initWooCommerceQuantity();
	initFaqSearch();
	initRequestQuotePage();
} );

/* Grid Guide overlay */
const toggle = document.querySelector( '#grid-overlay-toggle' );
if ( toggle ) {
	toggle.addEventListener( 'click', () => {
		document.querySelectorAll( '.grid-overlay' ).forEach( ( overlay ) => {
			const isHidden =
				overlay.style.display === 'none' ||
				getComputedStyle( overlay ).display === 'none';
			overlay.style.display = isHidden ? 'block' : 'none';
		} );
	} );
}
