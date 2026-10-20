---
name: sf-setup-menus
description: Build or rebuild this theme's Primary Nav mega menu and Utility Nav — choosing between deriving from existing content, a hand-authored tree from a design, or wiring it by hand, plus backup, verification, and the ACF fields that drive the mega-menu layout. Use when the header navigation is empty, when setting up menus on a fresh clone or new environment, when asked to seed or reseed the nav, or when mega-menu columns and thumbnails are not rendering.
---

# Setting up the navigation menus

Menus live in the **database**, not the theme. A fresh clone of this repo has
none, so `Happywood\Site_Header::render_primary_nav()` hits its `has_nav_menu()`
guard and the header nav row renders empty. That is expected, not a bug.

This skill owns getting from there to a working mega menu.

## Two locations

| Constant | Location slug | Renders |
|---|---|---|
| `HAPPYWOOD_MENU_PRIMARY` | `happywood-primary-nav` | The main nav row and its mega-menu panels |
| `HAPPYWOOD_MENU_UTILITY` | `happywood-utility-nav` | Small top-bar links with icons |

Both are registered in `lib/class-after-setup-theme.php`. The seeders create and
assign them if missing, so there is no manual step in Appearance > Menus first.

## Step 1 — always back up first

Non-negotiable if any menu already exists. The seeders add to the assigned menu
and will sit alongside what is there.

```bash
cd <repo root>
wp eval-file wp-content/themes/happywood/bin/backup-primary-nav-menu.php
# or: ddev wp eval-file ...
```

Writes a timestamped JSON snapshot to `bin/backups/`, which is gitignored
because it holds live term and menu-item IDs. `bin/dump-primary-nav-menu.php`
prints the current tree without writing anything.

## Step 2 — measure before choosing

Do not ask the user which source they want before knowing what is possible.
A derived menu needs content to derive from.

```bash
wp eval '
require_once ABSPATH."wp-content/themes/happywood/bin/lib/menu-setup-helpers.php";
require_once ABSPATH."wp-content/themes/happywood/bin/lib/menu-setup-from-content.php";
foreach ( happywood_menu_setup_survey_content() as $k => $v ) { printf("%-20s %d\n", $k, $v); }
'
```

Reports top-level product categories with products, their children, and counts
of products, pages, and posts.

## Step 3 — ask the user, with the survey in hand

Three sources. They are not ranked in the abstract; the survey and whether a
design exists decide it.

| Source | Use when | Command |
|---|---|---|
| **Derived from content** | The catalogue exists but the nav design does not. Default on any site with top-level categories | `bin/setup-primary-nav-from-content.php` |
| **Hand-authored tree** | A design exists. This is the real-project path | Edit `bin/lib/menu-setup-tree.php`, then `bin/setup-primary-nav-menu.php` |
| **By hand in wp-admin** | Fewer than about five items, or the nav is not catalogue-driven at all | Appearance > Menus |

**Articles are a supplement, not a fourth source.**
`bin/setup-primary-nav-articles.php` adds blog sections to whatever Primary Nav
already exists, so it runs *after* one of the three above rather than instead
of one. It discovers post categories and their most recent posts from the
install at run time, keyed on nothing, so the same command works on a fresh
install, a dummy-data install, and a clone of a client's live site. It skips
the default category, skips categories with nothing published, and caps both
the number of sections and the posts in each.

Each category becomes a four-column panel built from three groups, so the
handover shows what the mega menu can actually do rather than half a panel:

```
[category]                      grid_columns=4
  LATEST        span 2, children in 2   recent posts, thumbnail-link
  MORE ARTICLES 1 column                older posts, plain text
  IN THIS TOPIC 1 column                child categories, plain text
  (OTHER TOPICS when the category is flat, listing sibling categories)
```

Two heading widths, two link types, and a mix of posts and terms. Post
categories are usually flat, which is why the third group falls back to
siblings rather than rendering nothing. Any group whose source turns up empty
is skipped, not left as a blank column.

Put the trade-off to the user plainly, because the failure modes differ:

- **Derived** is correct the day it runs and exercises the mega-menu fields
  against real thumbnails. It cannot infer editorial grouping, so every category
  gets one heading and you split them by hand afterwards.
- **Hand-authored** gives exact control over grouping, column spans, and
  ordering. It costs the term IDs up front, and `menu-setup-tree.php` ships with
  placeholders (`HAPPYWOOD_MENU_SETUP_TERMS`, all zero) that log a skip until
  filled in.

