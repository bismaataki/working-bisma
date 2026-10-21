# Edit map — where to start

One row per surface. Edit the **source** column, not `build/` or `build-jsx-blocks/`.
Composition contracts live in other `kb/` files. Maps are regenerated from code.

| If you need to change | Start here | Also touches |
|---|---|---|
| **Product card** | `includes/woocommerce/product-card-functions.php`, `src-jsx-blocks/product-card/` | Hooks: `includes/woocommerce/product-card-hooks.php`. JS: `src/js/shared/product-card-add-to-cart.js`. Sass: `src/sass/woocommerce/product-card/`. Archive shell: `lib/class-woocommerce-archive.php`. Composition: `context/kb/grid-product.md`. |
| **Shop filters / archive shell** | `includes/woocommerce/product-filters-functions.php`, `src/js/product-filters.js` | Query/URLs: `includes/woocommerce/product-filters-query-functions.php`. Groups/chips: `includes/woocommerce/product-filters-groups-functions.php`. JS modules: `src/js/product-filters/`. Shell: `includes/archive-shell-functions.php`, `parts/shop-archive-shell.html`. Sass: `src/sass/woocommerce/_product-filters.scss`, `_shop-archive-layout.scss`. View toggle: `src-jsx-blocks/product-view-switcher/`, `src/js/product-view-switcher.js`. Contract: `context/kb/plugin-wc-shop-archive.md`. |
| **Load More** | `lib/class-load-more.php`, `src/js/shared/load-more.js` | Query replay: `includes/load-more-functions.php`. Catalog branches: `includes/woocommerce/load-more-functions.php`. Editor: `src/blocks-extension/extend-core-query-pagination/`. Contract: `context/kb/plugin-wc-shop-archive.md`, `context/kb/grid-product.md`. |
| **Category banner** | `includes/woocommerce/product-cat-banner-functions.php` | CPT `skaflow_banner`. Options repeater. Contract: `context/kb/plugin-wc-shop-archive.md`. |
| **Public product search** | `includes/woocommerce/search-hooks.php` | Scopes unscoped `/?s=` to products. Blog listing search on `/blogs/` is left alone. |
| **Product reviews** | `includes/woocommerce/product-reviews-functions.php` | Settings: `product-reviews-settings-functions.php`. Queries: `product-reviews-query-functions.php`. Slider/cache: `product-reviews-slider-functions.php`. Blocks: `src-jsx-blocks/product-reviews/`, `product-reviews-slider/`. |
| **Quick View** | `includes/woocommerce/quick-view-functions.php`, `src/js/quick-view.js` | Block: `src-jsx-blocks/quick-view-button/`. REST: `public/class-api-quick-view.php`. |
| **Wishlist** | `lib/class-wishlist.php`, `src/js/shared/wishlist.js` | Block: `src-jsx-blocks/wishlist-button/`. REST: `public/class-api-wishlist.php`. Account page: `includes/woocommerce/my-account/my-account-wishlist-functions.php`. Contract: `context/kb/plugin-wc-my-account.md`. |
| **Cart / checkout questions** | `includes/woocommerce/cart-questions-functions.php`, `includes/woocommerce/checkout-questions-functions.php` | Shared contract: `includes/woocommerce/order-questions-functions.php`. JS entry: `src/cart.js` (modules in `src/js/cart/`). Checkout: `src/checkout.js`. Sass: `_cart-questions.scss`, `_checkout-questions.scss`. Contract: `context/kb/plugin-wc-checkout-questions.md`. |
| **My Account** | `includes/woocommerce/my-account/my-account-functions.php` | Overrides: `woocommerce/myaccount/`. Entry: `src/my-account.js`. Contract: `context/kb/plugin-wc-my-account.md`. |
| **Order confirmation** | `templates/order-confirmation.html`, `src-jsx-blocks/order-confirmation-fulfillment/` | JS: `src/js/order-confirmation.js`. Sass: `_order-confirmation.scss`. Contract: `context/kb/plugin-wc-checkout-questions.md`. |
| **Request a Quote** | `includes/woocommerce/request-quote-functions.php`, `patterns/page-request-quote.php` | JS: `src/js/request-quote-page.js`. Sass: `_request-quote-page.scss`. |
| **Header / mega menu** | `lib/class-site-header.php`, `src/js/site-header.js` | Menu walkers + ACF: `includes/menu/`. Sass: `src/sass` header / mega-menu partials. Placeholder block: `src-jsx-blocks/site-header/`. |
| **FAQ archive / product FAQs** | `includes/faq/faq-archive-functions.php`, `includes/woocommerce/product-faqs-functions.php` | Archive block: `src-jsx-blocks/faq-groups/`. Product block: `src-jsx-blocks/product-faqs/`. Search: `src/js/faq-search.js`. Contract: `context/kb/cpt-faq.md`. |
| **Icons** | `src/js-admin/lucide-icon-options.js` then `npm run build:icons` | Generated: font CSS, Sass map, Icon Block data, menu PHP. PHP enqueue: `lib/class-lucide-icons.php`. Rule: `.cursor/rules/project-lucide-icons.mdc`. |
| **CPT / taxonomy registration** | `lib/class-content-types.php` | FAQ, Banner. Showroom was removed (`context/kb/cpt-showroom-surface.md`). ACF groups in `acf-json/` — a group whose location targets an unregistered type is dead weight, so add and remove them together. |
| **Theme Options fields** | `acf-json/group_theme_options.json` | Header reads via `lib/class-site-header.php`. WooCommerce tab: wishlist, reviews, filters, tabs. |
| **Block editor extensions** | `src/blocks-extension/` | Index: `src/blocks-extension/README.md`. Do not add new `src/js-admin/*-icons.js` by hand. |
| **Related posts Query Loop** | `src/blocks-extension/extend-core-query/query-related-posts.js` | PHP: `includes/post/post-archive-hooks.php`. Contract: `context/kb/query.md`. |
| **Blog listing filters** | `includes/post/post-filters-functions.php`, `src-jsx-blocks/post-filters/` | Posts page URL + public query vars. Do not send toolbar links to `/category/…`. Contract: `context/kb/query.md`. |

Event handlers on grids: `src/js/shared/delegated-events.js` — never bind inside a card loop.

Programmatic buttons: `skaflow_get_buttons_markup()` in `includes/helper-functions.php`.

What boots: `context/kb/map-load.md`.
