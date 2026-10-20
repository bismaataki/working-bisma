---
name: sf-strip-capability
description: Remove a swappable capability from the theme — commerce, a CPT, filters, cards, an SEO coupling — or audit what one owns before forking. Use when asked to strip WooCommerce, delete a capability, fork the starter without a feature, or produce a surface manifest for one.
---

# Stripping a Capability

The starter's directory conventions exist so a capability can be removed as a unit.
This is the procedure for actually doing it, and for auditing the surface before
committing to it.

**Never delete from memory.** The attachment points below are the ones this theme
has today; regenerate the list from code every time, because a capability that grew
after the last audit will have grown quietly.

## Step 0 — write the manifest first

Produce or refresh a surface manifest in `context/kb/` before deleting
anything. Name it with the prefixes in `context/kb/README.md`:

| Capability | Manifest |
|---|---|
| WooCommerce / a third-party plugin | `plugin-{slug}-surface.md` |
| A theme CPT | `cpt-{slug}.md` (add `-surface` only if it is a strip inventory) |
| A grid / card system | `grid-{slug}.md` |

`context/kb/plugin-woocommerce-surface.md` is the model: what the
capability owns, how many files, which of them are hard dependencies
versus name-only coupling, and a regenerate block so the next person
doesn't trust a stale copy. Do not invent a subfolder. Add the new file
to `context/kb/README.md`.

```bash
cd wp-content/themes/happywood

ls lib/ | rg -i '{capability}|{related-nouns}'
ls public/ | rg '^class-api-'
ls includes/{capability}/ 2>/dev/null
ls -d src/sass/*/ | rg -i '{capability}'
ls src-jsx-blocks/ src-acf-blocks/ | rg -i '{capability}'
ls acf-json/ | rg -i '{capability}'
rg -n '{capability}' src/js/index.js src/sass/index.scss src/sass-admin/index.scss

# capability leaking into always-loaded root files — these get edited, not deleted
rg -ln '{plugin_api_prefix}' includes/*.php
```

Match on a real call shape such as `wc_[a-z_]+\(` rather than a bare substring.
A loose pattern counts docblocks and prose and inflates the result several times
over, which makes the capability look far more entangled than it is.

## The anatomy

| Attachment point | Removing it also means |
|---|---|
| `lib/class-{capability}*.php` | Deleting `define_{capability}_hooks()` **and its call in the `Init` constructor** |
| `public/class-api-*.php` | Nothing — `Init::define_api_hooks()` skips missing classes. Tidy the list if you like |
| `includes/{capability}/` | Deleting its one `bootstrap.php` require in `includes/init.php` (header/menu: the `lib/` class require) |
| `includes/*.php` at the root | **Editing, never deleting.** Root files are always loaded; strip the capability's branches out |
| `src/sass/{capability}/` | **Two** load sites — `@use` in `src/sass/index.scss` and `meta.load-css()` in `src/sass-admin/index.scss` |
| `src/js/{module}.js` | Its `import` **and** its `init*()` call in `src/js/index.js`. Some modules have a directory (`src/js/cart/`, `src/js/product-filters/`) — delete the directory with the entry |
| `src-jsx-blocks/{block}/` | `rm -rf build-jsx-blocks && npm run build:blocks` |
| `src-acf-blocks/{block}/` | Nothing extra — registration is directory-driven |
| `acf-json/group_*.json` | Field groups vanish; **existing post meta stays** in the database |
| Template override dir (e.g. `woocommerce/`) | Nothing — dead without the plugin |
| `templates/`, `parts/`, `patterns/` | Auditing for references to deleted blocks and patterns |
| CPT / taxonomy registration | `lib/class-content-types.php` |
| Localized JS data | The capability's keys in `Front::get_localize_script_data()` |
| TGMPA plugin list | `lib/class-plugins-manager.php` |

## Order, and why it matters

1. **Blocks**, then `rm -rf build-jsx-blocks && npm run build:blocks`. Build output
   is generated; deleting source alone leaves the old block registered.
