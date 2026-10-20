---
name: sf-block-patterns
description: Create or fix WordPress block pattern PHP files for this FSE theme — headers, sourcing markup from the editor, the portability audit for local URLs and database IDs, cleanup of editor-generated markup, block locking, and verification. Use when asked to create a new pattern, update an existing pattern file, extract patterns from a test page, clean up editor markup, or when the Create Block Theme plugin is involved.
---

# Block Patterns

Patterns live in `patterns/{slug}.php` and auto-register — no PHP registration
needed.

Never run Prettier, ESLint, stylelint, PHPCS, or `phpcbf` on `patterns/`,
`templates/`, or `parts/`. Gutenberg owns that markup. Clean it by the rules
in this skill, not by a formatter.

```php
<?php
/**
 * Title: Human-readable name
 * Slug: happywood/slug-here
 * Description: One sentence describing the pattern and its style.
 * Categories: section, happywood
 * Keywords: keyword1, keyword2
 */
?>
<!-- block markup -->
```

All four of `Title`, `Slug`, `Description`, `Categories` are required. `Keywords`
is optional but improves inserter search.

**Only declare registered categories.** The theme registers its own in
`lib/class-after-setup-theme.php`; core supplies `banner`, `about`,
`call-to-action`, `footer`, `header`, `posts`, `query`, `text`, `gallery`,
`columns`, `featured`, `testimonials`, `services`, `contact`, `team`,
`portfolio`, `media`. An unregistered slug silently drops the pattern from that
filter. Current registered and retired categories: `PROJECT.md`.

## Reference files

Read the one you need; don't read all three by default.

| File | Covers |
|---|---|
| [portability-audit.md](portability-audit.md) | Local URLs, database IDs, synced-pattern refs, internal links, runtime context, double insertion |
| [cleanup-rules.md](cleanup-rules.md) | `metadata` stripping, `tagName`, images, presets vs raw values, locking, defaulted attributes |
| [create-block-theme.md](create-block-theme.md) | The Create Block Theme plugin — what it automates, and how to derive which pattern files it can overwrite |

## Two routes into a pattern file

Both are supported. Ask which one is in play if it isn't obvious, because the
starting point differs and the cleanup does not.

| Route | Use when | Then |
|---|---|---|
| **Hand-code** — design in the editor, copy block markup, paste into `patterns/{slug}.php` | Default. A new pattern, or one authored on the scratch page | Start at step 1 below |
| **Create Block Theme plugin** | Pulling uploads into the theme, dereferencing synced patterns, or capturing Site Editor work | Read [create-block-theme.md](create-block-theme.md) first — it silently overwrites any pattern whose slug matches a template or part, and that set has to be derived, not recalled — then join at step 3 |

Whichever route produced the file, steps 3 and 4 still apply. The plugin
automates some of the audit; it does not exempt the result from it, and it cannot
make the judgement call in *Stop and ask* below.

## Do these without asking

Mechanical and reversible, with one right answer. Apply them and say what you
changed:

```
- [ ] 1. Copy block markup from the code editor (hand-code route)
- [ ] 2. Write patterns/{slug}.php with the full header
- [ ] 3. Run the portability audit — fix local URLs, uploads, wp:block refs, attachment IDs
- [ ] 4. Apply the cleanup rules — metadata, tagName, presets, editor residue
- [ ] 5. Verify by fresh insertion, plus a front-end load
```

Verification is not optional and needs no permission — it is how you find out the
first four steps worked.

## Stop and ask

**Content-selection IDs are the user's call, not yours.** Some theme blocks are
*designed* to carry curated entity IDs — the ID is the content, and there is no
ID-free equivalent to migrate to. Others picked one up by accident. The pattern file
looks identical either way, so decide it from the block, not from the markup:

```bash
rg -n 'empty|return|get_field|get_post_meta' src-jsx-blocks/<block>/render.php
```

An attribute that defaults to `[]` and makes `render.php` return early on empty is
deliberate — the block has no other way to know what to show. An attribute named
`fallback*`, or one read only after ACF or post meta comes back empty, is the default
for posts with no selection of their own; emptying it blanks the block on all of
them. Full procedure in [portability-audit.md](portability-audit.md).

Either way, **report and ask** — do not strip the IDs, invent a slug-based query, or
change the block to avoid them. Resolve each ID first so the answer is informed:

```bash
cd <repo root>
ddev wp term get <taxonomy> <term_id> --fields=term_id,name,slug,count
ddev wp post get <id> --fields=ID,post_title,post_status,post_type
```

A useful way to put the question: *this pattern selects these categories and products
by ID — is that curation you want to keep, or should it adapt per install?* Then
record the answer in the pattern's `Description` so the next person doesn't
re-litigate it. Selections already accepted for this build are in `PROJECT.md`.

