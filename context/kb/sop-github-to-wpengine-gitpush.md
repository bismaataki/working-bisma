# GitHub to WP Engine Deployment via GitPush
## Knowledge Base / SOP

**Purpose:** Standard operating procedure for deploying a GitHub repository to WP Engine using GitHub Actions as the automation/review layer and WP Engine GitPush as the deployment transport.

**Primary use case:** WordPress repositories where Git tracks only the files the project intentionally owns, such as a custom theme, selected custom plugins, root assets, or other client-specific files, while WordPress core, uploads, WP Engine system files, and unrelated plugins remain outside Git.

**How to apply:** This SOP is generic. Use it when a future project deploys
to WP Engine via GitHub Actions and GitPush. Examples in the body that name
a past client are illustrations of the procedure. Substitute remotes and
environment names from that project's `PROJECT.md`.

**This copy (skaflow):** no WP Engine remote yet. Local is DDEV
`https://skaflow.test`. When a project adds GitPush, record its environment
names in `PROJECT.md` and `context/kb/go-live.md`.

**Related:** Client vs private AI overlay is
`context/kb/sop-client-code-ai-tools-repository-separation.md`. Deploy the
client repository only.

---

# 1. Final Architecture

The recommended flow is:

```text
Developer
   |
   | git push / Pull Request
   v
GitHub
   |
   | merge/push to environment branch
   v
GitHub Actions
   |
   | SSH-authenticated git push
   v
WP Engine GitPush
   |
   | validates and deploys tracked Git changes
   v
WP Engine environment
```

For Chairforce during development:

```text
feature/* or fix/*
        |
        v
      PR
        |
        v
    develop
        |
        v
GitHub Actions
        |
        v
WP Engine GitPush
        |
        v
chairforceadev
```

The repository is the deployment boundary:

> If a file is intentionally tracked in Git, it is deployable.  
> If a file is not tracked in Git, the deployment pipeline does not own it.

This is important because the repository is intentionally **not** a full WordPress installation.

Example:

```text
Tracked by Git:
wp-content/themes/chairforce/
wp-content/plugins/chairforce-woodmart-data-normalise/
wp-content/plugins/chairforce-postcode-pricing/
README.md
package.json
other explicitly allowed project files

Not tracked by Git:
WordPress core
wp-content/uploads/
third-party plugins
WP Engine-managed files
developer-local AI tooling
local IDE files
```

---

# 2. Why GitPush Is Used Instead of the Standard WP Engine GitHub Action

WP Engine provides an official GitHub Action:

```yaml
uses: wpengine/github-action-wpe-site-deploy@v3
```

That action deploys through WP Engine SSH Gateway using `rsync`.

It works well for additions and modifications, but its default deployment is intentionally non-destructive. A file deleted from Git can remain on WP Engine unless `rsync --delete` is enabled.

For a partial WordPress repository, enabling root-level `--delete` is unsafe because WP Engine contains many files that are not represented in Git:

```text
WordPress core
uploads
third-party plugins
WP Engine MU plugins/system files
other environment-specific files
```

A broad `rsync --delete` could remove files that the repository does not own.

WP Engine GitPush has better semantics for this repository model:

```text
File added to Git       -> added/updated on WP Engine
File changed in Git     -> updated on WP Engine
Tracked file deleted    -> deleted on WP Engine
Untracked WPE file      -> left alone
```

This gives Git ownership semantics without making Git responsible for the entire WP Engine filesystem.

Therefore the selected architecture is:

```text
GitHub Actions = automation and deployment trigger
WP Engine GitPush = deployment transport
```

The official WP Engine rsync Action is not used for the final deployment path.

---

# 3. Branch and Environment Strategy

Recommended long-term mapping:

| GitHub Branch | WP Engine Environment | Purpose |
|---|---|---|
| `develop` | Development | Daily development, QA, UAT |
| `staging` | Staging | Pre-production validation |
| `main` | Production | Live site |

Chairforce initially uses only:

```text
develop -> chairforceadev
```

