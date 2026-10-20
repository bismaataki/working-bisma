---
name: sf-filters-wpgb
description: Configure WP Grid Builder for archive filtering — grid query args, card attributes, custom card blocks, and grid settings overrides. Use only when the WP Grid Builder plugin is active. Use when asked to add faceted filtering to an archive, change grid queries, or register a custom WPGB card block.
---

# WP Grid Builder Filtering

## Precondition — two checks, in this order

**1. Read `PROJECT.md` § Stack.** If the Grid builder row does not say WP Grid
Builder, stop here. This is the authoritative answer and it needs no running
site, so it works when Docker is down or WP-CLI is unavailable.

**2. Confirm against the install**, if one is reachable:

```bash
cd <repo root>
wp plugin list --status=active | rg -i 'grid-builder|wpgb'   # or: ddev wp ...
```

**If WP Grid Builder is not active, stop.** Say so and ask which filtering
approach applies. Do not add `wp_grid_builder/*` hooks to a project without the
plugin.

A failed or unavailable shell check is **not** permission to proceed. Absence of
evidence from step 2 leaves step 1 as the answer.

## Choosing an approach

Three filtering approaches appear across this starter's lineage:

| Approach | Use when |
|---|---|
| **WooCommerce native** | Filtering products only — WC ships its own facets, no extra plugin |
| **WP Grid Builder** | Faceted filtering across custom post types, multiple facet types, AJAX grids |
| **Custom REST + delegated JS** | One archive, few facets, no plugin dependency wanted |

Don't add WPGB for a product-only archive — WooCommerce already covers it. Check
`PROJECT.md` § Stack for the declared choice before introducing a new approach.

## Where the code goes

| Piece | Location |
|---|---|
| Hook registrations | `includes/integrations/wp-grid-builder.php`, required from `includes/integrations/bootstrap.php` |
| Custom card templates and helpers | `wp-grid-builder/` |

Keeping both capability-named means the whole filtering layer is removable as a
group.

## The four hooks that matter

| Hook | Purpose |
|---|---|
| `wp_grid_builder/grid/query_args` | Modify the grid's `WP_Query` args |
| `wp_grid_builder/grid/settings` | Override grid settings per context |
| `wp_grid_builder/card/attributes` | Modify card wrapper attributes |
| `wp_grid_builder/blocks` | Register custom card blocks |

Always scope by grid ID or context. An unscoped filter applies to every grid on
the site, including ones added later:

```php
add_filter( 'wp_grid_builder/grid/query_args', 'happywood_wpgb_query_args', 10, 2 );

function happywood_wpgb_query_args( $query_args, $grid_id ) {

	if ( 'your-grid-slug' !== $grid_id ) {
		return $query_args;
	}

	// ...modify...

	return $query_args;
}
```

Return the value in every branch. A filter that returns nothing on its early path
empties the grid.

## Cards

If the project also uses a card plugin, WPGB card output and that plugin's card
output must resolve to the **same markup and classes** — otherwise a card looks
different inside a filtered grid than outside one. Register a custom WPGB card
block that delegates to the same partial rather than duplicating markup.

## Interactions must be delegated

WPGB replaces grid contents over AJAX. Every handler on a card or control inside
a grid must be delegated, and any lazy initialisation must be idempotent and
re-run on content update. Directly bound handlers work until the first facet
click, then die with no console error. See
`.cursor/rules/sf-event-delegation.mdc`.

## Verify

- Filter a facet and confirm cards still respond to clicks, hovers, and any
  swatch or quick-view interaction
- Confirm the grid's initial (unfiltered) render matches the filtered render
- Check that a second grid on the site is unaffected by your scoped filters

## Related

- `.cursor/rules/sf-event-delegation.mdc` — mandatory for AJAX-replaced grids
- `.cursor/skills/sf-cards-blokki/` — card rendering, if that plugin is in play
- `PROJECT.md` § Stack — this project's filtering approach
