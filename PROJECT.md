# PROJECT.md — Happy Wood

Project-specific facts for this build. Starter conventions are in `AGENTS.md`.

This file is replaced wholesale when the theme is forked into a new project.
Nothing here is expected to survive.

## Identity

This table is the declared input to the `rename-theme` skill. It substitutes
these values across the codebase **and across `AGENTS.md`**, which is why that
file spells names out concretely instead of templating them. The short CSS
prefix is not derivable from the slug, so it must be declared here.

| | |
|---|---|
| Theme slug | `happywood` |
| Theme path | `wp-content/themes/happywood/` |
| Namespace | `Happywood\` |
| Function prefix | `happywood_` |
| Constant prefix | `HAPPYWOOD_` |
| CSS + JS class prefix | `hw-` |
| Text domain | `happywood` |
| DDEV project | `happywood` (repo dir `happywood`), PHP 8.4, docroot = repo root |
| WordPress | 7.1, `theme.json` v3 |

## Repositories

One in-tree repo. Theme, WordPress install, and AI tooling (`AGENTS.md`,
`PROJECT.md`, `context/`, `.cursor/`) live together. There is no client /
AI-tools overlay on this copy. This is an internal project, so that split
is not used.

| | |
|---|---|
| Local DDEV | `https://happywood.test` |
| Origin | `git@github-ataki:atakidevelopers/happywood.git` |
| GitHub | `https://github.com/atakidevelopers/happywood` |
| Local git identity | `AtakiDev` / `developers@ataki.dev` |

## Stack — which implementation is live

Gated skills check this table before running. Do **not** create integration
files for anything marked absent.

| Capability | This project |
|---|---|
| Commerce | **WooCommerce** |
| Icons | **Lucide** — icon font for buttons, Icon Block plugin for standalone |
| Archive filters | **custom** (`includes/woocommerce/product-filters-functions.php`) + WooCommerce native |
| Card rendering | **custom** (`includes/woocommerce/product-card-functions.php`, `includes/post/post-card-functions.php`) |
| SEO | **Rank Math** |
| Grid builder | **none** |
| FAQ | **kept** (`happywood_faq` + `happywood_faq_category`) |
| Showroom | **removed** (`showrooms` + `showroom-locations`). Inventory: `context/kb/cpt-showroom-surface.md` |

Explicitly absent: no Blokki, no WP Grid Builder, no Yoast, no JetEngine,
no Jet Smart Filters, no Elementor, no Woodmart. Do not create `blokki/`,
`wp-grid-builder/`, or `includes/integrations/blokki.php` in this project.
Do not reintroduce a Jet\* or page-builder dependency. ACF Pro is the
field layer; filters and cards are custom theme code.

**`@wordpress/scripts` is `35.0.0`.** Flat `eslint.config.cjs`, Stylelint 17,
`--webpack-copy-php` and `--blocks-manifest` on the blocks build only.
See `ADR-0001` and `ADR-0002`.

**Swiper is `14.2.0`** (runtime, bundled into `build/`). CSS imports, not
SCSS. Theme nav buttons are empty and draw CSS chevrons, so every
`navigation` config sets `addIcons: false`. Surfaces: category swiper,
product-reviews slider, quick-view gallery.
Shop the Look does not use Swiper.

## Plugins

Required via TGMPA: **ACF Pro** and **Gravity Forms** (bundled zips in
`lib/plugins-zip/`), plus **Icon Block** and **Heroic Table of Contents**
from wordpress.org.

WooCommerce is the declared commerce implementation. It is not in the TGMPA
list; install it when the catalog is needed.

**YITH Request a Quote is optional.** The theme ships a guarded surface
(header widget, Theme Options toggles, `patterns/page-request-quote.php`,
`includes/woocommerce/request-quote-functions.php`) that no-ops without the
plugin. Do not add YITH to TGMPA unless a project actually needs quotes.

Gravity Forms embeds in patterns use `formId` `1` (contact) and `2`
(subscribe / sidebar). That is the one allowed database-ID exception; see
`AGENTS.md`. Create those two forms, in that order, on a fresh install.

## Content model

Custom post types and taxonomies all register from
`lib/class-content-types.php` (`Happywood\Content_Types`):
`happywood_faq` + `happywood_faq_category` (admin + REST only, not publicly
queryable), and `happywood_banner`. Showroom was removed; see
`context/kb/cpt-showroom-surface.md`.
Product catalog facets are WooCommerce categories, tags, and attributes.
There is no Review CPT (WooCommerce product reviews stay).

ACF options pages: **Theme Options** (`happywood-theme-options`) with
**FAQ Configurations** and **Banner Configurations** sub-pages. Field groups
auto-load from `acf-json/`.

Pattern categories registered by `lib/class-after-setup-theme.php`: `page`,
`component`, `section`, `stats`, `happywood`. The `elements` category is
retired. Use `component`.

## Known debt

**Delivery estimate is dormant**, inherited from the starter. `templates/single-product.html` includes
`happywood/delivery-estimate`. Lookups post to `cf_postcode_estimate`, which no
bundled plugin registers. The card renders and returns the error state. That
AJAX action and the `cf_delivery_postcode` / `cf_delivery_quantity` field
names are an external plugin contract: do not rename them with the theme
prefix. Deletion path is in
`includes/woocommerce/delivery-estimate-functions.php`.

## Lineage

Forked from the skaflow starter on 2026-10-20 for the internal Happy Wood
project. AI tooling stays in this repository. Showroom stays in the theme
until a later decision removes it.

## Where the detail lives

- `context/README.md` — plans → implementation → notes → kb
- `context/PROGRESS.md` — status layer, edited in place
- `context/CHANGELOG.md`, the append-only timeline of shipped features and ADRs
- `context/kb/` — derived facts (maps, contracts, WPCS, go-live, SOPs)
- `context/plans/`, `implementation/`, `notes/`, `decisions/`, `figma/` —
  placeholder dirs stay even when a folder is between features
- `sf-context-docs` skill, which owns naming, plan-to-implementation linking,
  ADR numbering, and the close-out steps for anything written in `context/`
