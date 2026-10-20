#!/usr/bin/env bash
# Warn when this clone has no pre-push hook, so tools/verify.sh will not run.
#
#   wp-content/themes/skaflow/tools/check-hooks.sh
#
# Git never clones .git/hooks/, and there is no CI here by design, so a fresh
# clone enforces nothing until someone runs the core.hooksPath step. That
# omission is otherwise silent: the gate does not fail, it simply never runs.
#
# Warn-only, always exit 0. This runs from npm postinstall, where a non-zero
# exit would break `npm install` over a missing reminder.

set -uo pipefail

repo_root="$(git rev-parse --show-toplevel 2>/dev/null || true)"

if [[ -z "$repo_root" ]]; then
	exit 0
fi

cd "$repo_root"

# --git-path honours core.hooksPath, so this resolves whichever directory Git
# would actually read, whether that is hooks/ or the default .git/hooks/.
if [[ -x "$(git rev-parse --git-path hooks)/pre-push" ]]; then
	exit 0
fi

cat <<'EOF'

  WARNING: this clone has no pre-push hook, so the gate will not run on push.
  Nothing checks your build, lint or PHPCS until you activate it:

      git config core.hooksPath hooks     # from the repo root, once per clone

EOF