Do not create staging automation until a WP Engine Staging environment actually exists.

Do not configure `main -> production` until go-live.

Recommended promotion flow once all environments exist:

```text
feature/*
   |
   v
PR -> develop
       |
       v
Development
       |
       v
PR -> staging
       |
       v
Staging
       |
       v
PR -> main
       |
       v
Production
```

GitHub Pull Requests should be the review/promotion gate.

---

# 4. SSH Key Strategy

Do not reuse one SSH key for every service.

Use separate keys for separate trust relationships.

Recommended model:

```text
Local developer -> GitHub
~/.ssh/id_ed25519_raonc

Local developer -> WP Engine GitPush
~/.ssh/id_ed25519_raonc_wpe

GitHub Actions -> WP Engine GitPush
~/.ssh/id_ed25519_raonc_wpe_ci
```

Example responsibilities:

| Key | Used By | Destination |
|---|---|---|
| `id_ed25519_raonc` | Developer machine | GitHub |
| `id_ed25519_raonc_wpe` | Developer machine | WP Engine GitPush |
| `id_ed25519_raonc_wpe_ci` | GitHub Actions | WP Engine GitPush |

Benefits:

- smaller blast radius
- easier key rotation
- easier debugging
- clear ownership
- local access and CI access can be revoked independently
- CI keys can be scoped to only the required WP Engine environment

Do not use an organization-wide GitHub secret for a deployment key unless there is a deliberate security reason to do so.

Preferred:

```text
Repository Secret
```

rather than:

```text
Organization Secret
```

This ensures only the intended repository can read/use the deployment credential.

---

# 5. GitHub SSH Configuration for Local Development

Example `~/.ssh/config`:

```ssh
# -------------------------
# Next & Co - GitHub
# -------------------------
Host github-nc
  HostName github.com
  User git
  IdentityFile ~/.ssh/id_ed25519_raonc
  IdentitiesOnly yes


# -------------------------
# Next & Co - WP Engine GitPush
# -------------------------
Host wpe-nc-git
  HostName git.wpengine.com
  User git
  IdentityFile ~/.ssh/id_ed25519_raonc_wpe
  PreferredAuthentications publickey
  IdentitiesOnly yes
```

`IdentitiesOnly yes` is important when the developer machine has multiple SSH keys. It prevents SSH from offering unrelated keys first.

---

# 6. Generate the Local WP Engine GitPush Key

Create a dedicated local GitPush key:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519_raonc_wpe
```

The key may use a passphrase for local/manual use.

Display the public key:

```bash
cat ~/.ssh/id_ed25519_raonc_wpe.pub
```

Add it to:

```text
WP Engine
-> Site
-> Target Environment
-> GitPush
-> Add SSH public key / Add Developer
```

Use a recognizable developer label, for example:

```text
chairforceadev-raonc
```

---

# 7. Verify Local WP Engine GitPush Access

Test the key before adding a Git remote:

```bash
ssh wpe-nc-git info
```

Expected output is similar to:

```text
hello chairforceadev-raonc
 R W    chairforceadev

Please see https://git.wpengine.com for more help
```

`R W` indicates read/write access.

If this fails with:

```text
Permission denied (publickey)
```

check:

1. the public key was added to the correct WP Engine environment
2. the SSH config points to the correct private key
3. `IdentitiesOnly yes` is present
4. the key has finished propagating in WP Engine
5. the environment/install identifier is correct

---

# 8. Add WP Engine as a Local Git Remote

GitHub remains the primary remote:

```text
origin
```

WP Engine is added as a second remote:

```text
wpe-development
```

Example:

```bash
git remote add wpe-development \
  wpe-nc-git:production/chairforceadev.git
