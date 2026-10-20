---
plan: 2026-09-11-wordpress-scripts-35
shipped: 2026-09-11
decisions: [ADR-0001, ADR-0002]
---

# @wordpress/scripts 35

Bumped `@wordpress/scripts` 31.3.0 → 35.0.0 and ran `packages-update`.
ESLint moved from `.eslintrc.js` to `eslint.config.cjs` using
`recommended-with-formatting` so TypeScript-eslint stays unloaded.
`@wordpress/stylelint-config` is a direct devDependency so Stylelint 17
can resolve `scss-stylistic`.

`build:blocks` / `start:blocks` now pass `--webpack-copy-php` and
`--blocks-manifest`. `lib/class-blocks-jsx.php` registers from
`build-jsx-blocks/blocks-manifest.php` when WordPress 6.8+ is present,
and keeps the per-folder loop as a fallback. 38 blocks in the manifest;
all `skaflow/` types still register.

`lint:pkg-json` is in `tools/verify.sh`. `engines` sits after `bugs` so
that check passes.

Existing `includes/` helpers were not moved. The copy flag unlocks
block-local PHP; it does not require a relocation.

`npm audit --omit=dev` is down from five toolchain advisories to two
(`fast-uri`, `js-yaml`).
