# Helper Function Inventory — `includes/helper-functions.php`

Generated from the codebase. **Regenerate rather than trust** — see the command
at the bottom.

Purpose: stop agents reinventing a helper that already exists, and stop them
calling one that never existed.

This is the **always-loaded** generic helper file — it can never be
deleted, so anything capability-specific here is a defect, not a feature.

Commerce helpers live in `includes/woocommerce/woocommerce-functions.php`.
See `context/kb/plugin-woocommerce-surface.md`.

## Build paths

| Function | Signature | Returns |
|---|---|---|
| `skaflow_get_build_url` | `$file_name_with_sub_dir` | `string` — URL into `build/` |
| `skaflow_get_build_dir` | `$file_name_with_sub_dir` | `string` — filesystem path into `build/` |

Always use these rather than composing `get_theme_file_uri( 'build/...' )` by hand.

## Debug

| Function | Signature | Output |
|---|---|---|
| `skaflow_write_log` | `$log` | Appends to `wp-content/themes/skaflow/debug.log` |
| `skaflow_dump` | `$var` | `<pre>` block in the response |

Use these instead of `error_log()` or `var_dump()`.

## Media

| Function | Signature | Returns |
|---|---|---|
| `skaflow_get_post_thumbnail_mime_type` | `$post_id` | `string` |
| `skaflow_get_default_placeholder_image_id` | — | `int` — from Theme Options, General tab |

## Taxonomy and queries

| Function | Signature | Returns |
|---|---|---|
| `skaflow_get_post_count_for_terms_and_post_types` | `array $term_ids, array $post_types, string $taxonomy` | `int` |

Primary term lives in `includes/taxonomy-terms-functions.php`
(`skaflow_get_primary_term_id_for_post()`), not here.

## Markup and attributes

| Function | Signature | Returns |
|---|---|---|
| `skaflow_get_buttons_markup` | `array $buttons, array $args = []` | `string` — real `core/buttons` via `do_blocks()` |
| `skaflow_get_button_icon_class_names` | `string $icon_slug, string $icon_position = 'left'` | `string` |
| `skaflow_build_html_attributes_string` | `array $attributes` | `string` |
| `skaflow_sanitize_html_classes` | `string $classes` | `string` |
| `skaflow_render_print_button` | `string $content, array $block` | `string` — `render_block_core/button` filter |
| `skaflow_render_site_header` | — | `void` — echoes the PHP header shell |

`skaflow_get_buttons_markup()` is the **only** approved way to output buttons
from PHP. Reference implementation: `lib/class-load-more.php`. Avoid it in tight
loops — `do_blocks()` has a per-call cost.

## Generic utilities

| Function | Signature | Returns |
|---|---|---|
| `skaflow_insert_array_at_position` | `$array, $insert, $position` | `array` |
| `skaflow_trim_filename` | `string $name, int $limit = 40` | `string` |

## Capability helpers live elsewhere

This file is generic utilities only. Capability functions are in
`includes/{capability}/` — `woocommerce/`, `post/`, `faq/`, `showroom/`. Search
there before adding anything product-, review-, or commerce-related.

Feature → file start points: `context/kb/map-edit.md`.
What boots: `context/kb/map-load.md`.

## Regenerate

```bash
cd wp-content/themes/skaflow
rg -n '^function skaflow_|^\tfunction skaflow_' includes/helper-functions.php
rg -o '^function (skaflow_[a-z_0-9]+)' -r '$1' includes/*.php includes/*/*.php | sort
```

## Related

- `.cursor/rules/sf-php-architecture.mdc` — where functions belong and why
- `context/kb/plugin-woocommerce-surface.md` — the commerce strip manifest
- `context/kb/map-edit.md` — where to start for a given surface