```

Verify:

```bash
git remote -v
```

Expected:

```text
origin          git@github-nc:Nextandco/chairforce-com-au.git (fetch)
origin          git@github-nc:Nextandco/chairforce-com-au.git (push)
wpe-development wpe-nc-git:production/chairforceadev.git (fetch)
wpe-development wpe-nc-git:production/chairforceadev.git (push)
```

Important:

The `production/` segment in the WP Engine GitPush repository path is part of WP Engine's GitPush URL format. It does **not** mean the target environment is the live Production environment.

The actual target in this example is:

```text
chairforceadev
```

---

# 9. Create the Development Branch

If not already present:

```bash
git switch -c develop
git push -u origin develop
```

Expected mapping:

```text
main       = future Production branch
develop    = Development branch
```

During active development, consider setting `develop` as the GitHub default branch so new Pull Requests naturally target Development instead of Production.

---

# 10. Perform a Manual GitPush Smoke Test First

Before automating deployment through GitHub Actions, prove WP Engine GitPush works manually.

Push:

```bash
git push -v wpe-development develop
```

A successful WP Engine response includes messages similar to:

```text
git.wpengine.com: validating
- success: validation complete!

git.wpengine.com: deploying
- info: deploying to production environment ... done.
- info: purging varnish cache ... done.

git.wpengine.com: complete!
```

Then verify the Development site manually.

Check:

- homepage/frontend
- `/wp-admin`
- custom theme
- custom plugins
- WooCommerce pages
- CSS/JS assets
- forms/integrations where relevant

This validates:

```text
Local Git
   ->
WP Engine GitPush
   ->
WP Engine Development
```

This smoke test is recommended, but it is not technically required by GitHub Actions.

---

# 11. Generate the GitHub Actions CI Key

Generate a separate deployment key:

```bash
ssh-keygen \
  -t ed25519 \
  -f ~/.ssh/id_ed25519_raonc_wpe_ci \
  -C "github-actions-chairforce" \
  -N ""
```

Important:

The CI private key must have **no passphrase** because GitHub Actions cannot interactively unlock it.

Display its public key:

```bash
cat ~/.ssh/id_ed25519_raonc_wpe_ci.pub
```

---

# 12. Add the CI Public Key to WP Engine GitPush

Add:

```text
~/.ssh/id_ed25519_raonc_wpe_ci.pub
```

to the target WP Engine environment's:

```text
GitPush -> Developer list
```

Use a recognizable name:

```text
github-actions-chairforce
```

For Chairforce Development, the GitPush developer list should contain two distinct identities:

```text
chairforceadev-raonc
github-actions-chairforce
```

Meaning:

```text
chairforceadev-raonc
    local/manual developer access

github-actions-chairforce
    automated GitHub Actions deployment
