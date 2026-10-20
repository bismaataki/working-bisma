# AGENTS.md

**Read `PROJECT.md` before starting.** It declares this project's theme slug,
class prefixes, and which implementation of each swappable capability is live
(commerce, icons, filters, cards, SEO). Several conventions below depend on
knowing that.

This file is the **starter layer**: a WordPress Full Site Editing theme built to
be copied into the next project. Everything here is written to survive that copy.
Anything true of only one build belongs in `PROJECT.md` or `context/kb/`.

Names below are concrete rather than templated. On a fork, the `rename-theme`
skill substitutes them from `PROJECT.md`'s identity table — this file is part of
its substitution set, so treat `happywood` / `Happywood\` / `happywood_` /
`hw-` here as placeholders that happen to be spelled out.

## Layout

The working tree is a full WordPress install, but **Git tracks only the theme,
the AI tooling, and `.ddev/config.yaml`.** WordPress core, `wp-config.php`, and
`wp-content/plugins/` are ignored, so a fresh clone needs core downloaded and
plugins installed before it will boot. `context/kb/go-live.md` has that sequence.

```
/                                WordPress core + wp-config (untracked)
.ddev/config.yaml                dev environment — the only tracked .ddev file
wp-content/themes/happywood/    the theme — all development happens here
  includes/                      procedural code: timeless at root, capabilities in subdirectories
  lib/                           Happywood\ classes, one per file
  public/                        Happywood\ REST endpoint classes — nothing else
context/                         research, plans, decisions
.cursor/rules/                   scoped conventions (sf-* starter, project-* this build)
.cursor/skills/                  on-demand procedures
```

## Where commands run

The most common source of wasted time, so it goes first.

**Node must sit inside the toolchain's window**, not on a hand-picked version.
`package.json`'s `engines` field is the source of truth, `.nvmrc` names one
convenient version in range, and `.npmrc` sets `engine-strict=true` so
`npm install` refuses anything outside it. `npm run` does **not** check
`engines`, so `tools/verify.sh` re-checks before building.

The window is currently `^20.19.0 || ^22.13.0 || >=24.0.0`. **Derive it, never
guess it.** `@wordpress/scripts` alone claims `>=18.12.0`, but the installed
tree is stricter: `@wordpress/ui` needs `^20.19.0 || >=22.13.0`, and `glob`,
`lru-cache`, `jackspeak` and the jest packages declare `20 || >=22`, which rules
out odd majors. Re-derive after a dependency bump:

```bash
rg -l '"engines"' node_modules --glob package.json | \
  xargs -I{} node -p "(p=>p.engines&&p.engines.node?p.name+' '+p.engines.node:'')(require('{}'))" | sort -u
