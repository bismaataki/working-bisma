# Load map — what boots the theme

**Regenerate rather than trust.** Graph as of 7 Sep 2026.
Re-run the commands at the bottom after adding a class or an includes file.

Feature → source file: `context/kb/map-edit.md`. This file is only the
bootstrap graph.

## PHP

`functions.php` → `includes/init.php` → `Skaflow\Init`.

### `includes/init.php`

Composer `wp_die` + `vendor/autoload.php`, then:

| Tier | Files |
|---|---|
| Core | `constants.php`, `helper-functions.php` |
| Timeless machinery | `accordion-functions.php`, `taxonomy-terms-functions.php`, `category-swiper-functions.php`, `load-more-functions.php` (generic), `archive-shell-functions.php` |
| Capabilities (one require each) | `woocommerce/bootstrap.php`, `post/bootstrap.php`, `faq/bootstrap.php`, `integrations/bootstrap.php` |

Commented `disable-comments.php` stays in `init.php` (root-tier). Do not add
capability files here.

Catalog Load More and `/?s=` product scoping are **not** in the timeless
tier. They load from `includes/woocommerce/bootstrap.php`.

### Capability bootstraps

Loaded from `init.php`:

| Bootstrap | Requires |
|---|---|
| `woocommerce/bootstrap.php` | Commerce function files (see `context/kb/plugin-woocommerce-surface.md`) |
| `post/bootstrap.php` | `post-card-functions.php`, `post-filters-functions.php`, `post-archive-hooks.php` |
| `faq/bootstrap.php` | `faq-archive-functions.php` |
| `integrations/bootstrap.php` | `query-monitor.php` |

Loaded from `lib/` classes, not from `init.php`:

| Bootstrap | Instantiated by |
|---|---|
| `header/bootstrap.php` (`header-acf.php`) | `Skaflow\Site_Header` |
| `menu/bootstrap.php` (walker, hooks, ACF, icon choices) | `Skaflow\Mega_Menu` |

`Site_Header` also `require_once`s `includes/menu/skaflow-lucide-icon-choices.php`
(already loaded from the menu bootstrap via `header-acf.php`).

### `lib/class-init.php`

Constructor groups (order is the hook-registration order — do not reshuffle
without a reason):

| Kind | `define_*_hooks()` |
|---|---|
| Always-on shell | `after_setup_theme`, plugins manager, ACF, REST (`define_api_hooks`), Front, shortcodes, JSX blocks, editor curation, Lucide, admin style fixes |
| Header / menu | `Site_Header`, `Mega_Menu` |
| Content types | `Content_Types`, `Showrooms_Admin` |
| Commerce | `WooCommerce` (returns early without the plugin; constructs admin / archive / classic-compat / single / quantity / swatches / wishlist). `Attribute_Swatch_Preview_Column` is a separate always-constructed admin column |
| Plugin integration | `Gravity_Forms` |
| Generic machinery | `Load_More` (shop branches hook in from `includes/woocommerce/load-more-functions.php`) |

REST endpoints listed in `define_api_hooks()` skip missing classes:
`Api_Product_Search`, `Api_Quick_View`, `Api_Wishlist`.

## Frontend JS

`src/public.js` → `src/js/index.js`. Webpack entry is `public.js`; never
build a single file with `wp-scripts` or `build/` is wiped.

`src/js/index.js` inits: site header, order confirmation, product search,
grid + single swatches, reviews form toggle, accordions, category swipers,
quick view, product filters, view switcher, Load More, card add-to-cart,
wishlist, quantity, FAQ search, request-quote page.

Directories used as modules (entry file stays the import path):

- `src/js/cart/` — imported from webpack entry `src/cart.js`, not `index.js`
- `src/js/product-filters/` — imported from `src/js/product-filters.js`

## Regenerate

```bash
cd wp-content/themes/skaflow

# includes graph
rg -n 'require_once' includes/init.php includes/*/bootstrap.php

# class graph
rg -n 'function define_|new [A-Z]' lib/class-init.php lib/class-woocommerce.php

# frontend inits
rg -n '^import |init[A-Z]' src/js/index.js src/cart.js
```
