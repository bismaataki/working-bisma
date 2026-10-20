# WooCommerce Surface — Strip Manifest

Every file and code path that assumes WooCommerce exists, so a future project can
remove commerce cleanly.

**Purpose:** commerce is a swappable capability. Most of it is already
capability-named and removable. This document records the exceptions — the places
where commerce has leaked into always-loaded code and must be untangled first.

**Regenerate rather than trust.** These lists were taken from the working tree
and drift as the build progresses. Re-run the commands at the bottom before
relying on the counts.

Status: **not stripped.** No project needs this yet; the manifest exists so the
work is scoped when one does.

The generic procedure — attachment points, deletion order, traps, verification — is
`.cursor/skills/sf-strip-capability/`. This file is the commerce-specific inventory
it consumes, and the worked example for writing a manifest for any other capability.

## Clean — delete the file, delete one line

### `lib/` classes

All instantiated from a single entry point in `lib/class-init.php`.

| File | Owns |
|---|---|
| `class-woocommerce.php` | Entry point / capability guard |
| `class-woocommerce-admin.php` | Product + variation admin fields |
| `class-woocommerce-archive.php` | Shop and archive behaviour |
| `class-classic-wc-compatibility.php` | Classic loop shims (legacy-template archives) |
| `class-woocommerce-single-product.php` | Single product template hooks |
| `class-woocommerce-breadcrumbs.php` | Breadcrumb output |
| `class-woocommerce-quantity.php` | Quantity input |
| `class-product-swatches.php` | Attribute swatch rendering |
| `class-attribute-swatch-preview-column.php` | Admin swatch preview column |
| `class-wishlist.php` | Wishlist integration |

### `includes/woocommerce/`

The whole directory, loaded from `includes/woocommerce/bootstrap.php`
(one require in `includes/init.php`). `ls includes/woocommerce/` plus
`my-account/` is the regenerate source — this list matches `bootstrap.php`
on 4 Sep 2026.

Filters split into query / groups / render; reviews into settings / query /
PDP / slider. Catalog Load More and `/?s=` product scoping live here, not
in the timeless root tier.

```
woocommerce-functions.php         woocommerce-breadcrumb-functions.php
product-filters-query-functions.php
product-filters-groups-functions.php
product-filters-functions.php
load-more-functions.php           search-hooks.php
product-card-functions.php        product-card-hooks.php
product-tabs-functions.php
product-reviews-settings-functions.php
product-reviews-query-functions.php
product-reviews-functions.php
product-reviews-slider-functions.php
product-cat-swiper-functions.php  product-cat-banner-functions.php
product-faqs-functions.php        quick-view-functions.php
delivery-estimate-functions.php   blocks-cart-guard-functions.php
order-questions-functions.php     checkout-questions-functions.php
cart-questions-functions.php      order-confirmation-functions.php
review-card-functions.php         shop-the-look-functions.php
request-quote-functions.php
my-account/my-account-functions.php
my-account/my-account-order-card-functions.php
my-account/my-account-dashboard-functions.php
my-account/my-account-orders-functions.php
my-account/my-account-wishlist-functions.php
```

`quick-view-functions.php` holds the summary-hook callbacks the quick-view
endpoint renders through. They are functions rather than methods because
`add_action`/`remove_action` address them by name.

### Frontend JS (commerce)

Imported from `src/js/index.js` unless noted. Delete the import and the
`init*()` call with the file. Directories travel with their entry:

```
src/js/product-filters.js         src/js/product-filters/
src/cart.js                       src/js/cart/          (own webpack entry)
src/checkout.js                   src/js/request-quote-page.js
src/js/product-search.js          src/js/quick-view.js
src/js/product-view-switcher.js   src/js/single-product-swatches.js
src/js/product-reviews-form-toggle.js
src/js/woocommerce-quantity.js    src/js/order-confirmation.js
src/js/shared/product-card-add-to-cart.js
src/js/shared/product-grid-swatches.js
src/js/shared/wishlist.js
```

Generic `src/js/shared/load-more.js` stays. Shop query branches are PHP in
`includes/woocommerce/load-more-functions.php`.

### `public/` REST endpoints

```
class-api-product-search.php    class-api-quick-view.php
class-api-wishlist.php
```

All three extend `Api` and gate on `class_exists( 'WooCommerce' )` via
`is_available()`. `Init::define_api_hooks()` lists them behind a `class_exists()`
test, so **deleting the files is sufficient** — no fatal, and no edit to `Init`
required first. `public/class-api.php` is the abstract base and stays.

### Sass directories

One import line each in `src/sass/index.scss`:

```
src/sass/woocommerce/    src/sass/swatches/    src/sass/quick-view/
```

### Template overrides

`woocommerce/` in its entirety — `content-product.php`, `global/`, `loop/`. Dead
without the plugin.

### ACF field groups

```
group_product_additional_information.json    group_pa_colour_swatch_fields.json
group_post_shop_the_look.json
```

### JSX blocks

Commerce-named directories in `src-jsx-blocks/`. Regenerate this list rather than
trusting it:

```bash
ls -d src-jsx-blocks/{product,review,wishlist,shop,quick,delivery,order-confirmation}* 2>/dev/null
```

Only four call WooCommerce APIs in their own code — `product-faqs`,
`product-reviews`, `product-reviews-summary`, `product-swatches`. The rest are
thin shims that delegate to `includes/woocommerce/`, which is the intended
architecture working correctly: deleting that directory and the block directories
together removes the capability with no orphaned WC calls.