```

**Build output does not depend on the Node major.** Verified byte-identical on
20.20.2, 22.22.0 and 24.13.1, so the build-freshness check below is safe
anywhere in range. Do not tighten the window to protect it.

It does depend on the dependency tree, which is why **`package-lock.json` is
committed** alongside `composer.lock`. Two things the gate treats as exact rest
on it: `build/` is compared byte-for-byte against `HEAD`, and the ESLint and
stylelint rule sets arrive through caret-ranged `@wordpress/scripts`. Unpinned,
two developers can enforce different rules and each rebuild what the other
committed. Neither lockfile ships; `.npmignore` excludes both. Use `npm ci`
when you want the locked tree exactly.

**npm runs in the theme directory:**

```bash
cd wp-content/themes/happywood
npm run dev            # browser-sync + both watchers in parallel
npm run build          # build:assets + build:blocks
npm run build:assets   # every webpack entry in package.json → build/
npm run build:blocks   # src-jsx-blocks/ → build-jsx-blocks/
npm run create-zip     # happywood.zip — packlist, not a hand-rolled archive
tools/verify.sh        # the whole gate, in one command
```

**`tools/verify.sh` is the gate, defined once.** Build, then the build-freshness
check, then `lint:style`, `lint:js`, `lint:pkg-json`, and PHPCS with the host-first-then-DDEV
fallback below. `hooks/pre-push` calls exactly this, so a push cannot pass
something a hand run would fail. Add a check here rather than to the hook.

The build-freshness step exists because nothing is installed or compiled on
GitHub or the host: **whatever is committed in `build/` is literally what
renders.** A stale build is a broken deploy, not untidiness. The step rebuilds
in place and then runs `git diff HEAD -- build build-jsx-blocks`, so a dirty
tree afterwards means the committed build did not match the committed source.
Do not reach for file modification times: Git does not preserve them, so any
mtime comparison is meaningless on a fresh clone.

That diff is blind to untracked files, so the step also runs
`git ls-files --others --exclude-standard` over the same two directories.
Adding a webpack entry to `package.json` is the case that needs it: the build
emits `build/<entry>.asset.php`, nobody adds it, and the diff sees nothing
while the deploy ships a build missing a file. **Build output has to be tracked,
not merely current.** Both halves matter, so keep them together.

Because the comparison is against `HEAD`, running the gate by hand mid-edit
reports uncommitted build output as stale. That is correct at push time. Run it
after committing.

**Theme zip is `npm run create-zip`.** That is `wp-scripts plugin-zip`.
`package.json` `"files": ["*"]` hands membership to `npm-packlist`. **`.npmignore`
is the release manifest.** When both files exist, packlist reads `.npmignore` and
ignores `.gitignore` entirely, so an exclusion added only to `.gitignore` will
still ship. Do not add a `.distignore` or zip the folder by hand. Build first.

Two directories need the same treatment in both files, for two different
delivery routes:

- **`vendor/` is split.** `vendor/tgmpa/` ships — TGMPA is a runtime dependency
  and the theme fatals without it. The PHPCS packages (`vendor/bin/`,
  `vendor/squizlabs/`, `vendor/wp-coding-standards/`, `vendor/phpcsstandards/`,
  `vendor/dealerdirect/`) are dev-only and must be excluded.
- **Both routes matter.** Some projects deploy the zip; others push the theme to
  a Git-based host that installs nothing, so the runtime half of `vendor/` has to
  be committed. That is why the split is expressed twice rather than solved with
  `composer install --no-dev`.

Bundled plugin zips in `lib/plugins-zip/` are included — they are not named
`happywood*.zip`. Companion plugins, when a project has them, are separate
repositories and are deployed separately.

**Audit the zip by reading the zip.** `npm pack --dry-run` does not agree with
`wp-scripts plugin-zip`: it lists `tools/`, `.nvmrc`, and the PHPCS test files
that the real archive correctly excludes. Trusting it produces false alarms
about a release that is fine.

```bash
npm run build && npm run create-zip
unzip -l happywood.zip | tail -1                      # file count
unzip -l happywood.zip | grep -c squizlabs            # expect 0
unzip -l happywood.zip | grep -c tgmpa                # expect non-zero
```

**Always the full script. Never a single-entry `wp-scripts` shortcut.**
`wp-scripts build src/my-account.js` (or any other subset) treats `build/` as
that run's output and deletes everything else. `lib/class-front.php` then
fatals on the missing `index.asset.php` and the whole site 500s. Same rule
for `start`: use `npm run start:assets` / `npm run dev`, not `wp-scripts start`
on one file.

**Never lint or format `templates/`, `parts/`, or `patterns/`.** Those are
Gutenberg block markup. Do not run Prettier, ESLint, stylelint, PHPCS, or
`phpcbf` on them. Hand-written Sass under `src/sass/patterns/` is not in
that ban.

**WP-CLI and anything that loads WordPress** run from the repo root, never the
theme directory. Try host `wp` first; fall back to DDEV when the host has no PHP
runtime or no WordPress bootstrap. Whichever works, keep using it for the rest of
the session rather than alternating.

```bash
cd <repo root>                     # NOT the theme directory

wp plugin list --status=active     # host first
ddev wp plugin list --status=active # fall back to this

