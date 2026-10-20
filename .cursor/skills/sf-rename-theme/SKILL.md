---
name: sf-rename-theme
description: Rename this theme when forking it into a new project — substitutes the slug, namespace, function/constant prefixes, short CSS prefix, text domain, block and pattern slugs across the codebase and AGENTS.md, then resets PROJECT.md. Use when asked to rename the theme, fork the starter, or set up a new project from this codebase.
---

# Renaming the Theme

Turns a copy of this starter into a new project. Several thousand identifiers
across six naming conventions — mechanical, but only if done completely. A
partial rename leaves a theme that boots and then fatals somewhere non-obvious.

**Never trust a count written in this file.** Blocks, patterns, and helpers are
added constantly, so any number here is wrong by the time you read it. Measure
the surface yourself before and after, and treat the two numbers as the gate.

## Step 0 — ask whether this fork splits repositories

The starter is a single repository: theme, WordPress install, and AI tooling
(`AGENTS.md`, `PROJECT.md`, `context/`, `.cursor/`) committed together. Many
client projects need the AI tooling kept private in a separate repository while
the theme goes to a client remote. **This skill owns that decision, and it is the
only place it gets made.**

Ask the user directly, before renaming anything:

> Does this fork keep the AI tooling in the same repository as the theme, or
> split it into a separate private repository?

- **Same repository** (default, simplest): change nothing structural. `setup.sh`
  and `hooks/client-pre-push` stay inert. Skip to Inputs.
- **Split**: the theme repo becomes the client repo and the AI tooling moves to
  its own. Run `./setup.sh /path/to/client-repo` to symlink the tooling in and
  install the pre-push guard, then add the two-repo rules to the new
  `AGENTS.md` § Git as described in
  `context/kb/sop-client-code-ai-tools-repository-separation.md`. That section
  currently states the single-repo rule, so it must be rewritten, not appended to.

Do not infer the answer from the presence of `setup.sh`. It ships with the
starter and is unused until someone chooses the split.

## Inputs — ask for both

`PROJECT.md`'s Identity table declares the current values. You need two new ones:

| Input | Example | Notes |
|---|---|---|
| Full slug | `brayco` | Lowercase, no spaces. Drives namespace, prefixes, text domain |
| Short CSS/JS prefix | `bc-` | **Not derivable** from the slug — must be asked for |

Derive the rest:

| Convention | From `skaflow` → `brayco` |
|---|---|
| Namespace | `Skaflow\` → `Brayco\` |
| Function prefix | `skaflow_` → `brayco_` |
| Constant prefix | `SKAFLOW_` → `BRAYCO_` |
| Kebab slugs | `skaflow-` → `brayco-` |
| Block / pattern namespace | `skaflow/` → `brayco/` |
| Text domain | `'skaflow'` → `'brayco'` |
| Short prefix | `sk-` → `bc-` (declared, not derived) |

Confirm all seven with the user before touching anything.

## Order matters

Do the **longest, most specific patterns first**. Renaming `skaflow` before
`skaflow_` leaves mangled identifiers.

1. `SKAFLOW_` (constants)
2. `Skaflow\` (namespace) and `Skaflow` (bootstrap function, class refs)
3. `skaflow_` (functions and hooks)
4. `skaflow/` (block and pattern slugs)
5. `skaflow-` (kebab slugs, menu locations, image sizes, admin page slugs)
6. `'skaflow'` (text domain)
7. `skaflow` (remaining bare occurrences — check each)
8. `sk-` → the new short prefix

## Measure the surface first

Run this before substituting anything and save the output. Re-run it after, and
every line must read `0` except the short-prefix rows, which move to the new
prefix. This is the completeness gate — a table in a document is not.

```bash
cd wp-content/themes/skaflow

# -I skips binaries. Without it the release zip and the bundled plugin zips in
# lib/plugins-zip/ report "binary file matches" and pollute every count.
EX=(-I --exclude-dir={node_modules,vendor,build,build-jsx-blocks}
    --exclude='*.zip' --exclude=package-lock.json --exclude=composer.lock)

for pat in 'SKAFLOW_' 'Skaflow' 'skaflow_' 'skaflow/' 'skaflow-' 'skaflow'; do
  printf '%-12s %s\n' "$pat" "$(grep -rF "$pat" . "${EX[@]}" | wc -l)"
done

# Short CSS/JS prefix. Every directory, not just src/ and patterns/.
grep -roh '\bsk-' . "${EX[@]}" | wc -l

# Filenames, which no search-and-replace will touch. `skaflow.zip` is build
# output, not a file to rename — `npm run create-zip` renames it via package.json.
find . -name '*skaflow*' ! -name '*.zip' \
  -not -path './node_modules/*' -not -path './vendor/*' -not -path './build*'
