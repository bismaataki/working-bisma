---
name: sf-blocks
description: Create or modify Gutenberg blocks in this theme — choosing JSX vs ACF, scaffolding files, deciding static save.js vs dynamic render.php, and where block PHP is allowed to live. Use when asked to build a new block, add an attribute or InspectorControl, convert a block between static and dynamic, or fix a block that isn't registering or rendering.
---

# Creating Blocks

Two systems, both auto-registered by directory.

| | JSX blocks | ACF blocks |
|---|---|---|
| Source | `src-jsx-blocks/{name}/` | `src-acf-blocks/{name}/` |
| Build | `npm run build:blocks` → `build-jsx-blocks/` | none — served from source |
| Registered by | `lib/class-blocks-jsx.php` | `lib/class-acf.php` |
| Options UI | React `InspectorControls` | ACF field group |
| Per-block Sass | yes (`style.scss`, `editor.scss`) | no — use `src/sass/` |

**Default to JSX.** Reach for ACF only when the block is a simple content
container whose options are better authored as ACF fields, and no custom editor
interaction is needed.

## Step 1 — decide static or dynamic

| Use static (`save.js` renders) | Use dynamic (`render.php` renders) |
|---|---|
| Content fully known at edit time | Content depends on the database, user, or request |
| No server data needed | Needs WordPress functions or plugin APIs |
| Best performance — no PHP at render | Output varies by context |

A static block's `save.js` returns markup. A dynamic block's `save.js` returns
`null` and `block.json` declares `"render": "file:./render.php"`.

## Step 2 — scaffold

```bash
cd wp-content/themes/skaflow
mkdir src-jsx-blocks/my-block
```

| File | Required | Purpose |
|---|---|---|
| `block.json` | yes | Metadata, `apiVersion: 3` |
| `index.js` | yes | `registerBlockType` |
| `edit.js` | yes | Editor component |
| `save.js` | yes | Markup, or `null` when dynamic |
| `render.php` | dynamic only | Server render |
| `view.js` | optional | Frontend behaviour |
| `style.scss` / `editor.scss` | optional | Only if the block needs its own styles. Load tokens with `@use "../../src/sass/tools" as *` — never `@import` settings/mixins. |

ACF blocks need `block.json`, `render.php`, and `index.php`.

Name the block `skaflow/{name}` in `block.json`, matching the directory.

## Step 3 — where block PHP goes

`build:blocks` passes `--webpack-copy-php`, so every `.php` file beside
`block.json` is copied into `build-jsx-blocks/{name}/`. Default
`wp-scripts` copies only the `render` file; the flag lifts that.

- **Block-local PHP may live next to `block.json`.** A helper only this
  block calls can be its own file in the folder. The folder stays
  deletable as a unit.
- **Shared logic goes in `includes/{capability}/`**, `require_once`'d
  from that directory's `bootstrap.php`, and `render.php` calls it as a
  thin shim.

Shared means consumed by any of: a PHP hook, a REST route, a `lib/` class, or a
second block. Do not relocate existing `includes/` helpers into a block
folder just because the copy flag exists.

In `render.php`, use `get_block_wrapper_attributes()` for the wrapper, and pass
the post ID explicitly when reading post fields:

```php
$block_setting = get_field( 'block_setting' );              // block's own group
$post_setting  = get_field( 'post_setting', get_the_ID() ); // the post
```

## Step 4 — frontend JS

If the block needs behaviour, add `view.js` and declare `viewScript` in
`block.json`. Anything that touches a card, swatch, or grid item **must be
delegated** — see `.cursor/rules/sf-event-delegation.mdc`. Direct
`addEventListener` inside a grid works on page 1 and breaks silently after Load
More.

## Step 5 — build and verify

```bash
npm run build:blocks     # or npm run build
```

Then confirm registration:

```bash
ddev wp eval 'foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $n => $b ) { if ( str_starts_with( $n, "skaflow/" ) ) echo "$n\n"; }'
```

## Plugin-dependent blocks

Registration is directory-driven with **no plugin-dependency check** — a block
requiring WooCommerce still registers on a site without it. Guard inside
`render.php`, and record the dependency so a fork can strip the block cleanly.

## When a block doesn't work

| Symptom | Check |
|---|---|
| Not in the inserter | Build completed? `build-jsx-blocks/{name}/block.json` present? Object cache cleared? |
| Blank on frontend | Is `save.js` returning `null` without a `render` key in `block.json`? |
| Fatal on render | Missing `require` path, or a helper that lives only in `src-jsx-blocks/` was not rebuilt (`npm run build:blocks`) |
| Editor JS error | Check `viewScript` / `editorScript` paths in `block.json` |
| Styles missing in editor only | Content styles need `enqueue_block_assets`, not `enqueue_block_editor_assets` |

## Conventions

- Functional components with hooks; no class components
- `@wordpress/components` and `@wordpress/block-editor` over hand-rolled UI
- Wrap user-facing strings in `@wordpress/i18n` with the `skaflow` text domain
- Escape all output in `render.php`

## Related

- `.cursor/rules/sf-build-pipeline.mdc` — the copy trap and enqueue rules
- `.cursor/rules/sf-acf-fields.mdc` — field groups for ACF blocks
- `.cursor/rules/sf-php-architecture.mdc` — capability-named function files
