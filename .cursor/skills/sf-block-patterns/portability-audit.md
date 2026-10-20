# Portability audit

A pattern authored in the editor is, by default, bound to **this** database and
**this** domain. Nothing warns you: the pattern registers fine, the editor reports
no invalid blocks, and the front end renders — just with a broken image or a silently
empty section. Run this before considering any pattern finished.

## The grep

Derive the local host rather than hardcoding it, so this works unchanged on any
build:

```bash
cd <repo root>
HOST=$( ddev wp eval 'echo wp_parse_url( home_url(), PHP_URL_HOST );' )
cd wp-content/themes/happywood/patterns

rg -n "$HOST|localhost|/wp-content/uploads/" *.php   # local URLs
rg -n '"ref"|wp:block |wp:navigation' *.php          # database entity references
rg -n '"id":[0-9]+|wp-image-[0-9]+' *.php            # attachment and entity IDs
rg -n 'href="https?://' *.php                        # absolute internal links
rg -n 'id="[a-z]' *.php                              # HTML anchors
```

## Verdict per hit

| Hit | Verdict |
|---|---|
| Local domain on a theme asset | **Fix** — `get_theme_file_uri()` |
| Local domain on an internal link | **Fix** — `home_url()` |
| `/wp-content/uploads/` | **Fix** — move the file into `assets/images/` |
| `wp:block {"ref":N}` | **Fix** — inline the synced pattern's blocks |
| `wp:navigation {"ref":N}` | **Fix** — drop the ref, or use a menu-less nav |
| `mediaId`, `wp-image-N` | **Fix** — strip, see [cleanup-rules.md](cleanup-rules.md) |
| `"id":N` naming a post, term, or product | **Ask** — often deliberate, see below |
| `gravityforms/form` `formId` | **Keep** the starter pair only — `1` on contact, `2` on subscribe / sidebar. That is the one database-ID exception in `AGENTS.md`. Do not add a third. CSS still must not use `#gform_N` |
| `id="some-anchor"` | **Ask** — breaks on double insertion, but may be intended |
| External URL (real third party) | Fine |
| `"id":0`, `"id":""` | Fine — already reset |

The **Fix** rows have one right answer; apply them. The **Ask** rows are content and
design decisions that look identical to bugs from the markup alone.

## Local URLs

The single most common defect. An image block copied from the editor arrives as:

```html
<!-- wp:image {"id":1834,"sizeSlug":"large"} -->
<figure class="wp-block-image size-large">
  <img src="https://your-site.test/wp-content/uploads/2026/08/foo.jpg" class="wp-image-1834"/>
</figure>
<!-- /wp:image -->
```

Two separate bugs: the domain, and the attachment ID. For a theme-owned design
asset, move the file into `assets/images/` and reference it through the theme:

```php
<img src="<?php echo esc_url( get_theme_file_uri( 'assets/images/example.svg' ) ); ?>" alt="..."/>
```

The nastiest version of this is an asset that **already lives in the theme**. The
editor still writes an absolute URL to it, so the markup renders perfectly on the
authoring machine and 404s everywhere else, while the file itself is present and
correct in git. Nothing about the pattern looks wrong. Check every `<img>` against
this even when you know the image ships with the theme.

## Internal links

```php
<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">
```

A root-relative `/contact/` is also acceptable and simpler, and works except on
subdirectory installs. An absolute local domain is never acceptable. If the link is
starter content the user is expected to replace, `#` is honest.

## Database entity IDs — report, don't fix

The subtlest class, because a curated selection and an accidental coupling look
identical in the markup. IDs hide **nested inside attribute objects**, which is why
the grep looks for `"id":` generally rather than for a guessed attribute name:

```
<!-- wp:happywood/example-swiper {"terms":[{"id":12,"name":"Some Category"},{"id":34,"name":"Another"}]} /-->
<!-- wp:happywood/example-widget {"fallbackItems":[{"id":56,"title":"Some Post"}]} /-->
```

Some theme blocks are *designed* around a curated selection: the ID is the content,
and there is no ID-free equivalent to migrate to. Others picked up an ID by accident.
You cannot tell from the pattern file, so **determine it from the block, then ask.**

### Determining whether the IDs are deliberate

Read the block's `render.php` and `block.json` and answer three questions:

