---
status: Decided
date: 2026-09-11
---

# JSX blocks register from a metadata collection

## Context

`lib/class-blocks-jsx.php` globs `build-jsx-blocks/*` and calls
`register_block_type()` once per folder. There are 38 JSX blocks.
`@wordpress/scripts` 30.14+ can emit `blocks-manifest.php` during the
blocks build, and WordPress 6.8+ can register that collection in one
call.

## Options

1. Keep the glob loop. Simple, already works, pays per-block
   `register_block_type()` cost on every request.
2. Register from `wp_register_block_types_from_metadata_collection()`
   when the manifest and the function exist, and keep the glob as a
   fallback for older WordPress.

## Decision

Option 2. The theme declares WordPress 7.1, so the collection APIs are
available. The fallback stays so a zip dropped onto an older install
still registers blocks.

## Consequences

`build:blocks` and `start:blocks` pass `--blocks-manifest`. The
generated `build-jsx-blocks/blocks-manifest.php` is tracked build
output, same as any other file under that directory.
