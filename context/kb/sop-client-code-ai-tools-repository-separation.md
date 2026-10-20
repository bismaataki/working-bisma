# Client Code / Private AI Tooling Repository Separation
## Knowledge Base / SOP

**Purpose:** Standard operating procedure for separating a client-owned code repository from private agency/developer AI tooling, research, instructions, and working context while preserving a seamless local development experience.

**Primary use case:** WordPress or similar projects where:

- the client repository must contain only deployable/maintainable project code;
- private AI instructions, agent rules, research, plans, notes, design references, and internal development tooling must remain outside the client repository;
- AI tooling such as `AGENTS.md`, `.cursor/`, `PROJECT.md`, and `context/` needs to appear at the project root locally;
- both repositories should remain independently versioned;
- the client repository may later be connected to CI/CD, hosting, or another deployment system.

**How to apply:** This SOP is generic. Use it when a future project splits
client-owned code from private AI tooling. Examples in the body that name a
past client are illustrations of the procedure, not this tree's remotes.

**This copy (skaflow):** one in-tree repo. Theme, WordPress install, and AI
tooling (`AGENTS.md`, `PROJECT.md`, `context/`, `.cursor/`) live together.
`setup.sh` is unused until a project actually splits. Remotes and environment
names for a given build belong in that build's `PROJECT.md`.

**Related:** Client-repo deployment is
`context/kb/sop-github-to-wpengine-gitpush.md`. Only the client
repository deploys.

---

# 1. Core Principle

Treat the project as two separate ownership domains:

```text
Client Repository
    = deliverable/runtime/client-maintainable code

Private AI Tools Repository
    = agency/developer instructions, context, research, tooling
```

Locally, these repositories can coexist and appear as one working project by exposing the private AI repository into the client project root using symbolic links.

The key rule is:

> The client repository owns the application.  
> The private AI repository owns the development intelligence around the application.

---

# 2. Final Architecture

Recommended local layout:

```text
~/Projects/wp/

chairforce-com-au/
│
│  Git remote:
│  Nextandco/chairforce-com-au
│
├── .git/
├── wp-content/
├── .gitignore
├── README.md
├── package.json
├── ...
│
├── AGENTS.md      -> ../chairforce-com-au-ai-tools/AGENTS.md
├── PROJECT.md     -> ../chairforce-com-au-ai-tools/PROJECT.md
├── context        -> ../chairforce-com-au-ai-tools/context
├── .cursor        -> ../chairforce-com-au-ai-tools/.cursor
└── .cursorignore  -> ../chairforce-com-au-ai-tools/.cursorignore


chairforce-com-au-ai-tools/
│
│  Git remote:
│  atakidevelopers/chairforce-com-au-ai-tools
│
├── .git/
├── AGENTS.md
├── PROJECT.md
├── context/
├── .cursor/
├── .cursorignore
├── hooks/
│   └── client-pre-push   # installed locally into the client .git/hooks
└── setup.sh              # overlay + local hook installer
```

From Cursor/Claude/agent tooling, the client project still appears to contain:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
.cursorignore
```

at the repository root.

From Git's perspective, ownership remains cleanly separated.

---

# 3. Why Two Repositories

A single mixed repository creates several problems:

- client code and private agency tooling become coupled;
- AI rules and internal research can accidentally be exposed to the client;
- internal reusable framework knowledge can become client-owned history;
- deployment systems may receive irrelevant AI/context files;
- future starter-theme extraction becomes harder;
- private development methodology can leak through Git history;
- the client repository becomes noisy and harder to maintain.

Separate repositories solve these concerns while allowing a unified local workspace.

---

# 4. What Belongs in the Client Repository

The client repository should contain only files that are appropriate to:

- deliver to the client;
- deploy to hosting;
- maintain long term;
- expose to future developers;
- review through Pull Requests;
- keep in the client's GitHub organization.

Examples:

```text
wp-content/themes/chairforce/
wp-content/plugins/chairforce-woodmart-data-normalise/
wp-content/plugins/chairforce-postcode-pricing/
README.md
package.json
package-lock.json
gulpfile.babel.js
.gitignore
.github/
other intentional runtime/project files
```

The exact list is project-specific.

The repository's `.gitignore` should act as an explicit ownership boundary.

---

# 5. What Belongs in the Private AI Tools Repository

For Chairforce, the private repository owns:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
.cursorignore
```

Typical responsibilities:

## `AGENTS.md`

Project/starter operating instructions for coding agents.

Examples:

