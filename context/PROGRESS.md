# Skaflow — Progress

Status layer on top of `context/plans/`, `context/implementation/`, and
`context/notes/`. Feature detail lives in those files. Derived facts live
in `context/kb/`. How the loop works: `context/README.md`.

**This file is edited in place and answers "what is the state now, what is
still open".** For "what happened when", see `context/CHANGELOG.md`, which is
append-only. The dated sections below are milestones, not a timeline.

## Starter extract (2026-09-07)

| Item | Status |
|---|---|
| DDEV + WordPress 7.1 at `https://skaflow.test` | Done |
| Theme renamed to skaflow (`Skaflow\`, `sk-`) | Done |
| Woo, FAQ, showroom kept | Done |
| `composer install` + `npm run build` | Done |
| Theme activated | Done |
| Context loop documented | Done |
| TGMPA plugins installed and active | Done |
| WooCommerce installed and active | Done |
| WP Engine / split remotes | Not this copy. SOPs stay for the next project |

## Starter freeze (2026-09-10)

| Item | Status |
|---|---|
| Release zip excludes dev tooling, keeps `vendor/tgmpa/` | Done |
| `AGENTS.md` matches the single-repo reality | Done |
| `.ddev/config.yaml` tracked so a fork clones a working env | Done |
| Repo-split decision owned by `sf-rename-theme` | Done |
| `sf-rename-theme` measures its own surface instead of quoting counts | Done |
| Client data scrubbed from patterns, templates, ACF defaults | Done — screenshot, agency headers, furniture assets, Chairforce copy |
| Hardcoded WC term/product IDs removed | Done |
| Gravity Forms styling decoupled from a form ID | Done — embeds keep `formId` 1 and 2, documented exception |
| `bin/` menu seeder made generic | Done |
| Quality gates green (`phpcs`, `lint:js`, `lint:style`, `lint:pkg-json`, `build`) | Built and linted this pass. Full `verify.sh` after commit |
| Copy genericised past the client's vertical, not just its name | Done — generic store copy; invented stats kept |
| Footer nav points only at URLs a bare install resolves | Done |
| `sf-setup-menus` owns nav setup; derived builder added | Done |
| Gate defined once in `tools/verify.sh`, run by `hooks/pre-push` | Done |
| Node window derived from the toolchain, not pinned by preference | Done |
| Build output verified byte-identical on Node 20, 22, 24 | Done |
| `sf-rename-theme` substitution proven | Done — this starter was produced by it |
| `sf-rename-theme` leftover-prefix and dataset-contract checks | Done — skill updated 2026-09-11 |
| `data-*` / `dataset` prefix mismatch on shop view, ATC, FAQ | Done |
| Zip installs and activates on a clean WordPress | Open — zip to be handed over |
| Swiper critical advisory | Done — `swiper@14.2.0`, `addIcons: false` on theme nav |
| CI workflow | Won't do — pre-push hook instead, see `PROJECT.md` |

## How to keep this updated

- Flip an item when it is verified, not only committed.
- New work starts as a dated `plans/` file, then a kb or rule update, then an
  `implementation/` note reusing the plan's date and slug, then a
  `CHANGELOG.md` entry. Delete the plan once those exist.
- Full procedure, naming, and ADR numbering: the `sf-context-docs` skill.

## Go-live

`context/kb/go-live.md` is the environment checklist. Update it when
a plugin, page, or deploy target is added.
