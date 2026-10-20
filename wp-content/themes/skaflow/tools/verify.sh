#!/usr/bin/env bash
# The gate. One definition, called by hooks/pre-push and runnable by hand.
#
#   wp-content/themes/skaflow/tools/verify.sh
#
# Nothing is installed or built on GitHub or the host, so whatever sits in
# build/ is literally what renders. That makes a stale build a broken deploy,
# not a tidiness issue, which is why it is checked alongside the linters.

set -euo pipefail

cd "$(dirname "${BASH_SOURCE[0]}")/.."

fail() {
	echo
	echo "VERIFY FAILED: $*"
	exit 1
}

# Warn-only: a hand run is legitimate before the hook is wired up, but a clone
# that never wires it up gets no gate at all and no sign that it is missing.
tools/check-hooks.sh

# The supported window is whatever the build toolchain supports, so it lives in
# package.json's engines field and is checked against that rather than against a
# single pinned version. Odd majors are excluded because glob, lru-cache and the
# jest packages all declare `20 || >=22`; the floor is 20.19.0 because
# @wordpress/ui requires it. Re-derive rather than guess after a dependency bump.
#
# Build output is NOT the reason for this window. Verified byte-identical on
# Node 20.20.2, 22.22.0 and 24.13.1, so the freshness check below is safe
# anywhere in range.
node -e '
	const range = require( "./package.json" ).engines.node;
	let semver;
	try { semver = require( "semver" ); } catch ( e ) { process.exit( 2 ); }
	process.exit( semver.satisfies( process.version, range ) ? 0 : 1 );
' && node_ok=0 || node_ok=$?

if [[ "$node_ok" == "1" ]]; then
	fail "Node $(node -v) is outside the supported window.
  Required: $(node -p 'require("./package.json").engines.node')

    nvm use          # reads .nvmrc

  This window comes from the build toolchain, not from preference. Widening it
  means checking what the dependency tree actually declares."
elif [[ "$node_ok" == "2" ]]; then
	echo "    note: semver unavailable, Node version not checked"
fi

# Build first. Output is byte-reproducible, so if rebuilding changes a tracked
# file then the committed build did not match the committed source.
echo "==> build"
npm run build >/dev/null || fail "build errored."

if ! git diff --quiet HEAD -- build build-jsx-blocks; then
	echo
	git diff --stat HEAD -- build build-jsx-blocks
	fail "build/ was stale. It has just been rebuilt for you: commit it, then push again."
fi

# git diff cannot see untracked files, so the check above passes a brand-new
# artifact straight through. Adding a webpack entry to package.json produces one:
# build/<entry>.asset.php is generated, never added, and then missing from the
# deploy, which is the fatal that class-front.php throws on a missing
# index.asset.php. Whatever build/ holds has to be tracked, not merely current.
orphans="$(git ls-files --others --exclude-standard -- build build-jsx-blocks)"

if [[ -n "$orphans" ]]; then
	echo
	echo "$orphans"
	fail "the build output above is untracked, so it would not deploy. Run: git add build build-jsx-blocks"
fi

echo "==> lint:style"
npm run lint:style >/dev/null || fail "stylelint. Run: npm run lint:style"

echo "==> lint:js"
npm run lint:js >/dev/null || fail "eslint. Run: npm run lint:js"

echo "==> lint:pkg-json"
npm run lint:pkg-json >/dev/null || fail "package.json lint. Run: npm run lint:pkg-json"

# Host composer first, DDEV second. A machine with no PHP runtime is the normal
# case here, not a blocker.
echo "==> phpcs"
if command -v composer >/dev/null 2>&1; then
	composer phpcs >/dev/null || fail "PHPCS. Run: composer phpcs"
elif command -v ddev >/dev/null 2>&1; then
	( cd ../../.. && ddev composer -d wp-content/themes/skaflow phpcs >/dev/null ) \
		|| fail "PHPCS. Run: ddev composer -d wp-content/themes/skaflow phpcs"
else
	fail "neither composer nor ddev found. Cannot run PHPCS."
fi

echo
echo "Verify passed."