- commands
- code layout
- conventions
- non-negotiables
- testing expectations
- framework-specific rules
- editing guidance

## `PROJECT.md`

Project-specific identity and technical context.

Examples:

- project name
- theme identity
- stack
- known technical debt
- environment notes
- project-specific architecture

## `.cursor/`

Cursor-specific rules and skills.

May include:

```text
.cursor/rules/
.cursor/skills/
.cursor/settings.json
```

Typical distinction:

```text
sf-*       reusable starter/framework rules
project-*  project-specific rules
```

## `.cursorignore`

Cursor search/index exclusions.

Example:

```text
build/
build-jsx-blocks/
```

## `context/`

Agent/human knowledge base.

Typical content:

```text
context/PROGRESS.md
context/kb/
context/existing-functionality/
context/plans/
context/decisions/
context/notes/
context/figma/
context/implementation/
```

This material can be highly valuable internally without belonging in the client repository.

---

# 6. Why Symlinks Are Used

Some AI tools expect files such as:

```text
AGENTS.md
.cursor/
PROJECT.md
context/
```

to exist at the project root.

Simply keeping the AI repository as a sibling directory would break that expectation.

Symlinks solve this:

```text
client-project/AGENTS.md
    ->
../client-project-ai-tools/AGENTS.md
```

The agent sees the file at the project root.

The actual file remains physically stored and versioned in the private repository.

This provides:

```text
Correct local path for agents
+
Clean Git ownership
```

---

# 7. Why Not Use a Git Submodule

A Git submodule is usually not appropriate for this pattern.

Problems:

- it creates a reference to the private repository inside the client repository;
- the client Git history becomes aware of the private repo;
- private repository access may be required for cloning;
- `AGENTS.md` still would not naturally exist at repo root;
- operational complexity increases;
- client ownership boundaries become less clean.

A submodule is useful when the client repository intentionally depends on another repository.

That is not the case here.

The client repository should have no knowledge that the private AI tooling repository exists.

---

# 8. Why Not Use Git Subtree

A Git subtree is also inappropriate.

It would copy or merge private tooling into the client repository's history.

That defeats the purpose of separation.

---

# 9. Why Not Use Two Git Repositories on the Same Physical Tree

Do not attempt to make two `.git` directories independently manage overlapping files in the same physical working tree.

Although advanced Git techniques can make this possible, it creates dangerous ambiguity around:

```text
git status
git add
git reset
git clean
git checkout
git restore
```

The safer pattern is:

```text
two independent sibling working trees
+
symlink overlay
```

---

# 10. Why Not Manually Copy AI Files Into the Client Working Tree

Copying files creates drift.

Example:

```text
chairforce-com-au/AGENTS.md
chairforce-com-au-ai-tools/AGENTS.md
```

would become two independent files.

Sooner or later:

- one gets updated;
- the other becomes stale;
- agents read the wrong version;
- developers forget to synchronize them.

Symlinks eliminate this duplication.

---

# 11. Migration Strategy When Git History Does Not Need to Be Preserved

If the old repository history does not need to move to the client, the cleanest strategy is:

```text
Old working repository
    ->
extract private AI tooling
    ->
remove old .git
    ->
initialize fresh client Git repository
```

Do not use:

```text
git filter-repo
repository transfer
history rewrite
```

unless history has value.

For Chairforce, history was intentionally discarded for the new client repository.

---

# 12. Pre-Migration Safety Checks

Before changing anything:

1. Stop DDEV/local services if the project directory will be renamed.
2. Close Cursor/IDE workspace.
3. Confirm the current repository state:

```bash
git status
git remote -v
git log -1 --oneline
```

4. Ensure important local work is not uncommitted or otherwise at risk.
5. Confirm the old remote repository can remain as an archive.

Do not commit/push AI-file removal to the old repository if the intention is to preserve it as a historical snapshot.

---

# 13. Rename the Project Directory

Example:

```bash
cd ~/Projects/wp
mv chairforce-2026 chairforce-com-au
```

After renaming, remember:

- DDEV project naming may still reference the old name;
- IDE workspace paths may need reopening;
- shell aliases/scripts may contain old paths;
- local proxy/domain configuration may need review.

Do not run old Git commits/pushes after moving AI files if the old remote is meant to remain untouched.

---

# 14. Extract the Private AI Layer

Create the sibling private repository directory:

```bash
cd ~/Projects/wp
mkdir chairforce-com-au-ai-tools
```

Move the private tooling:

```bash
mv chairforce-com-au/AGENTS.md \
   chairforce-com-au-ai-tools/

mv chairforce-com-au/PROJECT.md \
   chairforce-com-au-ai-tools/

mv chairforce-com-au/context \
   chairforce-com-au-ai-tools/

mv chairforce-com-au/.cursor \
   chairforce-com-au-ai-tools/

mv chairforce-com-au/.cursorignore \
   chairforce-com-au-ai-tools/
```

At this point:

```text
chairforce-com-au/
    client/runtime code only

chairforce-com-au-ai-tools/
    private AI/tooling layer
```

---

# 15. Important: Do Not Push the Extraction to the Old Remote

After moving the AI files, the old `.git` still sees them as deleted.

Do **not** run:

```bash
git add .
git commit
git push
```

against the old remote.

That would update the historical repository with a commit that deletes all AI tooling.

Instead, if the old remote should remain a snapshot:

```bash
rm -rf chairforce-com-au/.git
```

without committing or pushing.

The remote repository remains untouched.

---

# 16. Clean the Client `.gitignore`

Remove any temporary rules that explicitly re-included private AI files.

Example rules to remove:

```gitignore
!.cursor/
!.cursorignore
!context/
!AGENTS.md
!PROJECT.md
```

These rules were useful while AI tooling was intentionally tracked in the old internal repo.

They should not exist in the new client repository.

---

# 17. Example Client `.gitignore` Pattern

Chairforce uses a restrictive deployment-oriented root rule:

```gitignore
/*
```

and explicitly allows project-owned paths.

Example structure:

```gitignore
# Ignore everything at repository root
/*

# Explicit root files
!.gitignore
!.gitmodules
!.npmrc
!.babelrc
!.github/
!bitbucket-pipelines.yml
!gulpfile.babel.js
!package-lock.json
!/package.json
!/README.md

# Allow wp-content
!wp-content/

# Themes
wp-content/*
!wp-content/themes/
wp-content/themes/*
!wp-content/themes/chairforce/
wp-content/themes/*/node_modules

# Plugins
!wp-content/plugins/
wp-content/plugins/*
!wp-content/plugins/chairforce-woodmart-data-normalise/
!wp-content/plugins/chairforce-postcode-pricing/

# Developer tooling
.idea/
.vscode/
.cursor/
.cursorignore
.cursorrules
.claude/
.codex/
.copilot/
.windsurf/
.cline/
.roo/
.continue/
.gemini/
.amazonq/
.tabnine/
.augment/
.cody/
.qodo/
.devin/

# Developer instruction files
CLAUDE.md
CLAUDE.local.md
AGENT.md
AGENTS.md
AGENTS.local.md
AGENTS.override.md
GEMINI.md

# Developer tool configuration
.mcp.json
.clinerules
.clinerules/
.clineignore
.rooignore
.roomodes
.roorules
.roorules-*
.windsurfrules
.codeiumignore
.continueignore
.aider*
.aider.conf.yml
.tabnineignore
.augmentignore
.codyignore
.aiignore
```

`PROJECT.md` and `context/` are already excluded by the root `/*` rule unless explicitly re-allowed.

---

# 18. Remove the Old Git Repository Metadata

Once the private tooling has been extracted and `.gitignore` cleaned:

```bash
cd ~/Projects/wp/chairforce-com-au
rm -rf .git
```

This completely disconnects the local working tree from the old remote.

The old GitHub repository remains unchanged.

---

# 19. Initialize the New Client Repository

Create a fresh Git repository:

```bash
git init -b main
```

Add the client remote:

```bash
git remote add origin \
  git@github-nc:Nextandco/chairforce-com-au.git
```

Verify:

```bash
git remote -v
```

---

# 20. Protect Private Paths Before First Commit

Even when `.gitignore` already excludes AI tooling, add an additional local-only safety layer using:

```text
.git/info/exclude
```

Example:

```gitignore
AGENTS.md
PROJECT.md
context
.cursor
.cursorignore
```

Why:

- `.git/info/exclude` is not committed;
- it protects against accidental local staging;
- it allows the client `.gitignore` to stay focused on project-level rules;
- it helps protect future symlink overlays.

This is defense in depth.

---

# 21. Verify What Git Will Track Before the First Commit

Before staging:

```bash
git add -n .
```

This is a dry run.

Review every path.

Expected categories should be only client-owned code such as:

```text
.gitignore
wp-content/themes/chairforce/...
wp-content/plugins/chairforce-woodmart-data-normalise/...
wp-content/plugins/chairforce-postcode-pricing/...
README.md
package.json
...
```

AI/private paths must not appear.