ddev exec php -r 'require "wp-load.php"; echo get_option( "siteurl" );'
ddev ssh                           # interactive shell in the web container
```

**Never report "WP-CLI unavailable" until both have failed.** A missing host
`php` is the normal case on a DDEV-only machine, not a blocker.

**PHPCS does not need WordPress** (theme stubs in `tools/phpcs-wp-stubs.php`), so
it runs on the host whenever Composer is installed:

```bash
cd wp-content/themes/happywood
composer phpcs    # WPCS; must pass
composer phpcbf   # only on files you are already touching
```

If `php` or `composer` is not on your PATH, run the same scripts through DDEV
from the repo root:

```bash
ddev composer -d wp-content/themes/happywood phpcs
ddev composer -d wp-content/themes/happywood phpcbf
```

PHPCS prints one progress dot per parallel worker, not per file. A run ending
`8 / 8 (100%)` has scanned the whole tree, not eight files. Use
`--report=summary` for the real count.

`tools/verify.sh` already picks between these two, so prefer the gate when you
want the whole check and `composer phpcs` when you are iterating on PHP alone.

## Git

**Activate the pre-push hook once per clone**, from the repo root:

```bash
git config core.hooksPath hooks
```

Git never clones `.git/hooks/`, so a tracked `hooks/` directory plus that one
setting is how the gate survives into a fork on any Git host. It is also why CI
is not the mechanism here: some projects deploy by pushing to a host-provided
Git remote that runs nothing.

`hooks/pre-push` runs `tools/verify.sh`, then `hooks/client-pre-push`, which
self-skips unless `AGENTS.md` is an overlay symlink. Both live under the one
`pre-push` filename deliberately — setting `core.hooksPath` makes Git ignore
`.git/hooks/pre-push` entirely, so a second hook installed there would go
silently dead.

Forgetting that one setting used to be silent, so `npm install` and
`tools/verify.sh` both run `tools/check-hooks.sh`, which prints the command
above when Git would find no `pre-push` to run. It resolves the hooks directory
through `git rev-parse --git-path hooks`, so it stays quiet for a split-repo
clone whose hook sits at `.git/hooks/pre-push` instead. It warns and exits 0,
never non-zero: breaking `npm install` over a reminder is worse than the
reminder.

The hook is still a reminder, not a guarantee. `--no-verify` bypasses it, and
it needs `node_modules` installed and either Composer or DDEV available.
**Never `--no-verify` a push.** If the gate is wrong, fix the gate.

**Never commit or push unless the user asks.** Not at the end of a task, not to
"save progress", not because the change looks finished. Leave work staged or
unstaged and say what you changed — the user decides when it lands.

**The starter is one repository.** Theme, WordPress install, and AI tooling
(`AGENTS.md`, `PROJECT.md`, `context/`, `.cursor/`) are committed together. There
are no symlinks and no second working tree. Do not look for one, and do not split
this repository on your own initiative.

Client projects often *do* need that split, so the AI tooling can stay private
while the theme goes to a client remote. Setting it up is a fork-time decision
owned by the `sf-rename-theme` skill, which asks before doing anything.
`setup.sh` and `hooks/client-pre-push` are the machinery it uses; both are inert
until then. Procedure:
`context/kb/sop-client-code-ai-tools-repository-separation.md`. Remotes and
environment names for the current build are in `PROJECT.md`.

Deploy the theme only, and only when the user asks. Never deploy WordPress core
or the AI tooling. Do not use the official WP Engine rsync Action or root-level
`rsync --delete`. Procedure: `context/kb/sop-github-to-wpengine-gitpush.md`.

When a commit *is* requested:

- Never rewrite line endings as a side effect. The theme
  `.gitattributes` normalises text to LF (`* text=auto eol=lf`) and marks binary
  extensions, so endings are Git's job, not yours. Rewriting a whole file to
  normalise endings buries the real change and destroys `git blame`. Check
  `git diff --numstat` before committing: if a file you edited lightly shows its
  full line count changed, you have reflowed it.
- Never `push --force`, amend a pushed commit, or skip hooks.

## PHP architecture

Theme classes use the `Happywood\` namespace and live in `lib/class-{name}.php`:

- The file name must match the class name exactly. `composer.json` uses a
  `classmap` autoloader over `lib` and `public` — no PSR-4.
- Register hooks in a private `register_hooks()` called from the constructor.
- Instantiate from `lib/class-init.php` through a private `define_*_hooks()`
  method added to the constructor.
- Run `composer dump-autoload` after adding a class.

### REST endpoints

The one exception to `lib/`. HTTP endpoints live in
`public/class-api-{concern}.php` and extend the abstract `Api` base class, which
owns the `happywood/v1` namespace, the `rest_api_init` wiring, and response
shaping. A subclass declares routes in `register_routes()` and nothing else.

`public/` holds endpoint classes and nothing else — not front-end rendering, not
asset enqueuing. Those stay in `lib/`. The directory name describes the HTTP
surface, not the audience, or it becomes a second junk drawer.

Two gates, and they are not interchangeable: **`is_available()`** answers "does the
code this endpoint needs exist", the **`permission_callback`** answers "is this
request allowed right now".

**Never write `register_rest_route()` into an `includes/` function file.** That was
the old pattern; it fataled any fork that deleted the capability owning those
files. `.cursor/rules/sf-rest-api.mdc` is the full convention — read it before
adding or changing an endpoint.

Procedural code goes in `includes/`. Timeless root files are `require_once`'d
from `includes/init.php`; each capability directory loads through its own
`bootstrap.php`. That directory has two tiers, and the distinction is the point:

```
includes/                       timeless layer — generic machinery, no capability
  init.php  constants.php  helper-functions.php
  accordion-functions.php  taxonomy-terms-functions.php
  category-swiper-functions.php  load-more-functions.php
  archive-shell-functions.php
  woocommerce/                  one directory per capability, each with bootstrap.php
  post/  faq/  showroom/
  header/  menu/  integrations/
