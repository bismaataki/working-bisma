# Create Block Theme plugin

Reviewed against **2.10.1**. Currently **installed but inactive**. Read this before
activating it or running any of its save/export actions, because several of them
write directly into the theme's git-tracked files with no confirmation and no
existence check.

Re-verify against the installed source before trusting anything below; this plugin
changes behaviour between releases.

## What it automates well

These are the parts of the portability audit it genuinely does for you.

| Feature | What it does |
|---|---|
| Media localization | Copies referenced uploads into the theme's assets folder and rewrites the URL to a PHP call |
| Synced-pattern dereference | Finds `wp:block {"ref":N}` in database templates and rewrites it to `wp:pattern {"slug":"…"}` |
| Paternize | Moves PHP-bearing template markup into a `patterns/*.php` file and leaves the template referencing it |
| Translate-ready strings | Wraps text in gettext calls |
| Save Site Editor changes | Writes database template and style customizations back into theme files |

The media and synced-pattern features attack exactly the two defect classes in
[portability-audit.md](portability-audit.md). That is the real argument for using it.

## The main hazard — it can overwrite hand-written patterns

`paternize_template()` triggers on a simple condition:

```php
// If there is any PHP in the template then paternize
if ( str_contains( $template->content, '<?php' ) ) {
```

Which is reached the moment media localization inserts a PHP URL into a template.
The template's markup is then written to **`patterns/{template-slug}.php`** with
`file_put_contents` — no check for an existing file, no backup, no prompt.

This bites hardest on a theme that composes its templates from patterns of the same
name — where `parts/footer.html` is just
`<!-- wp:pattern {"slug":"happywood/footer"} /-->`. That is the convention here, and
it is also what this plugin generates, so template slugs and pattern filenames
collide by design.

Any colliding pattern file can be silently replaced by generated markup. Never work
from a remembered list; derive it, because it changes as templates and patterns are
added:

```bash
cd wp-content/themes/happywood
find templates parts -maxdepth 1 -name '*.html' -printf '%f\n' | sed 's/\.html$//' | sort -u > /tmp/slugs
find patterns -maxdepth 1 -name '*.php' -printf '%f\n' | sed 's/\.php$//' | sort -u > /tmp/pats
comm -12 /tmp/slugs /tmp/pats
```

**Never run a save or export action against a dirty working tree.** Commit first, run
the action, then read `git diff` and keep only what you meant. That is the whole
safety procedure, and it is sufficient — the plugin's writes are all visible to git.

## What it does not threaten

Worth stating, because it reads like a risk in the changelog. 2.10.0's "strip PHP
execution tags from pattern body content" is invoked in only two places, both on
**database** content:

```
theme-templates.php:175   $template->content     = …strip_php_tags( … )   ← DB template being exported
theme-patterns.php:90     $pattern_post->post_content                     ← wp_block synced pattern
```

It never reads or rewrites existing `patterns/*.php`. So the theme's deliberate
`<?php echo esc_url( get_theme_file_uri( … ) ); ?>` calls are safe from it. The
stripping exists to stop *user-authored* content from injecting executable PHP into
an exported theme, which is reasonable.

## Convention divergences

- **Asset URLs.** It emits `get_template_directory_uri()` — or
  `get_stylesheet_directory_uri()` for a child theme — where this theme's convention
  is `get_theme_file_uri()`. For a non-child theme these resolve to the same URL, so
  generated markup works; it just doesn't match `AGENTS.md`. Normalize it if you keep
  the output.
- **`theme.json`.** It rewrites the file wholesale (atomic temp-file plus rename).
  This theme's `theme.json` is hand-curated with ordering and comments that a
  round-trip will not preserve. Treat any `theme.json` diff from this plugin as
  something to review line by line, not accept.
- **Generated pattern headers.** Slugs and titles come from the template slug, not
  from this theme's naming conventions, and won't carry the `Description` or
  `Categories` that `SKILL.md` requires.

## Using it as route 2

It is a good extraction tool and a poor authoring authority. Use it for pulling
uploads into the theme, dereferencing synced patterns, and getting a first-draft
file out of Site Editor work.

```
- [ ] 1. Commit first — the working tree must be clean
- [ ] 2. Activate the plugin
- [ ] 3. Run only the action you need
- [ ] 4. git diff — keep what you meant, revert the rest
- [ ] 5. Normalize get_template_directory_uri() to get_theme_file_uri()
- [ ] 6. Add the pattern header it didn't write: Description, Categories, theme slug
- [ ] 7. Run the portability audit and cleanup rules on the output
- [ ] 8. Deactivate
```

Step 1 is the whole safety story — every write it makes is visible to git, so a clean
tree turns an overwrite from a loss into a `git checkout`.

Step 7 is not optional. What it generates still needs `metadata` stripping,
`tagName:"section"`, presets over raw values, and the placeholder conventions in
[cleanup-rules.md](cleanup-rules.md).

**It cannot make the content-selection judgement either.** Nothing in this plugin
knows whether a term or post ID in a block attribute is deliberate curation or
an accident, so the *Stop and ask* step in `SKILL.md` applies identically on this
route. The plugin narrows the mechanical work; the question you put to the user is
unchanged.

Leave it deactivated when not actively using it.
