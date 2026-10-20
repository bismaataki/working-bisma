#!/usr/bin/env bash
# Overlay private AI tooling into a client working tree and install
# the local client pre-push reminder.
#
# Only needed when a fork splits the AI tooling into its own repository. The
# starter is a single repo and does not run this. The `sf-rename-theme` skill
# asks whether a fork wants the split and calls this if so. Full procedure:
# context/kb/sop-client-code-ai-tools-repository-separation.md
#
# Usage: ./setup.sh /path/to/client-repo

set -euo pipefail

AI_REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ $# -lt 1 ]]; then
	echo "Usage: $0 /path/to/client-repo"
	echo
	echo "No default is assumed. Pass the client working tree explicitly."
	exit 1
fi

if [[ ! -d "$1" ]]; then
	echo "Client path does not exist: $1"
	exit 1
fi

CLIENT_REPO="$(cd "$1" && pwd)"

if [[ "$CLIENT_REPO" == "$AI_REPO" ]]; then
	echo "Client repo and AI-tools repo are the same directory: $AI_REPO"
	echo "Split the repositories first. See"
	echo "context/kb/sop-client-code-ai-tools-repository-separation.md"
	exit 1
fi

echo "AI repo:     $AI_REPO"
echo "Client repo: $CLIENT_REPO"

if [[ ! -d "$CLIENT_REPO/.git" ]]; then
	echo "Client path is not a Git repository: $CLIENT_REPO"
	exit 1
fi

links=(
	"AGENTS.md"
	"PROJECT.md"
	"context"
	".cursor"
	".cursorignore"
)

for item in "${links[@]}"; do
	target="$AI_REPO/$item"
	link="$CLIENT_REPO/$item"

	if [[ ! -e "$target" ]]; then
		echo "Missing source: $target"
		exit 1
	fi

	if [[ -L "$link" ]]; then
		rm "$link"
	elif [[ -e "$link" ]]; then
		echo "Refusing to overwrite existing non-symlink: $link"
		exit 1
	fi

	ln -s "$target" "$link"
	echo "Linked: $link -> $target"
done

exclude_file="$CLIENT_REPO/.git/info/exclude"
mkdir -p "$(dirname "$exclude_file")"
touch "$exclude_file"

ensure_exclude() {
	local line="$1"
	grep -qxF "$line" "$exclude_file" || echo "$line" >>"$exclude_file"
}

ensure_exclude "AGENTS.md"
ensure_exclude "PROJECT.md"
ensure_exclude "context/"
ensure_exclude ".cursor/"
ensure_exclude ".cursorignore"

hook_src="$AI_REPO/hooks/client-pre-push"
hook_dst="$CLIENT_REPO/.git/hooks/pre-push"

if [[ ! -f "$hook_src" ]]; then
	echo "Missing hook: $hook_src"
	exit 1
fi

if [[ -L "$hook_dst" ]]; then
	rm "$hook_dst"
elif [[ -f "$hook_dst" ]] && grep -q 'ai-tools: client-pre-push' "$hook_dst"; then
	rm "$hook_dst"
elif [[ -e "$hook_dst" ]]; then
	echo "Refusing to overwrite existing pre-push hook: $hook_dst"
	exit 1
fi

ln -s "$hook_src" "$hook_dst"
chmod +x "$hook_src"
echo "Hooked: $hook_dst -> $hook_src"

echo "Setup complete."