Useful checks:

```bash
git check-ignore -v AGENTS.md
git check-ignore -v PROJECT.md
git check-ignore -v context/
git check-ignore -v .cursor/
```

---

# 22. Stage and Inspect

After the dry run is clean:

```bash
git add .
```

Inspect:

```bash
git status
```

and:

```bash
git diff --cached --name-only
```

Optional private-file guard:

```bash
git diff --cached --name-only \
  | grep -E '(^|/)(AGENTS\.md|PROJECT\.md|context|\.cursor|\.cursorignore)'
```

Expected:

```text
no output
```

Only then make the first client commit.

---

# 23. First Client Commit

Example:

```bash
git commit -m "Initial Chairforce codebase"
git push -u origin main
```

At this point:

```text
Nextandco/chairforce-com-au
```

becomes the authoritative client repository.

The old internal repository can later be archived.

---

# 24. Correcting the First Commit Author If Needed

If the first commit was accidentally created with the wrong Git identity:

Configure the correct repository identity:

```bash
git config user.name "RaoNC"
git config user.email "rao@nextandco.com.au"
```

Amend:

```bash
git commit --amend --reset-author --no-edit
```

Verify:

```bash
git log -1 --format=fuller
```

Then update the remote:

```bash
git push --force-with-lease origin main
```

Use `--force-with-lease`, not plain `--force`.

This is especially safe/easy when the repository contains only the initial commit.

---

# 25. Initialize the Private AI Repository

Now initialize the sibling tooling directory:

```bash
cd ~/Projects/wp/chairforce-com-au-ai-tools

git init -b main
```

Add its private internal remote:

```bash
git remote add origin \
  git@github.com:atakidevelopers/chairforce-com-au-ai-tools.git
```

Adjust the SSH alias to match the developer's configuration if necessary.

Commit:

```bash
git add .
git commit -m "Initial Chairforce AI tooling"
git push -u origin main
```

---

# 26. Create the Symlink Overlay

Return to the client repository:

```bash
cd ~/Projects/wp/chairforce-com-au
```

Create:

```bash
ln -s ../chairforce-com-au-ai-tools/AGENTS.md AGENTS.md

ln -s ../chairforce-com-au-ai-tools/PROJECT.md PROJECT.md

ln -s ../chairforce-com-au-ai-tools/context context

ln -s ../chairforce-com-au-ai-tools/.cursor .cursor

ln -s ../chairforce-com-au-ai-tools/.cursorignore .cursorignore
```

The client working directory now appears complete to AI tools.

---

# 27. Verify the Symlinks

Run:

```bash
ls -la
```

Expected:

```text
AGENTS.md -> ../chairforce-com-au-ai-tools/AGENTS.md
PROJECT.md -> ../chairforce-com-au-ai-tools/PROJECT.md
context -> ../chairforce-com-au-ai-tools/context
.cursor -> ../chairforce-com-au-ai-tools/.cursor
.cursorignore -> ../chairforce-com-au-ai-tools/.cursorignore
```

Verify content:

```bash
head AGENTS.md
head PROJECT.md
ls context
ls .cursor
```

---

# 28. Verify Both Git Repositories Are Clean

Client repo:

```bash
cd ~/Projects/wp/chairforce-com-au
git status
```

Expected:

```text
nothing to commit, working tree clean
```

The symlinks should not appear as untracked files because of `.gitignore` and/or `.git/info/exclude`.

Private AI repo:

```bash
cd ~/Projects/wp/chairforce-com-au-ai-tools
git status
```

Expected:

```text
nothing to commit, working tree clean
```

---

# 29. Reopen the IDE/Agent Workspace

Open Cursor/IDE using the **client project directory**:

```text
~/Projects/wp/chairforce-com-au
```

Do not open only the AI repo for normal project work.

The project root then exposes:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
```

exactly where the agent expects them.

---

# 30. DDEV / Local Environment Check

If the project directory was renamed:

```text
chairforce-2026
->
chairforce-com-au
```

review DDEV configuration for:

```text
name:
PROXY=
hostnames
router settings
project URLs
scripts containing absolute paths
```

Then restart:

```bash
ddev start
```

Verify the local site still works.

---

# 31. Normal Development Workflow

## Client code change

Work normally inside:

```text
chairforce-com-au/
```

Example:

```bash
cd ~/Projects/wp/chairforce-com-au

git switch develop
git pull

# edit code

