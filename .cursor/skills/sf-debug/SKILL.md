---
name: sf-debug
description: Diagnose theme problems — missing build output, blocks not registering, ACF fields not appearing, classes not loading, styles missing in the editor, and reading runtime state through DDEV WP-CLI. Use when something is broken, blank, or not appearing and the cause isn't obvious.
---

# Debugging

## Ground rules

PHP and WP-CLI run through DDEV **from the repo root**. Host `php` and `wp` have
no WordPress bootstrap:

```bash
cd <repo root>
ddev wp option get <name>
ddev wp eval '...'
ddev exec tail -40 wp-content/themes/happywood/debug.log
```

npm runs from the theme directory. Mixing these up produces confusing failures
that look like code bugs.

Use the theme's logger, not `error_log()`:

```php
happywood_write_log( $thing );   // → wp-content/themes/happywood/debug.log
happywood_dump( $thing );        // → <pre> in the response
```

Query Monitor answers most questions about queries, hooks, and HTTP requests
faster than logging does. It is **not** bundled, so check whether it is installed
before recommending it:

```bash
wp plugin list --status=active | rg -i 'query-monitor'   # or: ddev wp plugin list ...
```

`includes/integrations/query-monitor.php` exists regardless — it is a small
deprecation filter, not evidence the plugin is present.

## Work outside in

Establish which layer is broken before changing code.

1. **Is the asset built?** Check `build/` and `build-jsx-blocks/` timestamps.
2. **Is the thing registered?** Query the registry, don't infer from the UI.
3. **Is the hook firing?** Log inside the callback.
4. **Is the data there?** Read the meta or option directly.

## Missing or stale build output

```bash
cd wp-content/themes/happywood
ls -la build/ build-jsx-blocks/
npm install && npm run build
```

`build/` should hold `index.js`, `index.css`, `index-rtl.css`, `index.asset.php`,
`public.js`, `public.css`, `public-rtl.css`, `public.asset.php`. Each built block
directory should hold `block.json`, `index.js`, `index.asset.php`, CSS, and
`render.php` if the block is dynamic.

**A helper PHP file beside `block.json` should appear in `build-jsx-blocks/`
after `npm run build:blocks`.** That command passes `--webpack-copy-php`.
If the helper is missing from the build, the blocks script was run without
the flag, or the file is new and the build is stale. Shared helpers still
belong in `includes/{capability}/`.

## Blocks not registering

```bash
ddev wp eval 'foreach ( WP_Block_Type_Registry::get_instance()->get_all_registered() as $n => $b ) { if ( str_starts_with( $n, "happywood/" ) ) echo "$n\n"; }'
```

If a block is absent: build didn't run, `block.json` has a syntax error, or an
object cache is holding the old registry. Registration is directory-driven from
`build-jsx-blocks/`, so a source-only block never registers.

For ACF blocks, confirm ACF is loaded first:

```bash
ddev wp eval 'var_dump( function_exists( "acf_register_block" ) );'
```

## ACF fields not appearing

Check in this order:

1. Location rules on the field group — the usual cause
2. Group is active
3. Field `name` matches the code exactly

```bash
ddev wp eval 'foreach ( acf_get_field_groups() as $g ) echo $g["key"] . " — " . $g["title"] . "\n";'
```

A `post_type == product_variation` location rule will never render — that post
type has `show_ui: false`. See `.cursor/rules/sf-commerce.mdc`.

## Class not loading

```bash
ddev wp eval 'var_dump( class_exists( "Happywood\\Your_Class" ) );'
```

Causes, in order of likelihood: `composer dump-autoload` not run; file name
doesn't match the class name exactly; the class is never instantiated from
`lib/class-init.php`; a dependency guard is returning early.

## Styles present on frontend, missing in the editor

Content styles must enqueue on `enqueue_block_assets`, which loads inside the
editor iframe and on the frontend. `enqueue_block_editor_assets` loads only in
the editor chrome. If WordPress logs `"...-style-css was added to the iframe
incorrectly"`, this is the cause. See `.cursor/rules/sf-build-pipeline.mdc`.

## Icon glyphs render as blank boxes

Usually a partial addition — the slug exists in one surface but not all of them.
Check every file in the icon file map, then verify the compiled bytes survived
minification rather than trusting how the character renders in an editor. See the
project's `project-*-icons` rule.

## Reading live data

```bash
ddev wp post list --post_type=product --fields=ID,post_title --format=table
ddev wp post meta get <id> <key> --format=json
ddev wp option get <name>
ddev wp db query "SELECT option_value FROM wp_options WHERE option_name='<name>'"
ddev wp plugin list --status=active
```

## Interactions breaking after Load More or filtering

The handler was bound directly instead of delegated. It works on page 1 and dies
on appended nodes, with no console error. See
`.cursor/rules/sf-event-delegation.mdc`.

## Before concluding it's a theme bug

Confirm the required plugins are active, and check `PROJECT.md` § Known debt —
some symptoms are recorded, deliberate, and not worth re-diagnosing.