```

**Put a file in a capability directory unless it is genuinely generic.** A
capability directory plus its one `require_once` of `bootstrap.php` in `init.php`
is deleted as a unit when a project doesn't need it — that is what makes a fork
cheap. Files at the root can never be deleted, so a capability-specific file
placed there welds that capability into the starter permanently. Add new files
to that directory's `bootstrap.php`, not to `init.php`.

Name directories after the **capability**, never after a layer. `woocommerce/`,
`showroom/`, `faq/` are right; `helpers/`, `utils/`, `services/` are not.
Third-party plugin hooks go in `includes/integrations/`.

Genuinely generic utilities go in `includes/helper-functions.php` — check
`context/kb/map-helpers.md` for what already exists before adding one.
Feature → source file start points: `context/kb/map-edit.md`.
What boots: `context/kb/map-load.md`.

## Non-negotiables

### Block PHP placement

The blocks build passes `--webpack-copy-php`, so every `.php` file in a
`src-jsx-blocks/{name}/` folder is copied into `build-jsx-blocks/{name}/`.
That used to be a silent drop: default `wp-scripts` copies only the file
named by `block.json`'s `render` key. The old "inline everything in
`render.php`" rule was a workaround for that, not a placement preference.

Two tiers remain:

- **Block-local PHP may live next to `block.json`.** Helpers only that
  block calls can sit in the folder and will ship. The folder stays
  deletable as a unit.
- **Cross-cutting code still goes in the capability's `includes/`
  directory** — predicates, markup, or data consumed by PHP hooks, REST
  routes, `lib/` classes, or more than one block. `render.php` then calls
  it as a thin shim.

Never pass `--webpack-copy-php` on `build:assets`. That run's source is
the theme root and the flag would dump the whole PHP tree into `build/`.

Most commerce blocks here are still shims of the second kind:
`src-jsx-blocks/product-card/` delegates to
`includes/woocommerce/product-card-functions.php`. Do not move those
into the block folder just because the copy flag exists.

### Programmatic buttons

Use `happywood_get_buttons_markup()` — never hand-roll
`<div class="wp-block-button">` in PHP. The helper emits real `core/buttons`
block comments and renders them through `do_blocks()`, so output matches the
Site Editor: block styles, layout classes, icon classes. Reference
implementation: `lib/class-load-more.php`.

Content CTAs authored in patterns stay as ordinary editor blocks. Avoid the
helper in tight loops — `do_blocks()` has a per-call cost.

### Event delegation

**Delegate repeatable interactions. Never `addEventListener` on an element
inside a grid.** Direct binding works on page 1 and breaks silently on page 2 of
Load More or after a filter refresh. Use `src/js/shared/delegated-events.js` —
see `.cursor/rules/sf-event-delegation.mdc`.

### Theme assets

Reference images through `get_theme_file_uri()`. Never leave a
`/wp-content/uploads/` URL in a pattern or template — assets must travel with
the theme.

### Placeholder content

The starter ships no real contact details. Patterns and templates carry
`REPLACE_ME` where a project must supply one:

```
tel:REPLACE_ME          REPLACE_ME phone          REPLACE_ME@example.com
https://example.com/REPLACE_ME
```

`rg REPLACE_ME` is the fill-in list for a new project. **Never invent a phone
number, address, or social URL to make a pattern look finished** — a plausible
fake is worse than an obvious blank, because nobody ever finds it.

Two related rules that keep the starter portable:

- **No database IDs in patterns, and none in `bin/` either.** Term IDs, post
  IDs, and menu IDs are per-install and resolve to nothing on reuse.
  Ship the empty array and give the block a sensible default instead;
  `src-jsx-blocks/product-cat-swiper/` is the reference, where no selection
  means top-level categories. Tooling resolves the same way: menus come from
  `happywood_menu_setup_locations()` plus a lookup by theme location, never a
  baked-in term ID. A hardcoded menu ID reads as "this menu is empty" on every
  install but the one it came from, which is not a failure anyone chases.
- **Gravity Forms `formId` in patterns is the one exception.** The embed block
  requires a numeric ID, so the starter ships `1` on the contact pattern and
  `2` on the subscribe / sidebar patterns. Treat those as "create these two
  forms, in that order" on a fresh install, not as portable content. Do not
  add further form IDs.
- **Never key CSS to a generated ID.** `#gform_2` was styling one form on one
  install. Put a class on a wrapper in the pattern and style that.