**Never seed placeholder links into a live nav.** A menu of `Category A` and
`Child One` renders 404s, and it survives to launch precisely because nothing
flags it. If there is no content and no design, leave the nav empty and say so.

## Step 4 — run it

```bash
cd <repo root>

# Derived
wp eval-file wp-content/themes/happywood/bin/setup-primary-nav-from-content.php

# Hand-authored, after filling in HAPPYWOOD_MENU_SETUP_TERMS
wp eval-file wp-content/themes/happywood/bin/setup-primary-nav-menu.php

# Articles, added on top of either of the above
wp eval-file wp-content/themes/happywood/bin/setup-primary-nav-articles.php
```

All three are idempotent per top-level item: they reuse an existing item for the
same term rather than duplicating it.

**These are bootstraps, and the menu belongs to the editor afterwards.** A
re-run is for standing up a new environment, not for syncing. Matching is on
the term, not the title, so renaming a seeded item in Appearance > Menus does
not produce a duplicate on the next run, and reordering survives too. The one
edge: deleting every child while leaving the parent lets the next run refill
the children.

Utility Nav is only built by the hand-authored runner. Its items are fixed
destinations (Showrooms, Account), not catalogue entries, so there is nothing to
derive. The derived runner leaves it alone.

## Step 5 — verify

```bash
wp eval-file wp-content/themes/happywood/bin/dump-primary-nav-menu.php
wp menu list --fields=term_id,name,locations,count
```

Then load the front end and check three things, because each fails differently:

1. **Top-level items appear** in the nav row. If not, the menu is not assigned to
   the location.
2. **A panel opens** with columns. If items appear but no panel, the mega-menu
   ACF fields did not save.
3. **Thumbnails render.** Check what the item links to before assuming content
   is at fault. `happywood_menu_get_thumbnail_id()` resolves an ACF `image`
   override first, then a `product_cat` term image, then a post's featured
   image. So a blank tile on a **category** item means the term has no image,
   and a blank tile on an **article** item means the post has no featured
   image. Both are content problems. A blank tile on an item type the resolver
   does not handle at all is a code problem, and looks identical.

## The ACF fields that drive the layout

Menu-item fields, registered from `includes/menu/`. The seeder helpers set them;
know them for hand-editing in Appearance > Menus.

| Field | On | Effect |
|---|---|---|
| `grid_columns` | Top-level item | Column count for the panel, usually `3` or `4` |
| `label_mobile` | Top-level item | Replacement label in the mobile drawer |
| `link_type` | Any item | `default`, `highlight-link` (accent style), `utility-link` |
| `nav_align` | Top-level item | `right` pushes the item to the end of the row |
| `column_span` | Heading | Grid columns the heading occupies |
| `child_columns` | Heading | Columns its own children lay out in |
| `utility_icon` | Utility item | Lucide slug from the curated set |

`column_span: 2` plus `child_columns: 2` is the standard treatment for a long
child list inside a four-column panel.

## Files

| File | Role |
|---|---|
| `bin/lib/menu-setup-helpers.php` | Generic. Creating items, setting fields, finding and deleting subtrees. Do not edit per project |
| `bin/lib/menu-setup-tree.php` | **Per project.** The hand-authored tree. Rewrite this from the design |
| `bin/lib/menu-setup-from-content.php` | Generic. Survey plus the derived builder |
| `bin/lib/menu-setup-articles.php` | Generic. Survey plus the Articles builder. Delete as a unit on a fork with no blog |
| `bin/setup-primary-nav-articles.php` | Runner for the Articles sections |
| `bin/setup-primary-nav-menu.php` | Runner for the hand-authored tree |
| `bin/setup-primary-nav-from-content.php` | Runner for the derived tree |
| `bin/backup-primary-nav-menu.php` | Snapshot to `bin/backups/` |
| `bin/dump-primary-nav-menu.php` | Print the tree, no writes |

`bin/` is excluded from the release zip but is committed, so these run on any
environment with WP-CLI.

## Related

- Header rendering and the mega-menu markup: `.cursor/rules/project-mega-menu.mdc`
- Icon slugs for `utility_icon`: `.cursor/rules/project-lucide-icons.mdc`
- Environment standup order: `context/kb/go-live.md`
- The footer nav is **not** a WordPress menu. It is inline `navigation-link`
  blocks in `patterns/footer.php` so it renders on a fresh clone. Do not
  "fix" it by converting it to a menu.
