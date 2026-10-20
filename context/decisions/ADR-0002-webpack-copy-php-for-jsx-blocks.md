---
status: Decided
date: 2026-09-11
---

# Blocks build copies every PHP file in the block folder

## Context

Default `wp-scripts` copies only the PHP file named by `block.json`'s
`render` (and `variations`) key. Sibling PHP was silently dropped, so
the theme treated that as a placement rule: inline everything in
`render.php`, or put helpers in `includes/{capability}/`. That was a
workaround for the copy behaviour, not a preference about where block
code should live.

`--webpack-copy-php` copies every `.php` file from the source tree into
the output tree.

## Options

1. Keep the default copy and the workaround rule.
2. Pass `--webpack-copy-php` on the blocks build only
   (`src-jsx-blocks/` → `build-jsx-blocks/`).
3. Pass it on both builds. Rejected: `build:assets` lists entries from
   the theme root, so the flag would dump the whole PHP tree into
   `build/`.

## Decision

Option 2.

Block-local PHP may now live next to `block.json` and will ship.
Cross-cutting code (hooks, REST, `lib/`, a second block) still belongs
in `includes/{capability}/`. This pass does not relocate existing
helpers.

## Consequences

`build:blocks` / `start:blocks` must keep `--webpack-copy-php`.
`build:assets` must never gain that flag. Docs that called the silent
drop a law of the pipeline need rewriting so the next author does not
re-learn the workaround.