2. **JS and Sass**, imports and call sites together with the files.
3. **`lib/` classes**, then their `define_*_hooks()` method and its constructor call.
4. **`public/` endpoints.**
5. **`includes/{capability}/`** and its `bootstrap.php` require. Classes go first
   because some `require_once` their capability's function file at the top.
6. **Root-tier `includes/*.php` edits.**
7. **ACF JSON, template overrides, templates, parts, patterns.**
8. **CPTs, localized data, TGMPA.**
9. `composer dump-autoload && npm run build`.
10. Docs — `PROJECT.md` § Stack and § Plugins, the manifest, and
    `context/kb/README.md` if a new file was added.

## Traps

- **Stale classmap fatals.** Deleting a `lib/` or `public/` class without
  `composer dump-autoload` leaves the classmap pointing at a missing file. This is
  the single most common way a strip appears to have broken the site.
- **Blocks register without their plugin.** `lib/class-blocks-jsx.php` registers
  everything in `build-jsx-blocks/` with no dependency check, so a leftover
  commerce block still appears in the inserter. The build output is the source of
  truth, not `src-jsx-blocks/`.
- **Patterns fail quietly.** A pattern referencing a deleted block renders as an
  invalid block in the editor, not a PHP error. Grep, don't rely on page loads.
- **One-sided Sass removal breaks the build** with a missing-file error, because
  the capability is loaded from both `src/sass/index.scss` (`@use`) and
  `src/sass-admin/index.scss` (`meta.load-css()`).
- **Root-tier files can't be deleted.** Edit branches out; do not remove the
  file. Primary term lives in `includes/taxonomy-terms-functions.php` (SEO
  plugin readers, not commerce). Catalog Load More and product search scoping
  already live in `includes/woocommerce/` and delete with that directory.
  Check `PROJECT.md` § Known debt and `context/kb/map-load.md` before editing
  leftover root helpers.
- **Frontend JS is a checklist plus a few directories.** Most modules are
  flat files imported from `src/js/index.js`. Cart and catalog filters also
  have `src/js/cart/` and `src/js/product-filters/` — treat `index.js` as the
  checklist, then delete those directories with their entries.

## Verification

```bash
cd <repo root>

# boots at all — catches classmap and require_once fatals
ddev wp eval 'echo "ok\n";'

# nothing references what you deleted
cd wp-content/themes/happywood
rg -n '{deleted_function_or_class}' --glob '!vendor/**' --glob '!build*/**' .

# deleted block names left in patterns or templates
rg -n 'happywood/{deleted-block}' patterns/ templates/ parts/

# builds clean
npm run build
```

Then load every template — home, an archive, a single post, search, 404 — and check
`debug.log`. A strip that boots can still render an empty region.

If the capability owned REST endpoints, confirm they are gone rather than erroring:

```bash
ddev wp eval 'do_action( "rest_api_init" );
  foreach ( array_keys( rest_get_server()->get_routes() ) as $r ) {
    if ( false !== strpos( $r, "happywood" ) ) { echo $r . "\n"; }
  }'
```

## Auditing without deleting

Most invocations of this skill are the audit, not the strip — "how entangled is
this?" before a fork. Stop after Step 0, write the manifest, and report the count
of hard dependencies versus name-only coupling. Those are very different costs, and
conflating them has previously made a capability look unremovable when every call
was already guarded.

## Related

- `context/kb/README.md` — filename prefixes
- `context/kb/plugin-woocommerce-surface.md` — worked example of a manifest
- `context/kb/map-load.md` — what `init.php` and `class-init.php` load today
- `.cursor/rules/sf-php-architecture.mdc` — the two-tier `includes/` rule that makes
  this possible
- `.cursor/rules/sf-build-pipeline.mdc` — why build output must be regenerated
- `.cursor/skills/sf-rename-theme/` — the other fork-time procedure
- `PROJECT.md` — which capabilities are live, and known leakage
