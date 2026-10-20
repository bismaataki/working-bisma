---
name: sf-cards-blokki
description: Configure the Blokki plugin's card system — per-post-type display config filters, custom card partials, and template overrides. Use only when the Blokki plugin is active. Use when asked to change how post cards render, add a card partial, or configure card output for a custom post type on a Blokki-based build.
---

# Blokki Card System

## Precondition — two checks, in this order

**1. Read `PROJECT.md` § Stack.** If the Card rendering row does not say Blokki,
stop here. This is the authoritative answer and it needs no running site, so it
works when Docker is down or WP-CLI is unavailable.

**2. Confirm against the install**, if one is reachable:

```bash
cd <repo root>
wp plugin list --status=active | rg -i blokki   # or: ddev wp plugin list ...
```

**If Blokki is not active, stop.** Say so and ask which card approach the project
uses. Do not create `blokki/` directories or `blokki_*` filters on a project
without the plugin — they will be silently dead code.

A failed or unavailable shell check is **not** permission to proceed. Absence of
evidence from step 2 leaves step 1 as the answer.

This skill carries forward a pattern used on sibling builds in this starter's
lineage. On a project where Blokki isn't installed, cards are rendered by theme
blocks or partials instead; check `PROJECT.md` § Stack.

## What Blokki owns

Blokki renders post cards from a per-post-type configuration array, assembled
from named partials. The theme customises that array through filters, and can
override or add partials.

| Piece | Location |
|---|---|
| Post type config filters | `includes/integrations/blokki.php` |
| Custom card partials | `blokki/partials/card/` |
| Template overrides | `blokki/templates/` |
| Function overrides | `blokki/functions/` |

## Configuring a post type

One filter per post type, named `blokki_get_post_type_config_{post_type}`:

```php
add_filter( 'blokki_get_post_type_config_post', 'skaflow_blokki_post_type_post' );

function skaflow_blokki_post_type_post( $post_type_config ) {

	$post_type_config = array_merge(
		$post_type_config,
		[
			'taxonomy'       => 'category',
			'link_card'      => true,
			'link_image'     => false,
			'link_title'     => false,
			'show_author'    => false,
			'image_size'     => 'medium_vertical',
			'title_html_tag' => 'h3',
			'partials'       => [
				'image',
				'inner' => [
					'meta' => [ 'date', 'taxonomy' ],
					'title',
				],
			],
		]
	);

	// Context-dependent overrides are allowed here.
	if ( is_home() ) {
		$post_type_config['image_size'] = 'medium_large';
	}

	return $post_type_config;
}
```

`partials` is an ordered, nestable structure — the nesting produces the card's
DOM wrappers, so order and grouping matter as much as the values.

Return the array. Forgetting the return silently blanks every card of that post
type.

## Adding a card partial

Create `blokki/partials/card/custom-partial.php`:

```php
<?php

$custom_field = get_field( 'custom_field', get_the_ID() );

if ( ! $custom_field ) {
	return;
}
?>
<div class="sk-card__custom">
	<?php echo wp_kses_post( $custom_field ); ?>
</div>
```

Then reference it by filename in the config:

```php
'partials' => [
	'image',
	'custom-partial',
	'inner' => [ 'title', 'excerpt' ],
],
```

Partials must bail early when empty rather than emitting empty wrappers — cards
appear in grids, so a stray empty `<div>` multiplies across the page.

Prefix partial classes with the project prefix; Blokki's own classes are outside
theme control and may change between plugin versions.

## Interactions in cards must be delegated

Card grids are re-populated by Load More and filtering. Any handler on a card,
swatch, or button inside a card must be delegated, not bound directly. See
`.cursor/rules/sf-event-delegation.mdc`.

## Styling

Card Sass belongs in a capability-named directory under `src/sass/` with a single
import line, so the whole card layer can be removed with the plugin. Do not scatter
card rules across component partials.

## Related

- `.cursor/rules/sf-event-delegation.mdc` — required for anything interactive in a grid
- `.cursor/rules/sf-sass-architecture.mdc` — capability-scoped Sass directories
- `PROJECT.md` § Stack — which card approach this project uses
