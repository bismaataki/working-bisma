---
name: sf-context-docs
description: Write and maintain the files under context/. Covers naming plans, implementation notes and notes with a date prefix, numbering decisions as ADRs, linking a plan to the implementation note that replaces it, and appending to the changelog when a feature ships. Use when starting a plan, recording an architectural decision, closing out work that just landed, or when asked where a piece of research, a finding, or a decision should be written.
---

# Writing in context/

`context/` is working memory. **Code is the source of truth**, so every file
here exists only to stop the next agent rediscovering something. That gives
each file a lifetime, and knowing which lifetime you are writing into is the
whole job.

This skill owns the conventions. `context/README.md` describes the loop for a
human reader and points here rather than restating any of it.

## Lifetimes

| Directory | Lives | Dies |
|---|---|---|
| `plans/` | While the work is in flight | Deleted when the feature ships |
| `implementation/` | Permanently | Never. It is the plan's surviving counterpart |
| `notes/` | Until promoted | Deleted once its findings reach `kb/` or a rule |
| `decisions/` | Permanently | Never. Superseded by a later ADR, never deleted |
| `kb/` | Permanently, edited in place | Never, but regenerate maps rather than patching |
| `CHANGELOG.md` | Permanently, append-only | Never. Entries are not edited after the fact |
| `PROGRESS.md` | Permanently, edited in place | Never |

`PROGRESS.md` and `CHANGELOG.md` look similar and are not. `PROGRESS.md`
answers "what is the state now, what is still open" and gets edited in place.
`CHANGELOG.md` answers "what happened when" and is only ever added to. If you
find yourself editing a past changelog entry, you wanted `PROGRESS.md`.

## Naming

| Directory | Pattern | Example |
|---|---|---|
| `plans/` | `yyyy-mm-dd-slug.md` | `2026-09-11-articles-in-primary-nav.md` |
| `implementation/` | Same date, same slug as the plan | `2026-09-11-articles-in-primary-nav.md` |
| `notes/` | `yyyy-mm-dd-slug.md` | `2026-09-11-menu-seeder-audit.md` |
| `decisions/` | `ADR-NNNN-slug.md`, zero padded to four | `ADR-0003-menu-seeding-is-a-bootstrap.md` |
| `kb/` | Topic prefix, **no date** | `map-edit.md`, `plugin-wc-my-account.md` |

**`kb/` never carries a date** because it describes the tree as it is now. A
date on a derived fact invites a reader to treat it as history and trust a
stale copy. `kb/README.md` owns those prefixes.

**The date is the plan's date, in both places.** The implementation note
inherits it rather than using the ship date, because the matching filename *is*
the relation between the two. The ship date goes in front matter and in the
changelog, where it is a fact about an event rather than an identifier.

## Front matter

Minimal, machine-readable, and only where it carries something a filename
cannot. Plans:

```markdown
---
status: in progress
started: 2026-09-11
decisions: [ADR-0003]
---
```

Implementation notes:

```markdown
---
plan: 2026-09-11-articles-in-primary-nav
shipped: 2026-09-11
commit: 658a4f0
decisions: [ADR-0003]
---
```

The `plan:` key is redundant with the filename on the day it is written and
stops being redundant the moment a slug is corrected. Keep it.

## Starting a plan

1. `context/plans/<today>-<slug>.md`, using today's real date. Do not guess it
   from a nearby file.
2. Write the approach, the alternatives you rejected and why, and how you will
   verify it. Rejected alternatives are the part a future reader cannot
   reconstruct from the code.
3. If the plan turns on an architectural call that outlives the feature, write
   an ADR too and cross-reference both ways. A plan is not a decision record:
   the plan is deleted, the ADR is not.

## Recording a decision

ADRs are **permanent and authoritative**. Do not delete one, and do not edit a
Decided file's body to reflect a change of mind.

1. Next number is the highest existing plus one. Never reuse a number, even
   for an ADR that was reversed the same day.

   ```bash
   ls context/decisions/ADR-*.md
   ```

2. Front matter carries `status` (`Decided`, `Superseded`, `Reversed`), the
   date, and `supersedes` where it applies.
3. Body: the context that forced a choice, the options, the call, and the
   consequences you accepted. The consequences are what makes it referenceable
   later.
4. To change a decision, write a **new** ADR with `supersedes: ADR-NNNN`, then
   set the old one's status to `Superseded` and add a single
   `superseded-by:` key. Leave the rest of the old file alone, including
   anything it got wrong. That record is the reason the folder exists.

The current *shape* of a decision still belongs in `PROJECT.md`, `kb/`, or a
rule, because that is where someone will look while writing code. The ADR is
why, not what.

## Closing out a shipped feature

Run all five steps. Skipping the promotion step is how a deleted plan takes a
fact with it.

1. **Promote the durable facts** into `kb/`, a `.cursor/rules/` file, or a
   skill. A convention goes in a rule, a procedure in a skill, a derived fact
   in `kb/`.
2. **Write `implementation/<plan-date>-<slug>.md`**: what shipped, the file
   map, the commit, and anything discovered during the build that the plan did
   not anticipate. Keep it short. It is a pointer, not a second plan.
3. **Append to `CHANGELOG.md`**, newest first, one entry per shipped feature.
4. **Delete the plan.** Only after steps 1 and 2, and only once nothing in it
   is the sole copy of a current fact.
5. **Update `PROGRESS.md`** if the work closes or opens a tracked item. Flip an
   item when it is verified, not when it is committed.

## What not to do

- Do not invent a new top-level directory under `context/`. Add a `.md` file to
  the directory that already fits.
- Do not leave a finished plan in `plans/` as the only copy of a current fact.
  That is the failure the lifetimes above exist to prevent.
- Do not date a `kb/` file, and do not number anything outside `decisions/`.
- Do not write a changelog entry for a refactor, a lint fix, or a commit. The
  changelog is features and decisions, at the granularity a future human would
  want as a timeline. `git log` already covers the rest.
