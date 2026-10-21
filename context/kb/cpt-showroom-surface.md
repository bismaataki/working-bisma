# Showroom surface — strip manifest

Inventory of the `showrooms` post type and `showroom-locations` taxonomy, taken before the strip on 2026-10-21.

**Purpose:** Showroom is a theme CPT, not a plugin. Most of it lives in capability-named directories and deletes as a unit. This file records that unit and the shared files that had to be edited.

**Regenerate rather than trust.** Re-run the commands at the bottom before relying on the counts.

Status: **stripped** on 2026-10-21. Posts, terms, and ACF meta already stored in the database were left in place.

The procedure is `.cursor/skills/sf-strip-capability/`.

## Delete as a unit

### Blocks (`src-jsx-blocks/`)

`showroom-card`, `showroom-locator`, `showroom-locator-full`, `showroom-contact-details`, `showroom-status-bar`, `showroom-location-details`, `showroom-map-link`.

Registered names: `happywood/showroom-card`, `happywood/showroom-locator`, `happywood/showroom-locator-full`, `happywood/showroom-contact-details`, `happywood/showroom-status-bar`, `happywood/showroom-location-details`, `happywood/showroom-map-link`.

Styles live in each block folder. There is no `src/sass/showroom/` and no `@use` in `src/sass/index.scss`.

### `includes/showroom/`

Loaded from one `require_once` of `bootstrap.php` in `includes/init.php`.

```
bootstrap.php
showroom-card-functions.php
showroom-contact-details-functions.php
showroom-hours-functions.php
showroom-single-functions.php
showroom-content-functions.php
index.php
```

`showroom-content-functions.php` hooks `render_block`. That hook goes away with the directory.

### `lib/` classes

| File | How it loads |
|---|---|
| `class-showrooms-admin.php` | `new Showrooms_Admin()` inside `Init::define_content_types_hooks()` |
| `class-showroom-locator.php` | Static calls from block `render.php`. Not constructed in `Init` |
| `class-showroom-locator-full.php` | Same |

No `public/class-api-*` route. No keys in `Front::get_localize_script_data()`. No TGMPA entry. No `src/js/index.js` import. Block `view.js` files ship with the block folders.

### ACF, templates, patterns, asset

| Path | Role |
|---|---|
| `acf-json/group_showrooms_fields.json` | Post fields |
| `acf-json/group_showroom_locations_term_fields.json` | Term fields |
| `templates/archive-showrooms.html` | Archive |
| `templates/single-showrooms.html` | Single |
| `patterns/section-visit-showrooms.php` | Locator section |
| `patterns/single-showrooms-default.php` | Default single layout |
| `patterns/showroom-gallery.php` | Gallery pattern |
| `assets/images/showroom-locator-map.svg` | Locator map |

## Edited, not deleted

| File | Change |
|---|---|
| `includes/init.php` | Drop the `showroom/bootstrap.php` require |
| `lib/class-init.php` | Drop `new Showrooms_Admin()` |
| `lib/class-content-types.php` | Drop both register methods and their calls |
| `patterns/page-home.php`, `patterns/page-about.php` | Drop the visit-showrooms pattern embed |
| `patterns/page-contact.php` | Drop `happywood/showroom-contact-details` and the locator heading |
| `patterns/footer.php` | Drop the `/showrooms/` nav item |
| `patterns/section-split-hero-stats.php` | Drop the Showrooms stat and the `/showrooms/` button |
| `patterns/section-why-choose.php` | Drop the "7 Showrooms" box copy |
| `patterns/info-box-contact-trade.php` | Drop the showroom info box |
| `patterns/title-contact.php`, `patterns/info-box-timeline-navy.php` | Copy that named the feature |
| `bin/lib/menu-setup-tree.php` | Drop the utility-nav Showrooms item |
| `includes/menu/menu-functions.php` | Drop the title-slug icon branch |
| `src/js/cart/pickup-popup.js` | Pickup validation string. It never queried the CPT |
| `includes/woocommerce/order-confirmation-functions.php` | Comment only. Pickup data stays on the WooCommerce order |

`composer dump-autoload` after the classes go. `rm -rf build-jsx-blocks && npm run build:blocks` so leftover build output does not keep the blocks registered.

## Left alone

Database posts, `showroom-locations` terms, and post meta. Name-only mentions in `context/` history. The pickup snapshot is WooCommerce order data and does not read the CPT.

## Regenerate

```bash
cd wp-content/themes/happywood

ls lib/ | rg -i 'showroom'
ls includes/showroom 2>/dev/null
ls src-jsx-blocks | rg -i 'showroom'
ls acf-json templates patterns assets/images | rg -i 'showroom'
rg -n 'showroom' includes/init.php lib/class-init.php lib/class-content-types.php
rg -n 'happywood/showroom' patterns templates parts
```
