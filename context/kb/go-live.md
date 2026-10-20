# Go-live tasks

Environment standup for this theme. Keep this list current while the theme
is built. Feature work stays in `plans/` / `implementation/`; this file is
what you run on a fresh install, a staging clone, or production.

**This copy (skaflow):** DDEV at `https://skaflow.test`, PHP 8.4, WordPress 7.1.
The theme is active. ACF Pro, Gravity Forms, Icon Block, Heroic Table of
Contents, and WooCommerce are all installed and active. There is no WP Engine
remote yet. When a future project deploys to WP Engine, follow
`context/kb/sop-github-to-wpengine-gitpush.md` and add that project's
environment names here.

## Local (every clone)

Git tracks the theme, the AI tooling, and `.ddev/config.yaml`. It does **not**
track WordPress core, `wp-config.php`, or `wp-content/plugins/`, so a fresh clone
needs steps 1 and 2 before anything will boot.

0. **Node inside `^20.19.0 || ^22.13.0 || >=24.0.0`.** `nvm use` in the theme
   directory reads `.nvmrc`, which names 24. `npm install` refuses anything
   outside the window (`.npmrc` sets `engine-strict=true`) and
   `tools/verify.sh` re-checks, because `npm run` does not.

   The window comes from what the dependency tree declares, not from
   preference — odd majors are excluded by `glob` and the jest packages. Build
   output is byte-identical across 20, 22 and 24, so any in-range version is
   fine; there is no need to match a colleague exactly.
1. Download core into the repo root: `ddev start` then
   `ddev wp core download --skip-content`, or copy an existing install.
2. `ddev wp core install --url=https://<project>.test --title=<Project>
   --admin_user=admin --admin_email=dev@example.com`
3. Theme directory: `composer install` and `npm install && npm run build`.
4. Activate the theme: `ddev wp theme activate skaflow`.
5. Install TGMPA plugins (Appearance → Install Plugins): **ACF Pro**,
   **Gravity Forms** (zips in `lib/plugins-zip/`), **Icon Block**, **Heroic
   Table of Contents**.
6. Install and activate **WooCommerce** when the catalog is needed.
7. Flush permalinks: `ddev wp rewrite flush`.
8. Confirm `Skaflow\Init` loads:
   `ddev wp eval 'var_dump( class_exists( "Skaflow\\Init" ) );'`
8a. **Activate the pre-push gate**, from the repo root:

   ```bash
   git config core.hooksPath hooks
   wp-content/themes/skaflow/tools/verify.sh   # confirm it passes
   ```

   Git does not clone `.git/hooks/`, so this is per clone and per developer, and
   it is silently absent until someone runs it. The gate itself is
   `tools/verify.sh`: build freshness, both linters, PHPCS.
9. Build the navigation. A fresh clone has no menus, so the header nav row
   renders empty until this is done. `.cursor/skills/sf-setup-menus/` owns the
   procedure and the choice of source; the short version is:

   ```bash
   # Derive from the catalogue (needs product categories to exist)
   ddev wp eval-file wp-content/themes/skaflow/bin/setup-primary-nav-from-content.php

   # Or build a designed tree, after filling in SKAFLOW_MENU_SETUP_TERMS
   ddev wp eval-file wp-content/themes/skaflow/bin/setup-primary-nav-menu.php
   ```

   The footer nav needs nothing. It is inline block markup in
   `patterns/footer.php`, not a WordPress menu, so it renders immediately.

## Staging / production (when an environment exists)

1. Deploy the theme (and any project-owned plugins). See the GitPush SOP
   if the host is WP Engine.
2. Repeat TGMPA + WooCommerce activation on that environment.
3. Set permalinks, WooCommerce shop / cart / checkout / account pages.
4. Import Gravity Forms if the project has them.
5. Assign Primary and Utility menus (`skaflow-primary-nav`,
   `skaflow-utility-nav`).
6. Fill Theme Options (header, FAQ Configurations, Banner Configurations).
7. Spot-check header, shop (if Woo is on), FAQ archive, showrooms, account,
   cart.

## Required plugins (TGMPA)

| Plugin | Source | Notes |
|---|---|---|
| ACF Pro | Bundled zip | Fields, options, mega-menu item fields |
| Gravity Forms | Bundled zip | Contact / forms. Create form 1 (contact) then form 2 (subscribe) so the pattern embeds resolve |
| Icon Block | wordpress.org | Standalone icons |
| Heroic Table of Contents | wordpress.org | Content TOC |

WooCommerce is the declared commerce implementation. It is not in TGMPA.
Install it when the catalog is needed. Rank Math is the declared SEO
plugin; add it when SEO work starts.

## Do not treat as required on this starter

No companion plugins (custom shipping rates, data normalisation, hosted
payments) ship with the starter. YITH Request a Quote is optional and
guarded; add it only when a project needs quotes. If a project needs a
companion plugin, record it here and in `PROJECT.md` § Plugins.

One feature is dormant because of this: the PDP delivery-estimate card posts to
an AJAX action no bundled plugin registers. It renders and returns the error
state. `includes/woocommerce/delivery-estimate-functions.php` documents the
contract, and its docblock lists the three files to delete to drop the feature.

## Keep this file updated

When you add a plugin, an options page, a required page, or a deploy
target, add a row. When a step is done on this copy, mark it in
`PROGRESS.md` and leave the instruction here for the next environment.
