# Skaflow Changelog

The timeline. One entry per shipped feature and per architectural decision, so
a future reader can see what happened and when without reading `git log`.

**Newest first. Append at the top, never edit an entry after the fact.** If you
want to correct the record, add a later entry that supersedes it. For current
status and open items, including what is still outstanding, see `PROGRESS.md`.

Not every commit belongs here. Refactors, lint fixes, and build chores do not.
The bar is whether a human catching up in a year would want it on a timeline.
Conventions and the close-out procedure: the `sf-context-docs` skill.

Format:

```markdown
## yyyy-mm-dd

- **Short title.** One or two sentences on what changed and why it matters.
  `implementation/yyyy-mm-dd-slug.md`, `ADR-NNNN`, `<commit>`.
```

<!-- Entries begin below. -->

## 2026-09-11

- **@wordpress/scripts 35.** Toolchain bump from 31.3. JSX blocks register
  from `blocks-manifest.php` (`ADR-0001`). The blocks build copies sibling
  PHP (`ADR-0002`). `implementation/2026-09-11-wordpress-scripts-35.md`.

- **Swiper 14.2.0.** Runtime bump from 11.2.10 to clear CVE-2026-27212. Theme
  nav keeps CSS chevrons (`addIcons: false`). Shop the Look is not a Swiper
  consumer. `PROJECT.md` § Stack.