Options that feed the front end follow the same rule: leave the default empty
and guard the consumer, rather than defaulting to a previous project's value.

### Debugging

Use the theme helpers, not `error_log()` or `var_dump()`:

```php
happywood_write_log( $thing );   // → wp-content/themes/happywood/debug.log
happywood_dump( $thing );        // → <pre> in the response
```

## Build entry points

- `src/index.js` → `build/index.*` — admin and editor assets. It imports
  `sass-admin/index.scss`, `js-admin` and `blocks-extension`, not the frontend
  entry points. `build/index.css` is still **not** admin-only, because
  `sass-admin/index.scss` loads the frontend capability sheets with
  `meta.load-css()` inside `.editor-styles-wrapper` so editor previews match
  the front end. A capability's Sass directory is therefore loaded **twice**
  — `@use` in `src/sass/index.scss` and `meta.load-css()` there.
- `src/public.js` → `build/public.*` — frontend only: `sass/index.scss` and
  `js/index`.
- `src-jsx-blocks/*/` → `build-jsx-blocks/*/`, registered from
  `blocks-manifest.php` by `lib/class-blocks-jsx.php` (per-folder
  fallback on older WordPress). The preferred block approach.
- `src-acf-blocks/*/` — auto-registered by `lib/class-acf.php`. Needs
  `block.json`, `render.php`, `index.php`. No per-block Sass; styles live in
  `src/sass/`.

Browser-sync needs `config-dev/.env` containing `PROXY=yoursite.test`.

## Deeper knowledge

`.cursor/rules/` is scoped by glob and loads automatically when relevant. `sf-*`
files are starter conventions; `project-*` files apply to the current build only.

`.cursor/skills/` holds on-demand procedures. Two are gated on a plugin choice —
`sf-cards-blokki` and `sf-filters-wpgb` apply only if `PROJECT.md` § Stack names
that plugin. **Check that table before invoking one, and never create integration
files for a plugin this project doesn't use.**

The other swappable capabilities carry their procedure in a rule rather than a
skill: icons in `sf-icons.mdc` plus the project's icon-set rule, SEO in
`sf-seo.mdc`, commerce across `sf-commerce.mdc` and the `sf-woocommerce-admin`
skill. Removing a capability entirely is `sf-strip-capability`.

**Menus live in the database, so a fresh clone has none** and the header nav row
renders empty until seeded. That is expected. `sf-setup-menus` owns building it,
including the choice between deriving from the catalogue and a designed tree.
The footer is not a menu: it is inline block markup in `patterns/footer.php`, so
it renders with no setup. Do not convert it to one.

`context/` holds research and planning. The loop is
`plans/` → `implementation/` → `notes/` → `kb/`. See `context/README.md`.

**Files there are named, not free-form**, and the names carry the wiring:
`plans/`, `implementation/` and `notes/` use `yyyy-mm-dd-slug.md`, `decisions/`
uses `ADR-NNNN-slug.md`, and `kb/` uses a topic prefix with no date. An
implementation note reuses its plan's date and slug, because the plan is
deleted when the feature ships and that matching filename is the only link
left between them. **The `sf-context-docs` skill owns all of it**: naming, the
front matter linking a plan to its implementation note and ADRs, ADR numbering
and superseding, and the close-out steps. Read it before writing in `context/`.

- `context/PROGRESS.md` — status layer, edited in place
- `context/CHANGELOG.md`, the append-only timeline of shipped features and ADRs
- `context/kb/` — derived facts (maps, composition contracts, WPCS, go-live).
  Regenerate maps from code rather than trusting a stale copy.
- `context/kb/wpcs.md` — how PHPCS is run and scoped
- `context/kb/go-live.md` — environment standup; keep current
- `context/kb/sop-client-code-ai-tools-repository-separation.md` — split remotes
  (this copy is one in-tree repo; the SOP is for the next project)
- `context/kb/sop-github-to-wpengine-gitpush.md` — GitHub Actions
  to WP Engine GitPush (no WPE remote on this copy yet)
- `context/plans/` — write before building; deleted when the feature ships
- `context/implementation/` — how it landed; the plan's permanent counterpart
- `context/notes/` — reviews and findings
- `context/decisions/` — numbered ADRs; superseded by a later one, never deleted
- `context/figma/` — design refs that are not theme assets