Everything else needing a decision belongs here too: whether a link is starter
content or a real destination, and whether an odd pixel value was deliberate.

**Deleting the database original is also an ask.** Once the file exists, the scratch
content or synced pattern is a stale duplicate that someone will eventually edit and
wonder why nothing changes — so it usually should go. But it is content, and
deleting it is destructive, so confirm first rather than tidying up unprompted.

### 1. Copy block markup, never rendered HTML

Use the editor's **Code editor** view, or **Copy all blocks**. The block comments
and their JSON attributes *are* the block — rendered frontend HTML has already lost
them and cannot be pasted back.

**A synced pattern is a reference, not content.** Copying one gives you
`<!-- wp:block {"ref":381} /-->`, a pointer to `wp_block` post 381 in *this*
database. Open the synced pattern itself, copy its inner blocks, and paste those. A
theme pattern must never contain `wp:block`. Same for `wp:navigation {"ref":42}`.

### 2–4. Header, audit, cleanup

See [portability-audit.md](portability-audit.md) and
[cleanup-rules.md](cleanup-rules.md). The audit is the step most often skipped and
the one that causes shipped defects.

### 5. Verify by inserting the real thing

Reviewing the file, or the inserter preview, is not verification. Both can look
right while the pattern is bound to this database.

Confirm it registered and that the PHP executed. **Take the slug from the file's
`Slug:` header, not from its filename** — the two routinely diverge, so a filename-
derived slug reports `NOT REGISTERED` for a pattern that is registered fine:

```bash
rg -n 'Slug:' wp-content/themes/happywood/patterns/<file>.php

cd <repo root>
ddev wp eval '$p = WP_Block_Patterns_Registry::get_instance()->get_registered( "happywood/{slug}" );
  echo $p ? "registered\n" : "NOT REGISTERED\n";
  echo $p["content"];' | rg '<\?php|"ref"|"id":[0-9]'
```

A literal `<?php` in the output means the PHP sat somewhere it was never executed.
`"ref"` or an entity `"id"` here is a portability bug, or a curated selection — see
*Stop and ask* above before touching it.

**This check cannot detect a hardcoded domain.** `get_theme_file_uri()` resolves to
this machine's URL at registration, so a correct asset reference and a hardcoded
local-domain one look **identical** in the registered output. Hardcoded URLs are
visible only in the **source file**, which is why the audit greps `patterns/*.php`
and not this.

Then, on a disposable page, insert a **fresh** copy from the inserter — not the old
blocks — confirm the editor reports no invalid blocks, and load the front end. An
entity-ID block that resolves nothing returns empty markup, so "no error" is not
"it worked".

## Extracting from a live-edited page

Patterns are often authored in the editor on a scratch page, separated by `h3`
labels and `<!-- wp:separator -->`.

```bash
ddev wp post get <post_id> --field=post_content
```

Then, per pattern: take the block between its `h3` label and the next separator,
strip the `h3` and separator (they're navigation markers, not content), and apply
the audit and cleanup rules before writing the file. The scratch page ID is
environment-specific — get it from `PROJECT.md` or ask.

## Fix checklist

- [ ] Header has `Title`, `Slug`, `Description`, `Categories`
- [ ] Every declared category is registered
- [ ] Markup came from the code editor, not rendered HTML
- [ ] No `wp:block` or `"ref":` — synced patterns and navigations inlined
- [ ] No local domain, `localhost`, or `/wp-content/uploads/` anywhere
- [ ] Theme assets via `get_theme_file_uri()`; internal links via `home_url()`
- [ ] Entity IDs raised with the user, never silently stripped, answer recorded in `Description`
- [ ] Root block: `tagName:"section"`, `metadata.name` kept, `patternName` added, `categories` stripped
- [ ] Inner blocks: `metadata` stripped entirely
- [ ] No `wp-image-{ID}` or `size-full` on `<img>`; `wp:media-text` has `mediaId:0`
- [ ] Existing sub-patterns referenced by `wp:pattern` slug, not inlined
- [ ] Presets, not raw hex or arbitrary px; no editor drag residue
- [ ] JSON attributes and their generated classes/styles agree
- [ ] No `id="…"` anchors; survives double insertion
- [ ] No PHP needing post or query context
- [ ] No dead `className` values; icon blocks have inline SVG
- [ ] Locking present where structure matters
- [ ] Attributes with non-empty defaults set explicitly where they should be blank
- [ ] Verified by fresh insertion on a disposable page, plus a front-end load

## Related

- `.cursor/rules/sf-fse-templates.mdc` — how templates compose from patterns
- `.cursor/rules/sf-sass-architecture.mdc` — token tiers the presets come from
- `.cursor/rules/sf-blocks.mdc` — making a block dynamic when context is needed
- `PROJECT.md` — registered categories, placeholder images, scratch page ID, known pattern debt