git add wp-content/...
git commit -m "Update product card"
git push
```

This goes to the client repository.

---

# 32. AI Tooling Change

If an AI rule, KB document, plan, or project note changes:

```bash
cd ~/Projects/wp/chairforce-com-au-ai-tools

git status
git add .
git commit -m "Update WooCommerce agent guidance"
git push
```

Even though the file may have been edited through the symlink while Cursor was open in the client directory, Git ownership remains with the AI tools repository.

---

# 33. Editing Symlinked Files From Cursor

A developer may edit:

```text
chairforce-com-au/AGENTS.md
```

inside Cursor.

Because it is a symlink, the physical file being changed is:

```text
chairforce-com-au-ai-tools/AGENTS.md
```

Therefore:

```bash
cd chairforce-com-au
git status
```

should remain clean.

But:

```bash
cd ../chairforce-com-au-ai-tools
git status
```

should show:

```text
modified: AGENTS.md
```

This is expected and desirable.

---

# 34. Bootstrap Script

The private repo ships:

```text
chairforce-com-au-ai-tools/setup.sh
```

It:

- creates the five overlay symlinks at the client root;
- appends overlay paths to the client `.git/info/exclude` if missing;
- installs the local client `pre-push` hook (see §35A).

It refuses to overwrite a real (non-symlink) overlay file or an unrelated
`pre-push` hook.

Usage:

```bash
cd chairforce-com-au-ai-tools
./setup.sh ../chairforce-com-au
```

Re-running is safe when the overlay already exists as symlinks.

---

# 35. Recommended Local Pre-Commit Guard

Optional defense in depth:

Create a local Git pre-commit hook in:

```text
chairforce-com-au/.git/hooks/pre-commit
```

Example:

```bash
#!/usr/bin/env bash

set -e

blocked_regex='(^|/)(AGENTS\.md|PROJECT\.md|context(/|$)|\.cursor(/|$)|\.cursorignore$)'

blocked="$(
  git diff --cached --name-only \
    | grep -E "$blocked_regex" || true
)"

if [[ -n "$blocked" ]]; then
  echo "ERROR: private AI tooling is staged in the client repository:"
  echo "$blocked"
  echo
  echo "Commit aborted."
  exit 1
fi
```

Enable:

```bash
chmod +x .git/hooks/pre-commit
```

This hook is local and is not committed by default.

---

# 35A. Client Pre-Push Reminder

Usual Chairforce work touches both repositories in one session, then pushes
the client remote. The AI-tools repo is easy to leave dirty because Cursor
is opened on the client root.

A local client `pre-push` hook blocks that push when the AI-tools working
tree is dirty or ahead of its upstream.

Source (versioned in the private repo):

```text
chairforce-com-au-ai-tools/hooks/client-pre-push
```

Installed by `setup.sh` as a symlink that is **not** part of client Git
history:

```text
chairforce-com-au/.git/hooks/pre-push
    ->
../chairforce-com-au-ai-tools/hooks/client-pre-push
```

Behaviour:

1. `SKIP_AI_TOOLS_PUSH_CHECK=1` — skip (explicit client-only push).
2. No `AGENTS.md` overlay symlink — skip (client-only clone).
3. Overlay target is not a Git repo — skip.
4. AI-tools `git status --porcelain` is non-empty, or `HEAD` is ahead of
   `@{upstream}` — **block** and print the AI-tools path plus next steps.

The hook discovers the AI-tools repo by resolving the `AGENTS.md` symlink,
so it does not hard-code a sibling directory name. It unsets `GIT_DIR` and
`GIT_WORK_TREE` before calling Git in that repo (a client hook otherwise
inspects the client `.git`).

Bypass:

```bash
SKIP_AI_TOOLS_PUSH_CHECK=1 git push
```

Do **not** commit this hook to `Nextandco/chairforce-com-au`. That would
advertise the private overlay. Keep it local, same as `.git/info/exclude`.

This does not catch a session that only edited AI-tools files and never
pushed the client repo. After those sessions, `git status` in the AI-tools
tree (or the `AGENTS.md` closing reminder) is still required.

---

# 36. Why `.git/info/exclude` Is Valuable

`.gitignore` is project-shared policy.

`.git/info/exclude` is developer-local policy.

For symlink overlays, local exclusion is useful because the symlinks exist only on developer machines and should never become part of client history.

Recommended local entries:

```gitignore
AGENTS.md
PROJECT.md
context
.cursor
.cursorignore
```

This protects the client repo even if `.gitignore` changes later.

---

# 37. Do Not Commit the Symlinks

A symlink itself is a Git object if staged.

If accidentally committed, the client repository could contain:

```text
AGENTS.md -> ../chairforce-com-au-ai-tools/AGENTS.md
```

That would be bad because:

- the path is developer-machine-specific;
- clients do not have the private repository;
- CI/hosting would receive broken links;
- private tooling existence becomes visible;
- clones become confusing.

Therefore symlinks must remain local-only.

---

# 38. Protect Against `git add -f`

Normal ignore rules can be overridden with:

```bash
git add -f
```

Therefore no ignore system is absolute.

Mitigations:

- `.gitignore`
- `.git/info/exclude`
- local pre-commit guard
- code review
- `git diff --cached --name-only` before important commits

---

# 39. Cloning the Project on a New Machine

Recommended setup:

```bash
cd ~/Projects/wp