```

## Surface map

Locations, not counts. Verify each group after substituting.

### PHP

| Target | Where |
|---|---|
| `namespace Skaflow;` | every file in `lib/` and `public/` |
| Composer `classmap` paths | `composer.json` — `lib` and `public`; run `composer dump-autoload` after renaming |
| Bootstrap `function Skaflow()` + `Skaflow\Init::get_instance()` | `functions.php` |
| Constants | defined in `includes/constants.php`, called throughout |
| REST namespace `skaflow/v1` | `Api::REST_NAMESPACE` in `public/class-api.php`; consumers in `lib/class-front.php` (localized) and `src/js/product-search.js` |
| Function definitions and calls | `includes/`, `lib/`, `public/`, `partials/`, and block `render.php` files |
| Text domain in `__()` / `esc_html__()` | everywhere a string is translated |
| TGMPA `id` and `menu` slugs | `lib/class-plugins-manager.php` |
| ACF options page `menu_slug` values | `lib/class-acf.php` — and every field group JSON targeting them |
| Filter/action names | `skaflow_init_construct`, `skaflow_tgmpa_plugins`, `skaflow_tgmpa_config` |
| Registered image sizes | e.g. `skaflow-menu-thumb` |

**Three PHP directories are easy to miss** because they are neither `lib/` nor
`includes/`. All three are in the PHPCS scope, so skipping them turns every
subsequent `composer phpcs` run red:

| Target | Note |
|---|---|
| `partials/` | `get_template_part()` targets, not autoloaded |
| `bin/` | WP-CLI setup scripts. Excluded from the release zip but still real code |
| `woocommerce/` | WooCommerce template overrides. Only exists on a commerce build |

### Blocks

| Target | Where |
|---|---|
| Block `name` | every `block.json` in `src-jsx-blocks/` and `src-acf-blocks/` |
| `registerBlockType` calls | each block's `index.js` |
| `textdomain` in `block.json` | all blocks |

Delete `build-jsx-blocks/` and rebuild rather than renaming inside it.
`src-acf-blocks/` has no build step, so its `render.php` and `block.json` are the
shipped files and must be substituted directly.

### Patterns and templates

| Target | Where |
|---|---|
| `Slug: skaflow/...` headers | every file in `patterns/` |
| `wp:pattern {"slug":"skaflow/..."}` cross-references | `patterns/`, `templates/`, `parts/` |
| `theme":"skaflow"` in `wp:template-part` | `templates/`, `parts/` |
| Theme-registered pattern category slug | `lib/class-after-setup-theme.php` + every pattern header declaring it |

### CSS and JS

| Target | Where |
|---|---|
| Short prefix classes | `src/`, `assets/`, `includes/`, `lib/`, `src-jsx-blocks/`, `patterns/`, `templates/`, `parts/`, `tools/`. The two heaviest are `src/` and `src-jsx-blocks/`; `assets/` is the one most often missed because the icon font CSS lives there |
| Sass icon variables | `$sk-icon-*`, `sk-icon-button` mixin in `src/sass/icon-font/` |
| Icon font `content` classes | `assets/css/button-icon-font.css` |
| Custom event names | `skaflow:content-updated` in `src/js/shared/delegated-events.js` and consumers |

### Config and metadata

| Target | Where |
|---|---|
| Theme header | `style.css` — Name, Description, Author, Author URI, Text Domain. Keep `Version` in step with `package.json`. Author is identity, not a leftover agency |
| `composer.json` | `name` (`ataki/skaflow` → `client/slug`), `authors`, `description` |
| `package.json` | `name`, `description`, `author`, `homepage`, `bugs`, `repository.directory`. `name` is also the `create-zip` filename (`skaflow.zip`) |
| **`phpcs.xml.dist`** | `<ruleset name>`, the `text_domain` property, and the three `PrefixAllGlobals` prefixes. **Miss this and every function fails PHPCS after the rename** |
| Zip filename rules | both `.gitignore` and `.npmignore` — `skaflow.zip` / `skaflow-*.zip` |
| Language files | `languages/` — regenerate with `npm run build:makepot`, do not hand-edit |
| DDEV project name | `.ddev/config.yaml` (`name`, and the `.test` hostname) — plus `ddev restart` |
| Debug log path | `includes/helper-functions.php` writes to the theme root; no change needed, but the path in `AGENTS.md` § Debugging is spelled out |

### Filenames

Search-and-replace edits file *contents*. These carry the old slug in the
**name** and must be renamed with `git mv`, then every reference to them updated.
Enumerate them with the `find` command in the measurement step rather than
working from a list, since new ones appear over time. Current shape:

| Kind | Example | Referenced from |
|---|---|---|
| Function files | `includes/menu/skaflow-lucide-icon-choices.php` | that capability's `bootstrap.php` |
| Template part targets | `includes/menu/skaflow-menu-thumbnail-link.php` | `get_template_part()` calls in `menu-hooks.php` |
| Admin JS | `src/js-admin/skaflow-icon-block-icons.js` | its entry point's `import` |
| ACF field group | `acf-json/group_skaflow_menu_options.json` | ACF loads by directory scan; the `key` inside also changes |
| CPT archive template | `templates/archive-skaflow_faq.html` | resolved by post type name — **rename only if the CPT is renamed** |
| Theme images | `assets/images/skaflow-art.{png,svg}` | `get_theme_file_uri()` calls in patterns |
| Translation catalogue | `languages/skaflow.pot` | regenerated, so delete rather than rename |

### Agent instruction layer

| File | Action |
|---|---|
| `AGENTS.md` | **Substitute** — it uses concrete names deliberately. § Git also needs rewriting if Step 0 chose the repository split |
| `.cursor/rules/sf-*.mdc` | **Substitute** — starter rules, keep the `sf-` filename prefix unchanged |
| `.cursor/rules/project-*.mdc` | **Review and reset** — these describe the old project |
| `.cursor/skills/sf-*/` | **Substitute** names in examples; keep the skills |
| `.cursorignore` | **Substitute** — it holds two theme-relative build paths |
| `PROJECT.md` | **Replace wholesale** — see below |
| `context/` | Leave. It documents the previous project's research and decisions; archive or delete deliberately, not as part of a rename |

`setup.sh` and `hooks/client-pre-push` need no substitution. Their shared marker
string is deliberately slug-free; keep it that way.

The `sf-` filename prefix is the starter's identity and does **not** change per
project. Renaming `sf-icons.mdc` to `bc-icons.mdc` would move the layer boundary
with the project and defeat its purpose.

## Rewrite PROJECT.md

Do not substitute inside it — replace it. The new file declares:

- Identity table with the new slug and short prefix
- Stack: which implementation of each swappable capability the new project uses
  (commerce, icons, filters, cards, SEO), with explicit absences
- Plugins, content model
- Empty Known debt section
- Lineage noting the fork point

Then remove any capability the new project doesn't need, using
`context/kb/plugin-woocommerce-surface.md` as the model for what a capability owns.

## Fill the placeholders

The starter ships no real contact details. Everything a project must supply is
marked, so this is a list, not a hunt:

```bash
cd wp-content/themes/<new>
rg -n 'REPLACE_ME'
```

| Marker | Supply |
|---|---|
| `tel:REPLACE_ME` / `REPLACE_ME phone` | The project's phone number, in both the `href` and the visible text |
| `REPLACE_ME@example.com` | Contact addresses. Several patterns use different ones (general, trade, orders) |
| `https://example.com/REPLACE_ME` | Social profile URLs in `patterns/social-icons.php`. **Delete the `wp:social-link` rows the project does not have** rather than leaving them pointing at `example.com` |

Two things deliberately have no marker, because empty is the correct value and
the consuming code is guarded:

- The header phone in Theme Options and its default in `lib/class-site-header.php`
- `patterns/` term and product ID arrays, which are empty so blocks fall back

Leave a marker in place if the project genuinely has no such detail, and delete
the block that would have shown it. Do not substitute a plausible-looking fake.

## Re-arm the gate

Two steps, both easy to forget because nothing complains until much later.

```bash
cd <new repo root>
git config core.hooksPath hooks              # Git never clones .git/hooks/
wp-content/themes/<new>/tools/verify.sh      # must pass before the first push
```

`tools/verify.sh` needs no substitution except the `skaflow` path segment in its
PHPCS fallback, which the slug pass handles. `hooks/pre-push` and
`hooks/client-pre-push` need none at all: their marker strings are deliberately
slug-free.

The first run after a rename is the most valuable one in the project's life,
because PHPCS will catch a missed `phpcs.xml.dist` prefix and the build check
will catch a `build/` directory still holding the old theme's output.

## Verify

Re-run the measurement block from the top of this file. Every `skaflow` row must
be `0` and the `sk-` count must be `0`, with the equivalent counts for the new
prefix roughly matching what the old ones were. A non-zero row is an incomplete
rename, not an acceptable remainder.