```

---

# 13. Add the CI Private Key to GitHub

In GitHub:

```text
Repository
-> Settings
-> Secrets and variables
-> Actions
-> Repository secrets
```

Create:

```text
WPE_GITPUSH_KEY_PRIVATE
```

Value:

```bash
cat ~/.ssh/id_ed25519_raonc_wpe_ci
```

Copy the entire private key including:

```text
-----BEGIN OPENSSH PRIVATE KEY-----
...
-----END OPENSSH PRIVATE KEY-----
```

Never commit the private key to Git.

Do not store it in `.env`, `.github/`, `context/`, the theme, or any tracked project file.

---

# 14. Repository `.gitignore` Requirements

If the repository uses a restrictive root rule such as:

```gitignore
/*
```

GitHub workflow files must be explicitly allowed:

```gitignore
!.github/
```

Example:

```gitignore
/*

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
!wp-content/
```

The repository itself should decide what can be deployed.

Do not add deploy-path logic to the workflow unless there is a specific reason.

---

# 15. GitHub Actions Workflow

Create:

```text
.github/workflows/develop.yml
```

Recommended workflow:

```yaml
name: Deploy Development to WP Engine

on:
  push:
    branches:
      - develop

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Checkout repository
        uses: actions/checkout@v4
        with:
          fetch-depth: 0

      - name: Configure WP Engine GitPush SSH
        shell: bash
        run: |
          mkdir -p ~/.ssh

          printf '%s\n' "${{ secrets.WPE_GITPUSH_KEY_PRIVATE }}" > ~/.ssh/wpe_gitpush
          chmod 600 ~/.ssh/wpe_gitpush

          ssh-keyscan git.wpengine.com >> ~/.ssh/known_hosts
          chmod 600 ~/.ssh/known_hosts

      - name: Deploy to WP Engine Development
        shell: bash
        env:
          GIT_SSH_COMMAND: ssh -i ~/.ssh/wpe_gitpush -o IdentitiesOnly=yes
        run: |
          git remote add wpengine git@git.wpengine.com:production/chairforceadev.git
          git push wpengine HEAD:develop
```

---

# 16. Why `fetch-depth: 0` Is Used

The default GitHub checkout is shallow.

WP Engine GitPush is a Git-based deployment system, so the workflow should use a complete Git history:

```yaml
with:
  fetch-depth: 0
```

This avoids issues caused by trying to push an incomplete/shallow history into the WP Engine GitPush repository.

---

# 17. Why `HEAD:develop` Is Used

This command:

```bash
git push wpengine HEAD:develop
```

means:

> Push the exact commit currently being executed by GitHub Actions to the `develop` branch in WP Engine GitPush.

This is preferable to relying on local branch assumptions inside the runner.

It also ensures the deployed commit is exactly the commit that triggered the workflow.

---

# 18. Deploy the Workflow

Commit:

```bash
git add .github/workflows/develop.yml .gitignore
git commit -m "Add WP Engine development deployment"
git push origin develop
```

Then check:

```text
GitHub
-> Repository
-> Actions
-> Deploy Development to WP Engine
```

The deployment job should run automatically.

A successful deployment should contain WP Engine GitPush output such as:

```text
validating
deploying
purging varnish cache
complete
```

---

# 19. Acceptance Test: Add a File

Create a harmless tracked file:

```bash
echo "GitPush deployment test" \
  > wp-content/themes/chairforce/gitpush-delete-test.txt
```

Commit and push:

```bash
git add wp-content/themes/chairforce/gitpush-delete-test.txt
git commit -m "Test GitPush file addition"
git push origin develop
```

Wait for GitHub Actions to pass.

Verify on WP Engine, for example through the browser:

```text
https://<development-domain>/wp-content/themes/chairforce/gitpush-delete-test.txt
```

Expected:

```text
HTTP 200
file contents visible
```

This proves:

```text
GitHub -> GitHub Actions -> WP Engine GitPush -> WPE
```

is genuinely deploying new files.

---

# 20. Acceptance Test: Delete a Tracked File

Delete the same test file:

```bash
git rm wp-content/themes/chairforce/gitpush-delete-test.txt
git commit -m "Test GitPush file deletion"
git push origin develop
```

Wait for GitHub Actions.

Then revisit the URL.

Expected:

```text
HTTP 404
```

This is the critical test distinguishing GitPush from the default rsync-based WP Engine GitHub Action.

Once both tests pass:

```text
Add      PASS
Modify   PASS
Delete   PASS
```

the deployment pipeline can be considered validated.

---

# 21. Important Note About Files Previously Deployed by rsync

GitPush only knows about files represented in its Git history.

If a file was previously placed on WP Engine through:

- SFTP
- SSH
- rsync
- WP Engine official GitHub Action
- manual upload

and that file was never tracked/deployed through the GitPush repository, GitPush cannot know that it should remove it.

Example:

```text
deploy-test.txt
```

was created through the earlier rsync experiment and then deleted in Git before GitPush ever tracked it.

Result:

```text
GitPush did not remove it
```

because GitPush never owned/tracked that file.

Such stale files must be removed manually once.

Future tracked GitPush files will obey normal Git deletion semantics.

---

# 22. Cleanup After Migrating From the Official WP Engine GitHub Action

If the official WP Engine SSH Gateway Action was tested before switching to GitPush, clean up after GitPush is fully validated.

## WP Engine

Remove the CI public key from the WP Engine **GitHub Actions / SSH Gateway** configuration.

Keep the same CI key registered under:

```text
GitPush -> Developer list
```

if that is the credential used by the final workflow.

## GitHub

Delete the old repository secret:

```text
WPE_SSHG_KEY_PRIVATE
```

Keep:

```text
WPE_GITPUSH_KEY_PRIVATE
```

Search workflow files for old references:

```bash
grep -R \
  "WPE_SSHG_KEY_PRIVATE\|github-action-wpe-site-deploy" \
  .github
```

Expected:

```text
no output
```

---

# 23. Keep the Local WP Engine Remote

Do not remove:

```text
wpe-development
```

even after GitHub Actions is working.

Normal deployment becomes:

```bash
git push origin develop
```

GitHub Actions then deploys automatically.

The local WPE remote remains useful as:

- emergency deployment route
- debugging route
- GitPush validation tool
- CI failure fallback
- environment inspection aid

Normal:

```text
origin -> GitHub -> GitHub Actions -> WPE
```

Fallback:

```text
local -> wpe-development -> WPE
```

Avoid using the manual fallback routinely because GitHub should remain the authoritative deployment trigger.

---

# 24. GitHub Branch Protection / Rulesets

Once automated deployment is active, protect the environment branch.

Recommended minimum rules for `develop`:

- require Pull Requests before merge
- block force pushes
- block branch deletion

Optional depending on team size:

- require at least one approval
- require conversation resolution
- require CI checks to pass
- restrict direct pushes
- require signed commits

For a small team or single primary developer, mandatory external approval may create unnecessary friction.

The core rule should still be:

```text
feature branch
    ->
Pull Request
    ->
develop
    ->
automatic Development deployment
```

Consider making `develop` the default GitHub branch while the site is in active development/UAT.

---

# 25. Production Setup at Go-Live

Do not reuse the Development workflow blindly.

At go-live:

1. confirm the WP Engine Production environment name
2. add the CI public key to Production GitPush if appropriate
3. create a Production workflow
4. map `main` to Production
5. protect `main`
6. test carefully before relying on automatic deployment

Example:

```text
.github/workflows/production.yml
```

Conceptually:

```yaml
name: Deploy Production to WP Engine

on:
  push:
    branches:
      - main
```

and:

```bash
git remote add wpengine \
  git@git.wpengine.com:production/<production-environment>.git

git push wpengine HEAD:main
```

Recommended production promotion:

```text
develop
    |
    | PR
    v
main
    |
    v
GitHub Actions
    |
    v
WP Engine Production
```

Production deployment should happen from a reviewed merge, not from an arbitrary developer command.

---

# 26. Staging Setup Later

Only create staging when a real WPE Staging environment exists.

Recommended mapping:

```text
staging -> WPE Staging
```

Create:

```text
.github/workflows/staging.yml
```

The same GitPush pattern applies.

Long-term:

```text
develop  -> Development
staging  -> Staging
main     -> Production
```

Each environment can use:

- a dedicated GitPush key, or
- the same CI key if deliberately permitted

Per-environment credentials provide stronger isolation.

---

# 27. Security Model

Preferred deployment security model:

```text
Repository-level GitHub secret
+
Dedicated CI SSH key
+
WP Engine GitPush access only to required environment(s)
```

Avoid broad organization-wide deployment secrets unless intentionally required.

Reason:

An organization secret combined with a broadly authorized WP Engine key can increase the blast radius if either the repository or secret is compromised.

Per-project/per-environment credentials make it easier to:

- revoke access
- rotate keys
- investigate incidents
- separate client infrastructure
- prevent accidental cross-site deployment

Never print the private key into GitHub Action logs.

Never commit private keys.

---

# 28. Key Rotation SOP

When rotating the GitHub Actions GitPush key:

1. Generate new key:

```bash
ssh-keygen \
  -t ed25519 \
  -f ~/.ssh/<new-key-name> \
  -C "github-actions-<project>" \
  -N ""
```

2. Add new public key to WP Engine GitPush.

3. Replace the GitHub repository secret:

```text
WPE_GITPUSH_KEY_PRIVATE
```

4. Run a harmless deployment.

5. Confirm successful GitPush.

6. Remove the old public key from WP Engine.

7. Delete/archive the old private key securely.

Always add and test the new credential **before** removing the old one.

---

# 29. Troubleshooting

## `Permission denied (publickey)`

Check:

- correct GitHub repository secret
- complete private key was pasted
- CI private key has no passphrase
- public key exists in the target environment's GitPush developer list
- workflow uses the correct environment/install name
- `GIT_SSH_COMMAND` specifies the intended key
- `IdentitiesOnly=yes` is present

---

## GitHub Action succeeds but site does not change

Check:

- correct WP Engine environment
- correct source branch triggered workflow
- deployed commit actually contains the intended change
- caches
- build output is committed if the project expects compiled assets in Git
- WPE deployment log shows `complete`

---

## File deleted from Git but remains on WPE

Determine whether the file was ever tracked by the WP Engine GitPush repository.

If it was originally deployed through rsync/SFTP/manual upload and GitPush never tracked it, remove it manually once.

If GitPush had previously deployed the file, a subsequent Git deletion should remove it.

---

## Non-fast-forward rejection

Do not immediately force push.

First inspect the relationship between GitHub and WP Engine GitPush history.

The first manual GitPush should establish the WPE repository from the GitHub history.

If WPE contains unrelated Git history, decide deliberately whether it should be reset.

Use force only when the deployment repository is intentionally being replaced.

---

## Wrong SSH key is being offered locally

Use SSH aliases and:

```ssh
IdentitiesOnly yes
```

Example:

```bash
ssh -v wpe-nc-git info
```

Inspect which identity file is offered.

---

## GitHub workflow file is ignored

If root `.gitignore` includes:

```gitignore
/*
```

add:

```gitignore
!.github/
```

Then verify:

```bash
git status
```

---

# 30. Repository Migration Considerations

When moving an existing project from a private/internal repository to a client GitHub repository and Git history does not need to be preserved:

1. stop local development services if the folder is being renamed
2. close the active IDE/editor workspace
3. move private AI/developer tooling out
4. rename the project directory
5. do not push deletions to the old repository
6. remove the old `.git`
7. clean `.gitignore`
8. initialize a fresh client repository
9. verify exactly what will be tracked
10. commit and push to the new GitHub repository

Useful dry-run:

```bash
git add -n .
```

Useful staged-file check:

```bash
git diff --cached --name-only
```

Useful ignore check:

```bash
git check-ignore -v <path>
```

---

# 31. Private AI/Developer Tooling Must Stay Outside Client Git

For Chairforce, the client repository intentionally does not own:

```text
AGENTS.md
PROJECT.md
context/
.cursor/
.cursorignore
```

These live in a separate private repository, for example:

```text
atakidevelopers/chairforce-com-au-ai-tools
```

Locally they may be exposed at the project root through symlinks.

Example:

```text
chairforce-com-au/
├── AGENTS.md      -> ../chairforce-com-au-ai-tools/AGENTS.md
├── PROJECT.md     -> ../chairforce-com-au-ai-tools/PROJECT.md
├── context        -> ../chairforce-com-au-ai-tools/context
├── .cursor        -> ../chairforce-com-au-ai-tools/.cursor
└── .cursorignore  -> ../chairforce-com-au-ai-tools/.cursorignore
```

The client repo should ignore these paths.

This keeps:

```text
Client runtime/deployment code
```

separate from:

```text
Private agency AI instructions/research/tooling
```

Canonical procedure:
`context/kb/sop-client-code-ai-tools-repository-separation.md`.

---

# 32. Chairforce Reference Configuration

## GitHub

```text
Repository:
Nextandco/chairforce-com-au

Primary development branch:
develop

Future production branch:
main
```

## WP Engine Development

```text
Environment:
chairforceadev

Site:
https://chairforceadev.wpenginepowered.com/
```

## Local Git remotes

```text
origin
git@github-nc:Nextandco/chairforce-com-au.git

wpe-development
wpe-nc-git:production/chairforceadev.git
```

## GitHub Repository Secret

```text
WPE_GITPUSH_KEY_PRIVATE
```

## Local keys

```text
GitHub:
~/.ssh/id_ed25519_raonc

Local WPE GitPush:
~/.ssh/id_ed25519_raonc_wpe

GitHub Actions WPE GitPush:
~/.ssh/id_ed25519_raonc_wpe_ci
```

## WPE GitPush Developers

```text
chairforceadev-raonc
github-actions-chairforce
```

---

# 33. Daily Developer Workflow

Normal feature development:

```bash
git switch develop
git pull origin develop

git switch -c feature/my-feature

# work
git add .
git commit -m "Add my feature"
git push -u origin feature/my-feature
```

Create Pull Request:

```text
feature/my-feature -> develop
```

Once merged:

```text
develop updated
      ->
GitHub Actions
      ->
WP Engine GitPush
      ->
chairforceadev deployed
```

No manual WPE push should normally be required.

---

# 34. Deployment Principles

The deployment system should follow these rules:

1. **GitHub is the source of truth.**
2. **Pull Requests are the review/promotion gate.**
3. **WP Engine GitPush performs deployment.**
4. **Git tracks only project-owned files.**
5. **Tracked deletions must propagate to WP Engine.**
6. **Untracked WP Engine files must remain untouched.**
7. **Development, Staging, and Production are branch-mapped separately.**
8. **CI deployment keys should be project/environment scoped where practical.**
9. **Repository secrets are preferred over broad organization secrets.**
10. **The manual WPE Git remote remains available as a fallback, not the normal workflow.**
11. **Never use root-level rsync `--delete` against a partial WordPress repository.**
12. **Do not mix private AI/development tooling with the client deployment repository.**

---

# 35. Quick Setup Checklist for a New Project

Use this condensed checklist when repeating the pattern on another Next&Co/WP Engine project.

## Repository

- [ ] Client GitHub repository created
- [ ] Deployment files intentionally tracked
- [ ] `.github/` allowed by `.gitignore`
- [ ] private/local tooling excluded
- [ ] `develop` branch created
- [ ] GitHub is `origin`

## Local WP Engine

- [ ] dedicated local WP Engine GitPush key generated
- [ ] public key added to WPE environment GitPush
- [ ] SSH alias configured
- [ ] `ssh <alias> info` succeeds
- [ ] WPE Git remote added
- [ ] manual GitPush succeeds
- [ ] site smoke-tested

## GitHub Actions

- [ ] dedicated no-passphrase CI key generated
- [ ] CI public key added to WPE GitPush
- [ ] private key added as repository secret
- [ ] `WPE_GITPUSH_KEY_PRIVATE` exists
- [ ] environment workflow created
- [ ] `fetch-depth: 0`
- [ ] `git push wpengine HEAD:<branch>` used
- [ ] workflow committed and pushed

## Acceptance

- [ ] Action succeeds
- [ ] new file deploys
- [ ] modified file deploys
- [ ] tracked deleted file disappears
- [ ] site works after deployment

## Governance

- [ ] environment branch protected
- [ ] force pushes blocked
- [ ] branch deletion blocked
- [ ] PR workflow established
- [ ] Production automation deferred until go-live if not yet required

---

# 36. Final Recommended Pattern

For partial WordPress repositories on WP Engine:

```text
GitHub repository
      |
      | PR-controlled branch update
      v
GitHub Actions
      |
      | git push over dedicated CI SSH key
      v
WP Engine GitPush
      |
      | Git-aware deployment
      v
WP Engine environment
```

This pattern combines:

- GitHub review controls
- GitHub branch management
- GitHub Actions automation
- WP Engine-native Git deployment semantics
- safe tracked-file deletion
- protection of untracked WordPress/WP Engine files
- isolated deployment credentials
- a clean path from Development to Staging to Production
