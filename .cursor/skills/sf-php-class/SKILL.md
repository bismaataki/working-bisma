---
name: sf-php-class
description: Add a new PHP feature class to this theme, or decide between a class and a function file. Use when asked to build a PHP feature, register hooks, add an admin screen or REST route, or when a newly added class isn't loading.
---

# Adding a PHP Class

## First — is a class the right shape?

| Shape | Home |
|---|---|
| Registers hooks, holds state, owns a feature | `lib/class-{name}.php` |
| REST endpoint | `public/class-api-{concern}.php`, extending `Api` |
| Stateless functions for one capability | `includes/{capability}/{name}-functions.php` |
| Hook wiring for a capability | `includes/{capability}/{name}-hooks.php` |
| Genuinely generic machinery | `includes/{name}-functions.php` (root) |

Don't create a class to hold two static helpers. Don't put hook registration at
file scope in a function file when the feature has any state.

**Adding a REST endpoint is a different procedure** — it extends the abstract
`Api` base class, gates itself through `is_available()` rather than a constructor
bail, and is registered from a list rather than its own `define_*_hooks()` method.
Read `.cursor/rules/sf-rest-api.mdc` and copy
`public/class-api-product-search.php` as the smallest working example. The rest of
this skill covers ordinary feature classes.

## Step 1 — write the class

`lib/class-custom-feature.php`:

```php
<?php

namespace Happywood;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'Happywood\Custom_Feature' ) ) {
	return;
}

/**
 * One-line description of what this feature owns.
 */
class Custom_Feature {

	/**
	 * Legacy meta key — never rename without an approved migration.
	 */
	private const META_KEY = 'existing_key';

	public function __construct() {
		$this->register_hooks();
	}

	private function register_hooks(): void {
		add_action( 'init', [ $this, 'register_thing' ] );
		add_filter( 'the_content', [ $this, 'filter_content' ] );
	}

	public function register_thing(): void {
		// ...
	}

}
```

The file name must match the class name exactly — the autoloader is a `classmap`
over `lib` and `public`, and a case mismatch fails silently on Linux.

## Step 2 — guard external dependencies

If the feature needs a plugin, bail in the constructor so the theme degrades
instead of fataling:

```php
public function __construct() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}
	$this->register_hooks();
}
```

## Step 3 — register from `class-init.php`

Add a private method and call it from the constructor. Nothing instantiates
theme classes anywhere else:

```php
// lib/class-init.php
public function __construct() {
	// ...existing define_* calls...
	$this->define_custom_feature_hooks();
}

/**
 * Register hooks for <feature>.
 *
 * @access private
 */
private function define_custom_feature_hooks(): void {
	new Custom_Feature();
}
```

Keep a property only if something else needs to reach the instance — most classes
just need `new Custom_Feature();`:

```php
private $blocks;
// ...
$this->blocks = new Blocks_Jsx();
```

## Step 4 — regenerate the autoloader

```bash
cd wp-content/themes/happywood
composer dump-autoload
```

Skipping this is the most common reason a new class "doesn't exist".

## Step 5 — verify it loaded

```bash
cd <repo root>
ddev wp eval 'var_dump( class_exists( "Happywood\\Custom_Feature" ) );'
ddev exec tail -20 wp-content/themes/happywood/debug.log
```

Add a temporary `happywood_write_log( __METHOD__ );` inside a hook callback if
you need to confirm the hook actually fires.

## Naming and constants

- Class names: `Title_Case_With_Underscores`, matching the file
- Functions and hooks: `happywood_` prefix
- Constants: `HAPPYWOOD_` prefix, declared in `includes/constants.php`
- Build custom hook and nonce names from `HAPPYWOOD_PREFIX` rather than
  hardcoding the string, so a theme rename stays mechanical

```php
wp_create_nonce( HAPPYWOOD_PREFIX . 'custom_action' );
do_action( HAPPYWOOD_PREFIX . 'thing_happened' );
```

## Keeping the class removable

Name the class and its files after the **capability**, and register it from one
`define_*_hooks()` entry point. A capability that a future project doesn't need
should be removable by deleting its files and one line from `class-init.php`.

Never place capability-specific helpers in `includes/helper-functions.php` — that
file is always loaded and can never be deleted.

## Related

- `.cursor/rules/sf-php-architecture.mdc` — the full convention
- `.cursor/skills/sf-debug/` — when it isn't loading
