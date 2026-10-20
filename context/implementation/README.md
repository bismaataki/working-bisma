# implementation/

How a feature actually landed: what shipped, the file map, the commit, and
anything the plan did not anticipate.

**One file per shipped feature, named `yyyy-mm-dd-slug.md` with the same date
and slug as the plan it replaces.** That matching filename is the relation
between the two, which matters because the plan is deleted on landing and this
note is what survives it. The date is the plan's date; the ship date goes in
front matter alongside the commit.

These are permanent. Keep them short: a pointer to the code and the commit, not
a second copy of the plan. Durable facts belong in `context/kb/` or a rule.

Close-out procedure and the front matter contract: the `sf-context-docs` skill.
