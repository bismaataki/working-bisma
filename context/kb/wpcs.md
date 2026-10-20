# WPCS — how this theme runs PHPCS

**PHPCS is one of four checks in `tools/verify.sh`**, which `hooks/pre-push`
runs. The gate already handles the host-versus-DDEV choice described below, so
use it when you want the whole check and `composer phpcs` when iterating on PHP
alone. Adding or scoping a rule means editing `phpcs.xml.dist`, not the gate.

**Status:** Adopted. Tooling and a scoped ruleset are in the theme.
Baseline cleanup of existing findings is **not** a prerequisite for new
work. New and touched PHP should not add to the baseline.

The 2026-08-26 adoption plan landed. This file is the current fact sheet.
Do not recreate a “there is no phpcs.xml” plan.

## Run

From the theme directory (host Composer preferred):

```bash
cd wp-content/themes/skaflow
composer phpcs
composer phpcbf   # only on files you are already touching
```

If `php` or `composer` is not on PATH, from the repo root:

```bash
ddev composer -d wp-content/themes/skaflow phpcs
ddev composer -d wp-content/themes/skaflow phpcbf
```

PHPCS does not need WordPress. Stubs live in `tools/phpcs-wp-stubs.php`.

## Config

- Ruleset: `wp-content/themes/skaflow/phpcs.xml.dist`
- Base: `WordPress-Extra` + `WordPress-Docs`
- Text domain: `skaflow`
- Prefixes: `skaflow`, `Skaflow`, `SKAFLOW`
- Dev packages: `squizlabs/php_codesniffer`, `wp-coding-standards/wpcs`,
  `phpcsstandards/phpcsextra`, `dealerdirect/phpcodesniffer-composer-installer`

## In scope (hand-maintained PHP)

`includes/`, `lib/`, `public/`, `partials/`, `functions.php`,
`src-jsx-blocks/`, `src-acf-blocks/`, `woocommerce/`, `bin/`.

The `<file>` list in `phpcs.xml.dist` is an **allow list**: a directory absent
from it is never scanned, whether or not it also appears in `exclude-pattern`.
`woocommerce/` (template overrides) and `bin/` (menu-seeder CLI scripts) sat
outside it until 2026-09-11 and had accumulated 69 findings that the gate
reported as clean. **Add new hand-written PHP to that list or nothing checks
it.** `bin/`, `tools/` and `config-dev/` are dev-only and excluded from the
release zip by `.npmignore`; that is a packaging decision and says nothing
about whether they are linted.

## Never lint

Gutenberg markup, not hand-formatted PHP:

- `templates/`
- `parts/`
- `patterns/`

Also excluded: `vendor/`, `node_modules/`, `build/`, `build-jsx-blocks/`,
`assets/`, `lib/plugins-zip/`, `tools/`.

`tools/` is a PHPCS exclusion only, and covers one stub file. Its JS and the
`config-dev/` browser-sync config are in `lint:js` scope, with a Node globals
override in `eslint.config.cjs`. CLI scripts write to `process.stdout`.

Hand-written Sass under `src/sass/patterns/` is not in that ban (stylelint,
not PHPCS).

## Safety stays hard-fail

Do not relax escaping, nonces, SQL, or capabilities to shrink the baseline.
The ruleset already drops low-value Extra noise (see the comments in
`phpcs.xml.dist`).

## Policy for new work

1. Do not run PHPCS or PHPCBF on `templates/`, `parts/`, or `patterns/`.
2. Only `phpcbf` files you are already touching.
3. Do not rewrite line endings as a side effect of a lint pass.
4. A full-theme “make phpcs pass” sweep is a scheduled job, not a
   side-effect of a feature.

## Related

- `AGENTS.md` — where commands run
- `wp-content/themes/skaflow/phpcs.xml.dist` — the live ruleset
- `wp-content/themes/skaflow/composer.json` — `phpcs` / `phpcbf` scripts
