# Cleanup rules

Apply to every pattern. Editor-generated markup carries state that must not ship
in a pattern file.

## 1. `metadata` — root versus inner blocks

- **Root block**: keep `metadata.name`. Add `metadata.patternName` matching the
  PHP `Slug:`. Strip `metadata.categories`.
- **All inner blocks**: strip `metadata` entirely, including any stale
  `patternName` left over from inserting a sub-pattern.

```
<!-- wp:group {"tagName":"section","metadata":{"name":"Split — Features","patternName":"skaflow/section-split-features"},...} -->   ← root, keep
<!-- wp:group {"metadata":{"categories":["skaflow"],"patternName":"skaflow/info-box-bar-navy","name":"Info Box"},...} -->        ← inner, strip
```

## 2. Root group uses `tagName:"section"`

Section-level patterns set `"tagName":"section"` on the root `core/group`, with a
matching `</section>`. Do not leave the default `<div>`.

## 3. Images — theme assets, never uploads

Never leave a `/wp-content/uploads/` URL in a pattern. Uploads are site-specific;
patterns must work on any install.

```php
<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/placeholder.png' ) ); ?>" alt="placeholder"/>
```

Also strip `wp-image-{ID}` classes and `class="size-full"` from placeholder
`<img>` tags. Available placeholder images are listed in `PROJECT.md`; if the one
you need isn't in `assets/images/`, add it there rather than referencing an upload.

## 4. `wp:media-text` — reset media IDs

```
<!-- wp:media-text {"align":"wide","mediaPosition":"right","mediaId":0,"mediaLink":"","linkDestination":"none","mediaType":"image","imageFill":false} -->
```

## 5. Reuse sub-patterns by reference

When a section contains an already-named sub-pattern, reference it — never
duplicate its markup:

```
<!-- wp:pattern {"slug":"skaflow/info-box-bar-navy"} /-->
```

Check `patterns/` for an existing slug before inlining block markup. Note this is
`wp:pattern` with a **slug** — not `wp:block` with a `ref`, which points at a
database row. See [portability-audit.md](portability-audit.md).

## 6. Presets, not raw values

A pattern should consume the design system, not restate it. Then changing
`theme.json` updates every pattern at once.

| Prefer | Over |
|---|---|
| `{"backgroundColor":"foreground","textColor":"secondary"}` | `{"style":{"color":{"background":"#123456"}}}` |
| `{"style":{"spacing":{"padding":{"top":"var:preset\|spacing\|small"}}}}` | `{"style":{"spacing":{"padding":{"top":"37px"}}}}` |
| `{"fontSize":"large"}` | `{"style":{"typography":{"fontSize":"23px"}}}` |

A raw hex or an odd pixel value is nearly always the editor's colour picker or a
drag handle, not a design decision. Registered presets: `theme.json` and
`.cursor/rules/sf-sass-architecture.mdc`.

## 7. Editor residue

Visual building leaves values behind that were never chosen. Ask of each one: *is
this part of the design, or residue from fiddling?*

Prime suspects: `padding`/`margin` in odd pixels (`37px`, `41px`), `minHeight`,
`aspectRatio`, `width`/`height` from resize handles, and inline typography on a
single heading.

## 8. Editing serialized markup — change both representations

Many attributes are stored **twice**: once in the block comment's JSON, once as a
generated class or inline style.

```
<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">Hello</h2>
<!-- /wp:heading -->
```

Change `"textAlign"` without changing `has-text-align-center` and the block fails
validation in the editor — "this block contains unexpected or invalid content".

So: **let Gutenberg serialize the markup; edit it minimally by hand.** For anything
beyond the mechanical strips above, make the change in the editor and re-copy
rather than hand-patching JSON.

## 9. Placeholder text

| Element | Placeholder |
|---|---|
| Heading | `Change me please` |
| Paragraph / description | `Change this please as well.` |
| Eyebrow | `Change me please` |
| Button label | `Change Me` |

Section and wrapper patterns may use real sample content where it communicates the
pattern's purpose.

## 10. Dead classes

Remove `className` values left behind by attributes that no longer exist. Check
against the block's current `block.json` rather than assuming.

## Locking

Where a pattern's structure is the point — an icon plus a content group — lock the
structural parts while leaving text editable:

```
<!-- wp:outermost/icon-block {"iconName":"...","hasNoIconFill":true,"lock":{"remove":true,"move":true},...} -->
...SVG inline...
<!-- /wp:outermost/icon-block -->

<!-- wp:group {"lock":{"remove":true},...} -->
...heading + paragraph...
<!-- /wp:group -->
```

- Icon block: locked for removal **and** movement
- Content group: locked for removal only
- Always include the SVG inline, not a self-closing empty icon block

## Blocks with defaulted attributes

When a block attribute has a non-empty default, an "empty" value must be set
explicitly or the default leaks through. Example — a counter whose `suffix`
defaults to `+`:

| Display | `number` | `suffix` |
|---|---|---|
| `50,000+` | `50000` | `+` (default) |
| `15k+` | `15` | `k+` |
| `98%` | `98` | `%` |
| `7` | `7` | `""` — **must** be set explicitly |

Check `block.json` for defaults before assuming an omitted attribute renders empty.
