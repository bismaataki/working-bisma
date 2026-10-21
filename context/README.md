# context/ — research, plans, and derived facts

This directory is the working memory for the theme. Code is the source of
truth. These files exist so the next agent or developer can pick up a
feature without rediscovering it.

## The loop

```
plans/           write the approach before building
       ↓
implementation/  how it landed, status, commit pointers
       ↓
notes/           investigation, reviews, findings (empty until the next spike)
       ↓
kb/              derived facts for this tree — update from the three above
```

`decisions/` locks an architectural call so it is not re-litigated, as a
numbered ADR that is never deleted. `figma/` holds design refs that are not
theme assets. `PROGRESS.md` is the status layer on top of the loop, and
`CHANGELOG.md` is the append-only timeline beside it.

When a fact becomes stable, **promote it into `kb/`** (or a `.cursor/rules/`
file if it is a convention). After a feature ships, update kb, write the
implementation note, append to the changelog, and delete the plan. Do not
leave a finished plan as the only copy of a current fact.

**`plans/`, `implementation/` and `notes/` are named `yyyy-mm-dd-slug.md`;
`decisions/` uses `ADR-NNNN-slug.md`; `kb/` carries a topic prefix and no
date.** An implementation note reuses its plan's date and slug, because that
matching filename is the link between the two once the plan is gone.

The **`sf-context-docs` skill owns these conventions** in full: naming, the
front matter that links a plan to its implementation note and its ADRs, ADR
numbering and superseding, and the five close-out steps for shipped work.
Read it before writing a file here. Nothing in this README is a second copy
of it.

## Directories

| Dir | Role | What belongs here |
|---|---|---|
| `plans/` | Before / during a build | One `.md` plan per feature. Deleted when it ships |
| `implementation/` | After it lands | One permanent note per shipped feature, named after its plan |
| `notes/` | Reviews and findings | Audits, inventories, spikes |
| `decisions/` | Locked calls | One numbered ADR per call, permanent, superseded not deleted |
| `figma/` | Design refs | Screens, tokens, exports while a feature is in progress. Empty between features. Theme-shipped SVGs stay in the theme `assets/` |
| `kb/` | Derived facts | Maps, surfaces, SOPs, go-live. **Regenerate maps from code** |

Placeholder directories stay even when empty so the loop has a place to
write. Add a `.md` file; do not invent a new top-level folder for a
one-off.

## kb/ that agents should trust

Regenerate rather than copy-edit after a rename or a large move:

- `kb/map-edit.md`, `map-load.md`, `map-helpers.md`
- `kb/grid-product.md`, `query.md`
- `kb/cpt-showroom-surface.md`, `cpt-faq.md`
- `kb/plugin-woocommerce-surface.md`, `plugin-wc-shop-archive.md`,
  `plugin-wc-checkout-questions.md`, `plugin-wc-my-account.md`
- `kb/design-token-color.md`, `design-token-typography.md`
- `kb/wpcs.md`, `go-live.md`
- `kb/sop-client-code-ai-tools-repository-separation.md`
- `kb/sop-github-to-wpengine-gitpush.md`

Prefixes and the full list: `context/kb/README.md`.

This copy is one in-tree repo. The SOPs still apply when a future project
splits remotes or deploys to WP Engine.

The legacy-site research pack (`existing-functionality/`) and the
header-mega-menu research folder were retired. Their useful facts live in
`kb/` and `project-mega-menu.mdc`.
