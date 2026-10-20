# Happywood

Happywood Theme Boilerplate with Namespace and Gutenberg Block support

## Changes Required to start Development

1. Update Theme Comment Block in main file `/style.css`

2. After Adding more files as you go, use composer to update autoload if you need to. You shall need to have composer
   installed on your computer. In Terminal in the plugin directory, run following:
    * `composer update`

3. To install NPM dependencies, run the following command:
    * `npm install`

4. Create `.env` file for local server proxy
    * The file `.env` should be created under `\config-dev` directory
    * The local dev server proxy should be added like this: `PROXY=mylocalsite.test`

5. After doing all the magic of coding, run the **full** build (never a
   single-entry `wp-scripts` shortcut — that wipes other `build/` outputs):
    * `npm run build`

6. While developing, use browser-sync plus both watchers:
    * `npm run dev`
    * Asset-only watch: `npm run start:assets`. Do not run `wp-scripts start` on one file.

7. PHP lint (WPCS) — should pass. PHPCS does not need WordPress; run Composer
   from this theme directory:
    * `composer phpcs`
    * `composer phpcbf` — only on files you are already touching
    * If `php` or `composer` is not on your PATH, from the repo root:
      `ddev composer -d wp-content/themes/happywood phpcs` (or the same via Docker)
    * Never run PHPCS or PHPCBF on `templates/`, `parts/`, or `patterns/`.

8. To Updates WordPress packages to the latest version:
    * `npm run packages-update`

9. Complete list of commands can be found
   here: [https://www.npmjs.com/package/@wordpress/create-block](https://www.npmjs.com/package/@wordpress/create-block)

## Dependencies

1. Node: `24.x` (the version DDEV's web container ships)
2. PHP: `8.4` — matches `.ddev/config.yaml`. `8.2` is the floor.

If the host has no `php`/`composer`, every Composer script also runs through
DDEV from the repo root. See `AGENTS.md` § Where commands run.
