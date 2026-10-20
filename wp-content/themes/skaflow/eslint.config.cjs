/**
 * Flat ESLint config for @wordpress/scripts 32+.
 *
 * recommended-with-formatting stays on so TypeScript-eslint is not loaded.
 * typescript is a transitive install and that plugin used to crash lint:js;
 * this theme has no TypeScript sources.
 */
const globals = require( 'globals' );
const wpPlugin = require( '@wordpress/eslint-plugin' );

module.exports = [
	{
		ignores: [
			'**/build/**',
			'**/build-jsx-blocks/**',
			'**/assets/**',
			'**/vendor/**',
			'**/templates/**',
			'**/parts/**',
			'**/patterns/**',
			'**/node_modules/**',
		],
	},

	...wpPlugin.configs[ 'recommended-with-formatting' ],
	...wpPlugin.configs.jsdoc,

	{
		settings: {
			jsdoc: {
				preferredTypes: {
					object: 'Object',
				},
				tagNamePreference: {
					returns: 'return',
					yields: 'yield',
				},
			},
		},
		languageOptions: {
			globals: {
				...globals.browser,
				...globals.jquery,
				CSS: 'readonly',
			},
			parserOptions: {
				requireConfigFile: false,
				babelOptions: {
					presets: [
						require.resolve( '@wordpress/babel-preset-default' ),
					],
				},
			},
		},
		rules: {
			'import/no-unresolved': [
				'error',
				{ ignore: [ '^jquery$', '^@wordpress/' ] },
			],
		},
	},

	{
		files: [ 'tools/**/*.{js,mjs}', 'config-dev/**/*.js' ],
		languageOptions: {
			globals: {
				...globals.node,
			},
		},
	},
];