git clone \
  git@github-nc:Nextandco/chairforce-com-au.git

git clone \
  git@github.com:atakidevelopers/chairforce-com-au-ai-tools.git
```

Then:

```bash
cd chairforce-com-au-ai-tools
./setup.sh ../chairforce-com-au
```

Open:

```text
chairforce-com-au
```

in Cursor/IDE.

This reconstructs the complete working environment.

---

# 40. Developer Without Private AI Access

A normal client developer can clone only:

```text
Nextandco/chairforce-com-au
```

They receive a fully valid codebase without:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
```

This is intentional.

The runtime/build/deployment must never depend on those private files.

---

# 41. Runtime Independence Requirement

Before separating repositories, verify:

> No runtime code imports, reads, requires, or references the AI tooling layer.

Examples that must **not** exist:

```php
require 'AGENTS.md';
```

```js
readFileSync('context/...');
// runtime dependency
```

```text
theme build requiring .cursor/
```

AI/private files must be development-only.

Chairforce satisfied this requirement.

---

# 42. Build Pipeline Independence

If the project has:

```text
npm
gulp
webpack
Vite
Composer
tests
deployment workflows
```

ensure none depend on private AI files.

The client repo must remain independently:

- buildable
- testable
- deployable
- maintainable

without cloning the AI tools repo.

---

# 43. README Ownership

Human onboarding documentation such as:

```text
README.md
```

usually belongs in the client repository.

It should document things future maintainers genuinely need:

- setup
- build commands
- architecture
- deployment
- project-specific maintenance

It should not become a substitute for private AI agent instructions.

---

# 44. `PROJECT.md` Decision Rule

`PROJECT.md` is often AI-oriented and project-specific.

Default:

```text
private AI repo
```

Move content into the client `README.md` only if it is genuinely useful to human maintainers and appropriate for client ownership.

Do not include `PROJECT.md` in the client repo merely because it describes the project.

---

# 45. Context Folder Decision Rule

The `context/` directory should be reviewed by category.

Typical examples:

## Usually private

```text
context/plans/
context/notes/
context/decisions/
context/figma/
context/existing-functionality/
context/header-mega-menu/
context/implementation/
```

Reasons:

- internal thought process
- temporary research
- project investigation
- derived design references
- old implementation plans
- agent-specific material

## Potentially reusable internally

```text
context/kb/
```

This can later seed a starter framework or internal knowledge base.

But it still does not need to live in the client repository.

---

# 46. Cursor Rules Decision Rule

For project repositories that evolve from an internal starter:

```text
.cursor/rules/sf-*
```

may represent reusable starter conventions.

```text
.cursor/rules/project-*
```

may represent project-only rules.

During the client/AI split, both can remain in the private project AI repository.

Later, when extracting a starter framework, separate reusable starter material from project-specific material.

Do not mix that extraction into the client repository migration unless necessary.

---

# 47. Starter Framework Extraction Is a Separate Task

For Chairforce:

```text
chairforce-com-au
```

and:

```text
chairforce-com-au-ai-tools
```

are project-specific outputs.

A future:

```text
skaflow
```

starter repository should be created separately.

That later process may:

- rename Chairforce identifiers to Skaflow;
- retain reusable `sf-*` rules;
- remove `project-*` rules;
- retain reusable KB content;
- remove Chairforce-specific plans/research;
- generalize theme code;
- clean project-specific assets/integrations.

Do not make the client repo migration depend on finishing the starter extraction.

---

# 48. Old Repository Archival

After the new client repo and AI tools repo are proven:

```text
atakidevelopers/chairforce-2026
```

can be archived.

Recommended checks before archiving:

- client repo clone works;
- AI tools repo clone works;
- symlink overlay works;
- both Git statuses are clean;
- Cursor reads root AI files correctly;
- local site works;
- deployment works;
- no needed history exists only in the old repo.