```
delivery-estimate          order-confirmation-fulfillment
product-card               product-cat-banner        product-cat-child-swiper
product-cat-swiper         product-faqs
product-filters            product-reviews           product-reviews-slider
product-reviews-summary    product-swatches          product-view-switcher
quick-view-button          review-card               shop-the-look
verified-reviews-summary   wishlist-button
```

`delivery-estimate` is a special case: it depends on a companion shipping plugin
that no project ships, so it is already dormant. Its own docblock in
`includes/woocommerce/delivery-estimate-functions.php` lists what to delete.

**Caveat:** `lib/class-blocks-jsx.php` registers every directory in
`build-jsx-blocks/` with **no plugin-dependency check**. These blocks register on
a site without WooCommerce and fatal or render empty when used. Either guard
inside each `render.php` or delete the directories — there is no middle state.



## Previously entangled

### Root Load More / search — resolved 2026-09-04

Catalog per-page, orderby, and shop query gates moved to
`includes/woocommerce/load-more-functions.php`. Unscoped `/?s=` product
scoping moved to `includes/woocommerce/search-hooks.php`. Root
`includes/load-more-functions.php` is generic only.

### `includes/helper-functions.php` — resolved 2026-08-17

Commerce had leaked into this always-loaded file, which can never be deleted.
**Fixed:** all 12 commerce functions now live in
`includes/woocommerce/woocommerce-functions.php`, inside the commerce
`require_once` block that a commerce-free fork deletes with the directory.

Reading the code during the move corrected the severity twice recorded here.
**Every WooCommerce and YITH call was already behind a `function_exists()`
guard** — `skaflow_get_shop_products_per_page()` falls back to
`get_option( 'woocommerce_catalog_columns' )`,
`skaflow_get_product_card_save_label_markup()` returns `''` without
`wc_get_product`, and `skaflow_get_wishlist_login_url()` falls back through
`/my-account/` to `wp_login_url()`. None of them could fatal. The problem was
purely organisational placement, never a runtime risk.

The 12 that moved:

| Group | Functions |
|---|---|
| Product Collection blocks | `skaflow_is_product_collection_loop_block`, `skaflow_get_product_collection_block_post_id`, `skaflow_get_shop_products_per_page`, `skaflow_get_hand_picked_product_collection_blocks_markup`, `skaflow_get_product_card_save_label_markup` |
| YITH Request a Quote | `skaflow_is_ywraq_available`, `skaflow_is_ywraq_quote_button_enabled`, `skaflow_is_quick_view_request_quote_enabled`, `skaflow_render_ywraq_button_quote_markup` |
| Wishlist — ACF-gated, theme-native, no plugin dependency | `skaflow_is_wishlist_enabled`, `skaflow_is_wishlist_loop_enabled`, `skaflow_get_wishlist_login_url` |

Clubbed into one file rather than split per capability: the three groups are small
and interdependent (`skaflow_get_hand_picked_product_collection_blocks_markup()`
calls `skaflow_get_shop_products_per_page()`), and a single file matches how the
capability is actually removed — as a unit.

### Patterns

Patterns referencing product blocks or shop URLs will render empty rather than
fatal. Audit `patterns/` for `skaflow/product-*`, `skaflow/review-*`,
`skaflow/shop-*`, and `woocommerce/*` block references.

### Templates

`templates/` entries for shop, product archive, and single product. Removing the
plugin leaves them orphaned but harmless.

## Order of work, when stripping

1. ~~Move the commerce helpers out of `helper-functions.php`~~ — **done**, they
   are in `includes/woocommerce/`; delete the directory and its `require_once`
   block
2. Delete the JSX block directories, then `rm -rf build-jsx-blocks && npm run build:blocks`
3. Delete `lib/` classes and their `define_woocommerce_hooks()` call
4. Delete `includes/woocommerce/` and its `require_once` block in `init.php`
5. Delete `public/class-api-product-search.php`, `class-api-quick-view.php`,
   `class-api-wishlist.php`. Tidying their lines out of
   `Init::define_api_hooks()` is optional — the `class_exists()` test already
   handles their absence.
6. Delete Sass directories and their import lines
7. Delete `woocommerce/` and the commerce ACF JSON
8. Audit `patterns/` and `templates/`
9. Remove WooCommerce and YITH from the TGMPA list in `lib/class-plugins-manager.php`
10. `composer dump-autoload && npm run build`, then load every template

## Regenerate

```bash
cd wp-content/themes/skaflow

ls lib/ | rg -i 'woo|product|wishlist|swatch'
ls includes/woocommerce/
ls public/ | rg '^class-api-'
ls -d src/sass/*/ | rg -i 'woo|swatch|quick'
ls src-jsx-blocks/ | rg -i 'product|review|shop|wishlist'
ls acf-json/ | rg -i 'product|pa_|review|shop'

# commerce leaking into the always-loaded root tier — should return nothing
rg -ln 'wc_[a-z_]+\(|WC\(\)|WC_Product|yith_|ywraq' includes/*.php

# blocks that call WC directly, vs those that only shim to includes/
for d in src-jsx-blocks/*/; do
  rg -ql 'wc_[a-z_]+\(|WC\(\)|WC_Product|yith_|YITH' "$d" && echo "direct: $d"
done
```

Match on `wc_...(` rather than a bare `wc_`/`woocommerce` substring — the loose
pattern counts docblocks and prose and inflates the result several times over.

## Related

- `.cursor/rules/sf-commerce.mdc` — conventions that keep commerce removable
- `.cursor/rules/project-woocommerce-meta.mdc` — meta keys and formats
- `context/kb/map-load.md` — bootstrap graph
- `.cursor/skills/sf-rename-theme/` — uses this as the model for capability removal
