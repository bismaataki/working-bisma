# Shop archive

How the catalog page refreshes, paginates, and switches views. Card markup
is `context/kb/grid-product.md`. Start files: `context/kb/map-edit.md`.

## Transport

Filter, sort, and Load More all **fetch a full catalog HTML page** and extract
a piece of it. There is no `load-more` REST route.

| Action | Fetch | Extract |
|---|---|---|
| Filter / sort | Current archive URL with new query args | `.sk-shop-archive-shell` |
| Load More | `/shop/page/N/` (or the current archive + page) | `.wc-block-product-template > li` |

Frontend: `src/js/product-filters/catalog.js` (`refreshCatalogFromUrl`) and
`src/js/shared/load-more.js`. After a swap or append, dispatch
`skaflow:content-updated` so delegated listeners re-apply.

`pushState` + `popstate` keep the URL and the shell in sync. The filter panel
stays open across a swap when it was open.

JS names a fetch-only query arg `_sk_archive` and strips it from public
and fetch URLs. Do not add a PHP branch for it. History state uses
`{ skFilters: true }`.

## Grid / list view

Client-only. No URL param, no REST.

| | |
|---|---|
| Storage | `localStorage` key **`sk_products_view`** (`grid` / `list`) |
| Class | **`sk-products-view-list`** on `.sk-shop-archive-main` |
| Scope | `.sk-shop-archive-shell` only |
| Markup | Same `skaflow/product-card` in both modes |

Re-apply on `skaflow:content-updated`. Block:
`src-jsx-blocks/product-view-switcher/`. JS: `src/js/product-view-switcher.js`.

## Category banner

`skaflow_banner` CPT, mapped from Banner Configurations (ACF options repeater).
Resolution in `skaflow_resolve_product_cat_banner_id()`:

1. Exact `product_cat` term
2. Else first ancestor row with `display_on_child_categories`
3. Else nothing

Render is `do_blocks()` on the banner post content
(`includes/woocommerce/product-cat-banner-functions.php`).

## Single-product swatches (Quick View reuse)

Swatches prepend through
`woocommerce_dropdown_variation_attribute_options_html`. There is no
`variable.php` override.

`src/js/single-product-swatches.js` exports `primeVariationForms(root)`.
Quick View must call that after injecting SSR HTML. Gallery swap is WooCommerce
Variation Gallery (Settings → Advanced → Features), not theme code.

## Related

- `context/kb/grid-product.md` — card + Load More extract
- `context/kb/map-edit.md` — Shop filters, Load More, Product card