```bash
cd <repo root>

# Whole tree, including the agent instruction layer. context/ is excluded
# because it documents the previous project and is handled separately.
rg -i 'skaflow' \
  --glob '!context/**' \
  --glob '!**/node_modules/**' \
  --glob '!**/vendor/**' \
  --glob '!**/build/**' \
  --glob '!**/build-jsx-blocks/**' \
  --glob '!*.log'

# Short prefix, every theme directory. Scoping this to src/ and patterns/
# reports success on a half-finished rename.
rg -o '\bsk-' wp-content/themes/<new> \
  --glob '!**/node_modules/**' --glob '!**/vendor/**' \
  --glob '!**/build/**' --glob '!**/build-jsx-blocks/**'

# Filenames.
find wp-content/themes/<new> -name '*skaflow*' \
  -not -path '*/node_modules/*' -not -path '*/vendor/*'
```

Both `rg` calls should print nothing.

```bash
cd wp-content/themes/<new>
composer dump-autoload
rm -rf build build-jsx-blocks
npm install && npm run build
composer phpcs        # proves phpcs.xml.dist was renamed too
npm run build:makepot # regenerate the catalogue against the new domain
```

```bash
cd <repo root>
ddev restart
ddev wp theme list
ddev wp eval 'var_dump( class_exists( "Brayco\\Init" ) );'
ddev exec tail -40 wp-content/themes/<new>/debug.log
```

Then load the site and the block editor. Confirm patterns appear in the inserter
(their category slug changed), template parts resolve (the `theme` attribute
changed), and icons still render (the short prefix changed).

## Leftover prefixes and dataset contracts

Counting `skaflow` / `sk-` is not enough. A half-finished earlier rename left
`data-sk-products-view` in PHP and `dataset.cfProductsView` in JS: the selector
matched, the click did nothing, and no linter saw it. Run these after the
slug pass.

### `data-*` writers must match `dataset` readers

`data-sk-foo-bar` is `dataset.skFooBar`. A leftover previous prefix on only
one side is a silent storefront bug.

```bash
cd wp-content/themes/<new>

# Server-written data attributes (PHP, patterns, block render).
rg -oN 'data-[a-z0-9-]+' includes lib public partials src-jsx-blocks patterns \
  --glob '!**/node_modules/**'

# JS dataset reads. Every `dataset.xyz` that reads a server-written attribute
# must use the new short prefix camelCased (`bcProductsView` for `bc-`).
rg -n 'dataset\.[a-zA-Z]+' src src-jsx-blocks --glob '*.js'
```

Init-only flags written and read in the same JS file (accordion already-bound,
swiper already-inited) are fine if **both** sides use the new prefix. The
failure is a PHP `data-sk-*` paired with a JS `dataset.cf*` leftover.

Click the archive grid/list toggle, add-to-cart label restore, and any other
control that stores its mode on a data attribute. Lint will not catch this.

### Leftover previous-project prefixes

This starter's pre-skaflow prefix was `cf`. After the skaflow freeze the only
remaining `cf_` identifiers are an **external plugin contract**. Do not rename
them with the theme prefix, and do not treat them as missed substitutions:

- `cf_postcode_estimate` (AJAX action)
- `cf_delivery_postcode`
- `cf_delivery_quantity`

Anything else (`dataset.cf*`, `cfHideBackground*`, `_cf_archive`,
`cf_products_view`, `cf_search`, product tab keys, ACF `cf_*` field names) is
a leftover and must become the current prefix before freeze, or the new
prefix during a rename.

```bash
cd <repo root>

# After a skaflow → new rename, this should be only the three contract names
# above (plus comments that mention them).
rg -n '\bcf_|dataset\.cf|cfHide|cfFilters' wp-content/themes/<new> \
  --glob '!**/node_modules/**' --glob '!**/vendor/**' \
  --glob '!**/build/**' --glob '!**/build-jsx-blocks/**'

# After renaming away from skaflow, leftover `sk-` / `dataset.sk` / `sk_` on
# the theme-owned side is the same class of bug.
rg -n 'dataset\.sk|data-sk-|\bsk_' wp-content/themes/<new> \
  --glob '!**/node_modules/**' --glob '!**/vendor/**' \
  --glob '!**/build/**' --glob '!**/build-jsx-blocks/**'
```

A hit that is not one of the three contract names is an incomplete rename.

## Cautions

- **Rename the directory last**, after substitutions, so paths stay valid while
  you work.
- Do not substitute inside `node_modules/`, `vendor/`, `build/`, or
  `build-jsx-blocks/`.
- Legacy helper prefixes from an older starter generation, if present, should
  be renamed to the new prefix rather than preserved.
- Existing content in the database references old block and pattern slugs.
  Renaming the theme does not migrate post content. For a fresh project this is
  fine; for a fork with content, a slug migration is a separate, explicit task.