Do not immediately delete the old repository.

Archive first.

---

# 49. Recovery If a Private File Is Accidentally Committed

If the private file is committed but not yet pushed:

```bash
git restore --staged AGENTS.md
```

or:

```bash
git rm --cached AGENTS.md
```

Then fix ignore rules and amend the commit.

If already pushed, consider whether the content is sensitive.

Removing it in a later commit does **not** remove it from Git history.

For sensitive/private content, repository history may need rewriting.

Prevention is much easier than cleanup.

---

# 50. Recovery If the Wrong Repository Was Modified

If a symlinked file is edited, remember it belongs to the AI repo.

Check:

```bash
git status
```

in both repositories before committing.

Client:

```bash
cd chairforce-com-au
git status
```

AI:

```bash
cd ../chairforce-com-au-ai-tools
git status
```

Never assume changes belong to the currently open IDE root.

---

# 51. Recovery If a Symlink Breaks

Check:

```bash
ls -la AGENTS.md
```

If the sibling AI repo moved, recreate the link:

```bash
rm AGENTS.md

ln -s \
  ../chairforce-com-au-ai-tools/AGENTS.md \
  AGENTS.md
```

Or rerun:

```bash
../chairforce-com-au-ai-tools/setup.sh .
```

---

# 52. Recovery If Cursor Stops Seeing `.cursor/`

Check:

```bash
ls -la .cursor
```

Verify the symlink target exists.

Then close/reopen the Cursor workspace.

Some editor features may not immediately re-index a directory after it has been moved or relinked while the workspace is open.

---

# 53. Security Considerations

Private AI repositories may contain:

- internal implementation reasoning;
- client research;
- design screenshots;
- architectural notes;
- agent instructions;
- code-edit maps;
- reusable agency intellectual property;
- deployment observations;
- non-public project details.

Therefore:

- keep the AI repo private;
- restrict organization/repository access;
- do not deploy it;
- do not expose it through client Git history;
- do not store credentials or secrets in it merely because it is private.

Private Git is not a secrets manager.

---

# 54. Secrets Still Belong Elsewhere

Even the private AI repo should not contain:

```text
SSH private keys
API secrets
database passwords
GitHub PATs
WP Engine credentials
production secrets
```

Use:

- local SSH key storage;
- GitHub repository secrets;
- WP Engine secret/config systems;
- environment variables;
- password manager/secrets manager.

---

# 55. Chairforce Reference: Final Repository Ownership

## Client Repository

```text
GitHub:
Nextandco/chairforce-com-au
```

Owns:

```text
wp-content/themes/chairforce/
wp-content/plugins/chairforce-woodmart-data-normalise/
wp-content/plugins/chairforce-postcode-pricing/
.gitignore
.github/
README.md
package.json
package-lock.json
other explicitly tracked project files
```

## Private AI Repository

```text
GitHub:
atakidevelopers/chairforce-com-au-ai-tools
```

Owns:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
.cursorignore
hooks/client-pre-push
setup.sh
```

## Historical Repository

```text
atakidevelopers/chairforce-2026
```

Role:

```text
archive / historical snapshot
```

No longer the active project repository.

---

# 56. Chairforce Reference: Local Layout

```text
/home/rao/Projects/wp/

chairforce-com-au/
├── .git/
├── .gitignore
├── .github/
├── wp-content/
├── AGENTS.md      -> ../chairforce-com-au-ai-tools/AGENTS.md
├── PROJECT.md     -> ../chairforce-com-au-ai-tools/PROJECT.md
├── context        -> ../chairforce-com-au-ai-tools/context
├── .cursor        -> ../chairforce-com-au-ai-tools/.cursor
└── .cursorignore  -> ../chairforce-com-au-ai-tools/.cursorignore

chairforce-com-au-ai-tools/
├── .git/
├── AGENTS.md
├── PROJECT.md
├── context/
├── .cursor/
├── .cursorignore
├── hooks/client-pre-push
└── setup.sh
```

---

# 57. Chairforce Reference: Git Identity

The Next&Co client repository uses the appropriate client/company Git identity.

Example:

```bash
git config user.name "RaoNC"
git config user.email "rao@nextandco.com.au"
```

Verify per repo:

```bash
git config user.name
git config user.email
```

Do not assume global Git identity is correct when working across multiple clients/organizations.

---

# 58. Chairforce Reference: GitHub SSH Alias

Example:

```ssh
Host github-nc
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_raonc
  IdentitiesOnly yes
