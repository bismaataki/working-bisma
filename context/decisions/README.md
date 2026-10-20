# decisions/

Architectural decision records. **Permanent and numbered**: `ADR-NNNN-slug.md`,
zero padded to four digits, numbers never reused.

An ADR is never deleted and its body is never rewritten to reflect a change of
mind. To change a decision, add a new ADR that supersedes the old one and set
the old one's status to `Superseded`. Keeping the superseded reasoning, wrong
turns included, is the reason this folder exists.

`status` (`Decided`, `Superseded`, `Reversed`), the date, and `supersedes` go in
front matter. The body carries the context, the options, the call, and the
consequences accepted.

The current *shape* of a decision still belongs in `PROJECT.md`, `context/kb/`,
or a `.cursor/rules/` file, because that is where someone looks while writing
code. The ADR records why, not what.

Numbering, superseding, and when a plan needs an ADR alongside it: the
`sf-context-docs` skill.
