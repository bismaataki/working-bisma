/**
 * Same base as @wordpress/scripts, plus ignore generated CSS.
 * Scope the npm script to *.scss so JS/PHP in src-jsx-blocks are not linted as CSS.
 *
 * Do not --fix the whole tree:
 * - Sass maps with trailing `//` comments (eats keys: wide: 1480px → 80px)
 * - icon-font/_variables.scss (literal PUA glyphs; --fix shreds the map)
 *
 * Overrides below are only for a different product or a tool mismatch.
 * A rule that describes a standard we want stays on; the Sass is fixed.
 */
module.exports = {
	extends: '@wordpress/stylelint-config/scss-stylistic',
	ignoreFiles: [
		'build/**',
		'build-jsx-blocks/**',
		'assets/css/**',
		'vendor/**',
		'node_modules/**',
		'templates/**',
		'parts/**',
		'patterns/**',
		// Literal Lucide PUA glyphs. --fix rewrites quotes/newlines and shreds the map.
		'**/src/sass/icon-font/_variables.scss',
	],
	rules: {
		// WordPress ships kebab-only (`foo-bar`). This theme authors BEM
		// (`sk-foo__bar--mod`) and must also target Woo, Gravity Forms,
		// Select2, and YITH classes we do not name.
		'selector-class-pattern': [
			'^[a-zA-Z][a-zA-Z0-9_-]*$',
			{
				message:
					'Selector should start with a letter and use letters, digits, hyphens, or underscores (selector-class-pattern)',
			},
		],
		// WooCommerce and YITH emit underscored IDs (`review_form_wrapper`,
		// `update_raq_wpnonce`). We style those; we do not rename them.
		'selector-id-pattern': [
			'^[a-zA-Z][a-zA-Z0-9_-]*$',
			{
				message:
					'ID should start with a letter and use letters, digits, hyphens, or underscores (selector-id-pattern)',
			},
		],
		// Gutenberg allows only `px` line-heights. This theme uses unitless
		// line-height per the CSS spec.
		'declaration-property-unit-allowed-list': {
			'line-height': [],
		},
		// Stylelint 17 treats `&` as native CSS nesting. These `&` live inside
		// `@include` mixin content, which is valid Sass and has no CSS parent
		// in the file.
		'nesting-selector-no-missing-scoping-root': null,
	},
};