```bash
rg -n 'empty|return|get_field|get_post_meta' src-jsx-blocks/<block>/render.php
rg -n -A 3 '"<attribute>"' src-jsx-blocks/<block>/block.json
```

1. **Does it return early when the attribute is empty, with no alternative source?**
   Then the selection *is* the content — the block has no other way to know what to
   show, and emptying the attribute renders nothing. Deliberate.
2. **Is there a non-ID mode** — query by slug, by count, by recency? Then IDs may be a
   shortcut rather than the design. Still ask before switching, because the order and
   membership of a hand-picked list is editorial.
3. **Is the attribute a fallback for per-post data** — a name like `fallback*`, or
   `render.php` reading ACF or post meta first? Then it is the default for content
   that has no selection of its own. Emptying it silently blanks the block on every
   such post, which is worse than the portability problem you were fixing.

An attribute defaulting to `[]` in `block.json` combined with an early `return` on
empty is the clearest signal of case 1.

### Reporting it

Do not strip the IDs, substitute a slug-based query, or modify the block to avoid
IDs. Resolve each one so the question is answerable:

```bash
ddev wp term get <taxonomy> <term_id> --fields=term_id,name,slug,count
ddev wp post get <id> --fields=ID,post_title,post_status,post_type
```

Blocks of this kind usually store a human label beside each ID — `"name":"…"`,
`"title":"…"`. Where those labels are accurate the selection is self-documenting: you
can report the intent without database access, and on a fork where the IDs no longer
resolve the label still says what was meant. Keep them accurate when editing by hand,
for exactly that reason.

State the cost plainly when reporting: on any other install these IDs are absent or
point at something unrelated, and a block that returns early renders **empty with no
error**. For a curated selection that is the price of curation rather than a bug, and
the right outcome is usually to record the decision in the pattern's `Description`
and note it as a fork concern — not to change it now. Any such selections already
accepted for this build are listed in `PROJECT.md` under known debt.

The same *ask, don't assume* rule covers navigation IDs, form IDs, and any
plugin-generated object ID.

## Synced patterns

Covered in `SKILL.md` because it is easy to do by accident: copying a synced pattern
yields `<!-- wp:block {"ref":381} /-->`, a pointer to `wp_block` post 381 — not the
content. Open the synced pattern, copy its inner blocks, paste those. A theme
pattern must never contain `wp:block`.

## Runtime context — PHP runs at registration

PHP in `patterns/foo.php` does **not** make it a template. WordPress executes the
file once, during pattern **registration on `init`**, and stores the resulting block
markup. It is not evaluated where the pattern is inserted.

Fine, because they don't depend on request context:

```php
<?php echo esc_url( get_theme_file_uri( 'assets/images/chair.jpg' ) ); ?>
<?php esc_html_e( 'Read More', 'happywood' ); ?>
<?php echo esc_url( home_url( '/contact/' ) ); ?>
```

Useless, because there is no post and no main query at `init`:

```php
<?php the_title(); ?>
<?php is_product(); ?>
<?php global $product; ?>
```

If a pattern needs runtime context, **make the block dynamic** — a `render.php` that
runs per request — rather than trying to make the pattern dynamic. See
`.cursor/rules/sf-blocks.mdc`.

## Insert it twice

Cheap test with real findings: put the pattern on a page twice and look for anything
that must be unique per document.

HTML `id` attributes and anchors, JS selectors that assume one match, form IDs,
carousel or accordion instance IDs, and `aria-controls` / `aria-labelledby` pairs —
which break *silently* for screen-reader users when the ID they point at is
duplicated.

```html
<div id="hw-feature-list">   ← ambiguous the moment the pattern appears twice
```

Blocks that need an instance ID generate it at render time, in `render.php`:

```php
'instance_id' => 'hw-<block-name>-' . wp_unique_id(),
```

Find the current examples to copy from:

```bash
rg -l wp_unique_id src-jsx-blocks/*/render.php
```

A pattern should not hardcode one. If you find a hardcoded anchor, **report it rather
than removing it** — it may be a deliberate jump target for a menu or a link
elsewhere on the site, in which case the fix is to insert the pattern once, not to
strip the `id`. This also applies to the theme's JS: see
`.cursor/rules/sf-event-delegation.mdc` for why per-instance binding is scoped rather
than global.
