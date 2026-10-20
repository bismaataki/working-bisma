---
name: sf-plugin-integrations
description: Add a third-party plugin integration to this theme — capability-named hook files, guarding, and a catalogue of known small integrations (Yoast breadcrumbs, JVM Rich Text Icons asset removal). Use when asked to integrate, customise, or suppress output from a third-party plugin.
---

# Plugin Integrations

## The pattern

One file per plugin, named after the plugin, so the integration is removable as a
group.

**Step 1** — create `includes/integrations/{plugin}.php`:

```php
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'plugin_hook_name', 'skaflow_plugin_integration' );

/**
 * Describe what this changes and why.
 */
function skaflow_plugin_integration( $value ) {
	// ...
	return $value;
}
```

**Step 2** — `require_once` it from `includes/integrations/bootstrap.php`.

**Step 3** — guard on the plugin. A theme must not fatal because a plugin was
deactivated:

```php
if ( ! function_exists( 'plugin_function' ) ) {
	return;
}
```

For anything stateful, use a `Skaflow\` class in `lib/` instead, guarded in the
constructor. See `.cursor/skills/sf-php-class/`.

**Never** put plugin-specific code in `includes/helper-functions.php`. That file
is always loaded and can never be deleted, so anything there permanently welds the
integration into the starter.

## Verify before writing

```bash
cd <repo root>
ddev wp plugin list --status=active
```

Confirm the plugin is actually installed and active. Several integrations
described in this starter's lineage target plugins that aren't present on every
build — writing hooks for an absent plugin produces silently dead code.

## Catalogue — known small integrations

Carried forward from sibling builds. Each is a single filter; re-add only if the
plugin is active on the current project.

### Yoast SEO — suppress breadcrumbs on the home page

```php
add_filter( 'wpseo_breadcrumb_output', 'skaflow_no_breadcrumbs_for_home_page' );

function skaflow_no_breadcrumbs_for_home_page( $output ) {
	return is_front_page() ? '' : $output;
}
```

### JVM Rich Text Icons — drop the bundled Font Awesome stylesheet

Relevant when the theme supplies its own icon set and the plugin's Font Awesome
would be a duplicate payload:

```php
add_filter( 'jvm_richtext_icons_css_file', '__return_false' );
```

## Larger integrations

Card systems, filtering, and commerce each have their own skill, because they own
directories rather than a single filter:

- `.cursor/skills/sf-cards-blokki/`
- `.cursor/skills/sf-filters-wpgb/`
- `.cursor/skills/sf-woocommerce-admin/`

## Plugin requirements

Required and recommended plugins are declared through TGMPA in
`lib/class-plugins-manager.php`, filterable via `skaflow_tgmpa_plugins` and
`skaflow_tgmpa_config`. Add a plugin there if the theme genuinely depends on
it — not for an optional enhancement.

## Related

- `.cursor/rules/sf-php-architecture.mdc` — capability-named files, guarding
- `PROJECT.md` § Plugins — what's active on this build