```

Client remote:

```text
git@github-nc:Nextandco/chairforce-com-au.git
```

This helps ensure the correct GitHub identity/key is used.

---

# 59. Recommended Daily Sanity Check

When working heavily across code and AI tooling, occasionally run:

```bash
cd ~/Projects/wp/chairforce-com-au
git status

cd ../chairforce-com-au-ai-tools
git status
```

This immediately shows which repository owns outstanding changes.

A client `git push` is also a reminder: the local `pre-push` hook (§35A)
blocks if the AI-tools repo is dirty or unpushed.

---

# 60. Quick Migration Checklist

## Preparation

- [ ] stop DDEV/local services
- [ ] close Cursor/IDE
- [ ] run `git status`
- [ ] run `git remote -v`
- [ ] confirm old repo should remain as archive
- [ ] confirm history does not need preserving

## Extract AI layer

- [ ] create sibling AI repo directory
- [ ] move `AGENTS.md`
- [ ] move `PROJECT.md`
- [ ] move `context/`
- [ ] move `.cursor/`
- [ ] move `.cursorignore`
- [ ] do not commit/push these deletions to old remote

## Create client repo

- [ ] remove old `.git`
- [ ] clean `.gitignore`
- [ ] `git init -b main`
- [ ] add client GitHub remote
- [ ] add private paths to `.git/info/exclude`
- [ ] run `git add -n .`
- [ ] inspect tracked files
- [ ] stage
- [ ] inspect `git diff --cached --name-only`
- [ ] commit
- [ ] push

## Create AI repo

- [ ] `git init -b main`
- [ ] add private internal remote
- [ ] commit
- [ ] push

## Overlay

- [ ] create symlink for `AGENTS.md`
- [ ] create symlink for `PROJECT.md`
- [ ] create symlink for `context/`
- [ ] create symlink for `.cursor/`
- [ ] create symlink for `.cursorignore`
- [ ] verify `ls -la`
- [ ] verify client Git status clean
- [ ] verify AI Git status clean

## Local environment

- [ ] reopen Cursor on client root
- [ ] verify agent instructions load
- [ ] verify context paths work
- [ ] review DDEV project name after rename
- [ ] start local environment
- [ ] smoke-test site

## Finalization

- [ ] run `setup.sh` (overlay + local pre-push hook)
- [ ] create optional pre-commit guard
- [ ] confirm a dirty AI-tools tree blocks `git push` in the client repo
- [ ] test fresh clone process
- [ ] archive old repository only after validation

---

# 61. Quick Clone Checklist for Future Developers

Developer with private AI access:

```bash
git clone <client-repo>
git clone <private-ai-repo>

cd <private-ai-repo>
./setup.sh ../<client-repo>

cd ../<client-repo>
# open IDE here
```

Developer without private AI access:

```bash
git clone <client-repo>
```

Nothing else is required to build or run the client project.

---

# 62. Decision Summary

For projects like Chairforce, use:

```text
Client code:
separate client-owned Git repository

AI tooling:
separate private agency repository

Local integration:
symlinks from client root to private repo

Git relationship:
none

Deployment relationship:
client repo only
(see context/kb/sop-github-to-wpengine-gitpush.md)

Private tooling dependency at runtime:
none
```

This provides the cleanest balance of:

- client ownership;
- internal IP separation;
- agent usability;
- deployment safety;
- maintainability;
- reproducibility;
- future starter-framework extraction.

---

# 63. Final Pattern

```text
                  LOCAL DEVELOPMENT MACHINE

        ┌────────────────────────────────────┐
        │ Client working tree                │
        │ chairforce-com-au/                 │
        │                                    │
        │ Runtime/client code                │
        │                                    │
        │ AGENTS.md ───────────────┐         │
        │ PROJECT.md ──────────────┤         │
        │ context/ ────────────────┤ links   │
        │ .cursor/ ────────────────┤         │
        │ .cursorignore ───────────┘         │
        └───────────────┬────────────────────┘
                        │
                        │ Git
                        v
              Next&Co client GitHub


        ┌────────────────────────────────────┐
        │ Private AI working tree            │
        │ chairforce-com-au-ai-tools/        │
        │                                    │
        │ AGENTS.md                          │
        │ PROJECT.md                         │
        │ context/                           │
        │ .cursor/                           │
        │ .cursorignore                      │
        └───────────────┬────────────────────┘
                        │
                        │ Git
                        v
              Ataki private GitHub
```

The two repositories never need to know about each other.

Only the developer's local filesystem creates the overlay.
