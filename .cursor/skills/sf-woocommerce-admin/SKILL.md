---
name: sf-woocommerce-admin
description: Add or modify WooCommerce product and variation admin fields — choosing ACF vs native WC hooks, the product_variation bridge pattern, admin asset enqueueing, and the CLI plus wp-admin verification loop. Use when working on product meta, variation fields, the Variations panel, Product data tabs, or a WooCommerce_Admin change.
---

# WooCommerce Admin Fields

## Precondition

Commerce is a swappable capability. Confirm it applies before proceeding:

```bash
cd <repo root>
ddev wp plugin list --status=active | rg woocommerce
```

If WooCommerce is absent, stop — say so and do not create commerce files. Check
`PROJECT.md` § Stack for the declared answer.

## Step 1 — ACF or native?

| Situation | Approach |
|---|---|
| Field on `product`, standard edit screen | ACF JSON in `acf-json/` |
| Field on `product_variation`, or in the Variations panel | **Native WC hooks** |
| Meta belonging in Product data tabs | Native WC hooks + WC UI components |
| Legacy storage format from a previous plugin | Same meta key; normalise on save only |

### Why variations can't use ACF

`product_variation` is registered with `show_ui: false`. Variations are edited
inside the parent product's **Variations** tab, loaded over AJAX. ACF has no
screen to bind to, so a `post_type == product_variation` location rule silently
renders nothing.

Bridge pattern — all three parts are required:

| Step | Hook |
|---|---|
| Render | `woocommerce_variation_options` |
| Save | `woocommerce_save_product_variation` |
| Admin JS re-init after AJAX | `woocommerce_variations_loaded` |

## Step 2 — place the code

Admin UI goes in the WooCommerce admin class. Frontend behaviour goes in the
archive or single-product class, not here. Nothing goes in `woocommerce/` — that
directory holds template overrides only.

Hold the meta key in a private class constant so the string appears once:

```php
private const META_KEY = 'existing_legacy_key';
```

## Step 3 — respect the legacy key and format

On a rebuild over an existing store, meta keys and formats are **data
contracts**. Never rename a key without an approved migration. Match the existing
format — serialized array, CSV, WYSIWYG HTML — rather than "improving" it.
Project-specific keys and formats are in the `project-*-meta` rule.

## Step 4 — hooks for the right screen

Product-level meta in the Linked Products area, for example:

| Step | Hook |
|---|---|
| Render | `woocommerce_product_options_related` |
| Save | `woocommerce_admin_process_product_object` |

## Step 5 — save safety

Variation fields POST as arrays keyed by variation ID:

```
field_name[{variation_id}]
```

**If the POST key is absent entirely, do not delete existing meta.** An absent
key means the Variations panel wasn't loaded on that request — not that the user
cleared the value. This is the single easiest way to destroy production data here.

Always check capability and sanitize:

```php
if ( ! current_user_can( 'edit_post', $post_id ) ) {
	return;
}
```

## Step 6 — admin assets

WooCommerce product screens do **not** load `build/index.css`. Small wp-admin UI
may enqueue static files from `assets/js/` and `assets/css/` with `filemtime()`
versioning, on `admin_enqueue_scripts`, restricted to product edit screens.

Frontend shop styles go through the Sass build in `src/sass/`, never here.

## Step 7 — verify, every time

Two passes, both required:

**CLI** — read the meta before and after, on a record with known data:

```bash
ddev wp post meta get <id> <key> --format=json
```

**wp-admin** — open that record and confirm the value displays, edits, and saves,
and that sibling meta on the same record is unchanged. For variation fields,
confirm the field survives the Variations panel's AJAX reload.

A field that saves correctly via CLI but not in wp-admin usually means the JS
re-init hook is missing.

## Anti-patterns

- ACF on `product_variation` expecting wp-admin visibility
- Renaming legacy meta keys without an approved migration
- Hooks or classes inside `woocommerce/`
- Commerce helpers in `includes/helper-functions.php` — that file can never be
  deleted, so anything there permanently welds commerce into the starter
- Deleting variation meta when the POST key is entirely absent
- Bulk data migration for schema-only work
- Applying WooCommerce **plugin-core** conventions — this is theme integration

## Related

- `.cursor/rules/sf-commerce.mdc` — conventions and removability
- `.cursor/rules/project-woocommerce-meta.mdc` — this store's keys and formats
- `.cursor/rules/sf-acf-fields.mdc` — ACF JSON conventions
- `context/kb/plugin-woocommerce-surface.md` — which files commerce owns
