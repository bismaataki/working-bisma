# kb/

Derived facts about **this** codebase. Keep the folder flat. The prefix
says what kind of file it is.

| Prefix | Role | Regenerated? |
|---|---|---|
| `map-` | Inventories from code | Yes |
| `grid-` | Card / collection composition | No |
| `cpt-` | Custom post type surfaces | No |
| `plugin-` / `plugin-wc-` | Plugin companion, or a WooCommerce theme surface | Surface: no. `plugin-woocommerce-surface.md`: yes |
| `design-` | Tokens and later design facts | No |
| `sop-` | Procedures for this project and the next | No |
| *(none)* | Ops (`wpcs.md`, `go-live.md`) and blog query (`query.md`) | No |

Do not invent subfolders. After a rename or a large move, regenerate the
maps rather than copy-editing them.

**No date prefixes here.** These files describe the tree as it is now, and a
date invites a reader to treat a derived fact as history and trust a stale
copy. Dates belong on `plans/`, `implementation/` and `notes/` files.

## Maps

- `map-edit.md` — feature → source file
- `map-load.md` — what boots
- `map-helpers.md` — generic helpers

## Grids

- `grid-product.md` — product card + Load More extract

## CPTs

- `cpt-showroom-surface.md` — Showroom strip inventory (removed)
- `cpt-showroom.md` — points at the strip inventory
- `cpt-faq.md` — archive + product merge

## Plugins

- `plugin-woocommerce-surface.md` — commerce strip manifest
- `plugin-wc-shop-archive.md` — filter refresh, view toggle, category banner
- `plugin-wc-checkout-questions.md` — meta keys, SlotFills, cart guard
- `plugin-wc-my-account.md` — shortcode engine + account assets

Companion plugins (custom shipping rates, hosted payments, data migration) are
separate repositories and are not documented here. When a project has one, add a
`plugin-{name}.md` describing only the theme-facing contract — the actions,
meta keys, and options the theme reads — not the plugin's internals.

## Design

- `design-token-color.md`
- `design-token-typography.md`

## SOPs

- `sop-client-code-ai-tools-repository-separation.md`
- `sop-github-to-wpengine-gitpush.md`

## Ops

- `query.md` — related posts + blog listing URLs
- `wpcs.md` — how PHPCS is run and scoped
- `go-live.md` — environment standup
