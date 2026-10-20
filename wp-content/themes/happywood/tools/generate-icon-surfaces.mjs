#!/usr/bin/env node
/**
 * Generates every icon surface from the curated list in
 * src/js-admin/lucide-icon-options.js.
 *
 * Run with `npm run build:icons` after editing that list.
 *
 * The curated set spans five files. Keeping them in sync by hand is what
 * this script exists to prevent: an icon missing from one surface either
 * renders as a blank box, or - in the PHP choices array, which also backs
 * happywood_sanitize_lucide_icon_slug() - is silently discarded on save.
 *
 * Each target file keeps its hand-written header and static rules; only
 * the region between the `hw-icons:start` / `hw-icons:end` markers is
 * rewritten.
 *
 * Glyph values are emitted two different ways on purpose:
 *   - button-icon-font.css gets `content: "\eXXX"` escapes. It is a static
 *     file served as-is, so nothing minifies them.
 *   - _variables.scss gets literal characters, because cssnano empties
 *     `\eXXX` escapes when they pass through the build. See
 *     .cursor/rules/sf-icons.mdc.
 *
 * The webfonts in assets/fonts/lucide/ are also generated here, subset to
 * exactly the curated glyphs. Upstream ships ~2,000 glyphs in a 274KB
 * woff2; the curated set is a fraction of that. Subsetting in the same run
 * that writes the codepoints is what keeps the two from drifting - the
 * fonts used to be copied out of node_modules by hand, so a
 * `lucide-static` upgrade could shift codepoints while the stale font
 * stayed put, silently rendering every icon as the wrong glyph.
 */

import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, join } from 'node:path';
import subsetFont from 'subset-font';

const THEME = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const LUCIDE = join( THEME, 'node_modules/lucide-static' );

const OPTIONS_FILE = join( THEME, 'src/js-admin/lucide-icon-options.js' );
const SOURCE_FONT = join( LUCIDE, 'font/lucide.ttf' );
const TARGETS = {
	css: join( THEME, 'assets/css/button-icon-font.css' ),
	scss: join( THEME, 'src/sass/icon-font/_variables.scss' ),
	iconBlock: join( THEME, 'src/js-admin/happywood-icon-block-icons.js' ),
	php: join( THEME, 'includes/menu/happywood-lucide-icon-choices.php' ),
};
const FONT_FORMATS = [
	[ 'woff2', join( THEME, 'assets/fonts/lucide/lucide.woff2' ) ],
	[ 'woff', join( THEME, 'assets/fonts/lucide/lucide.woff' ) ],
];

const read = ( f ) => readFileSync( f, 'utf8' );

/* -- Input ------------------------------------------------------------- */

function parseOptions() {
	const src = read( OPTIONS_FILE );
	const body = src.slice( src.indexOf( 'HAPPYWOOD_LUCIDE_ICON_OPTIONS' ) );
	const icons = [ ...body.matchAll( /\{\s*slug:\s*'([^']+)',\s*label:\s*'([^']+)'([^}]*)\}/g ) ].map(
		( [ , slug, label, rest ] ) => ( { slug, label, custom: /custom:\s*true/.test( rest ) } ),
	);

	if ( ! icons.length ) {
		throw new Error( `No icons parsed from ${ OPTIONS_FILE }` );
	}

	const seen = new Set();
	for ( const { slug } of icons ) {
		if ( seen.has( slug ) ) {
			throw new Error( `Duplicate slug '${ slug }' in lucide-icon-options.js` );
		}
		seen.add( slug );
	}

	return icons;
}

function resolve( icons ) {
	const codepoints = JSON.parse( read( join( LUCIDE, 'font/codepoints.json' ) ) );
	const problems = [];

	for ( const icon of icons ) {
		if ( icon.custom ) {
			icon.svgFile = join( THEME, `assets/icons/${ icon.slug }.svg` );
			if ( ! existsSync( icon.svgFile ) ) {
				problems.push( `${ icon.slug }: custom icon has no assets/icons/${ icon.slug }.svg` );
			}
			continue;
		}

		icon.codepoint = codepoints[ icon.slug ];
		if ( icon.codepoint === undefined ) {
			problems.push( `${ icon.slug }: not a glyph in the Lucide font` );
		}

		icon.svgFile = join( LUCIDE, `icons/${ icon.slug }.svg` );
		if ( ! existsSync( icon.svgFile ) ) {
			problems.push( `${ icon.slug }: no SVG at node_modules/lucide-static/icons/${ icon.slug }.svg` );
		}
	}

	if ( problems.length ) {
		throw new Error( `Unusable icon slugs:\n  - ${ problems.join( '\n  - ' ) }` );
	}

	return icons;
}

/* -- SVG normalising ---------------------------------------------------- */

// Drop width/height/class from the root <svg> - the Icon Block plugin sizes
// the icon itself - and flatten to one line. Child shapes are untouched.
const ROOT_ATTR_ORDER = [
	'xmlns',
	'viewBox',
	'fill',
	'stroke',
	'stroke-width',
	'stroke-linecap',
	'stroke-linejoin',
];

function normaliseSvg( file ) {
	const raw = read( file ).replace( /<!--[\s\S]*?-->/g, '' ).trim();
	const open = raw.match( /<svg([\s\S]*?)>/ );
	const attrs = Object.fromEntries(
		[ ...open[ 1 ].matchAll( /([\w:-]+)="([^"]*)"/g ) ].map( ( [ , k, v ] ) => [ k, v ] ),
	);
	delete attrs.width;
	delete attrs.height;
	delete attrs.class;

	const ordered = [
		...ROOT_ATTR_ORDER.filter( ( k ) => k in attrs ),
		...Object.keys( attrs ).filter( ( k ) => ! ROOT_ATTR_ORDER.includes( k ) ),
	];
	const rootAttrs = ordered.map( ( k ) => `${ k }="${ attrs[ k ] }"` ).join( ' ' );

	const children = raw
		.slice( raw.indexOf( '>', open.index ) + 1, raw.lastIndexOf( '</svg>' ) )
		.replace( /\s*\n\s*/g, '' )
		.replace( /\s+/g, ' ' )
		.replace( /\s*\/>/g, ' />' )
		.trim();

	return `<svg ${ rootAttrs }>${ children }</svg>`;
}

/* -- Emitters ----------------------------------------------------------- */

const hex = ( cp ) => '\\' + cp.toString( 16 ).padStart( 4, '0' );

function cssBody( icons ) {
	const glyphs = icons
		.filter( ( i ) => ! i.custom )
		.map(
			( i ) =>
				`.wp-block-button.hw-icon-${ i.slug } .wp-block-button__link::before,\n` +
				`.editor-styles-wrapper .wp-block-button.hw-icon-${ i.slug } .wp-block-button__link::before,\n` +
				`.hw-icon-preview.hw-icon-${ i.slug }::before {\n  content: "${ hex( i.codepoint ) }";\n}`,
		)
		.join( '\n\n' );

	const custom = icons.filter( ( i ) => i.custom );
	if ( ! custom.length ) {
		return glyphs + '\n';
	}

	const masks = custom
		.map(
			( i ) =>
				`.wp-block-button.hw-icon-${ i.slug } .wp-block-button__link::before,\n` +
				`.editor-styles-wrapper .wp-block-button.hw-icon-${ i.slug } .wp-block-button__link::before,\n` +
				`.hw-icon-preview.hw-icon-${ i.slug }::before {\n` +
				`  content: '';\n  display: inline-block;\n  width: 1em;\n  height: 1em;\n` +
				`  background-color: currentColor;\n` +
				`  mask: url('../icons/${ i.slug }.svg') no-repeat center / contain;\n` +
				`  -webkit-mask: url('../icons/${ i.slug }.svg') no-repeat center / contain;\n}`,
		)
		.join( '\n\n' );

	return `${ glyphs }\n\n/* --- Custom Figma icons (SVG mask, not Lucide font) --- */\n\n${ masks }\n`;
}

function scssBody( icons ) {
	return (
		icons
			.filter( ( i ) => ! i.custom )
			.map( ( i ) => `  '${ i.slug }': "${ String.fromCodePoint( i.codepoint ) }",` )
			.join( '\n' ) + '\n'
	);
}

function iconBlockBody( icons ) {
	return (
		icons
			.map( ( i ) =>
				[
					'\t{',
					`\t\tname: '${ i.slug }',`,
					`\t\ttitle: __( '${ i.label.replace( /'/g, "\\'" ) }', 'happywood' ),`,
					`\t\ticon: '${ normaliseSvg( i.svgFile ) }',`,
					"\t\tcategories: [ 'happywood' ],",
					'\t\thasNoIconFill: true',
					'\t}',
				].join( '\n' ),
			)
			.join( ',\n' ) + '\n'
	);
}

function phpBody( icons ) {
	const pad = Math.max( ...icons.map( ( i ) => i.slug.length ) );
	return (
		icons
			.map(
				( i ) =>
					`\t\t'${ i.slug }'${ ' '.repeat( pad - i.slug.length ) } => ` +
					`__( '${ i.label.replace( /'/g, "\\'" ) }', 'happywood' ),`,
			)
			.join( '\n' ) + '\n'
	);
}

/* -- Write -------------------------------------------------------------- */

// Subset the upstream font down to the curated glyphs. `subset-font` takes
// the characters to keep as a string, so the Private Use Area characters
// are built from the same codepoints written into the CSS and Sass map.
async function writeFonts( icons ) {
	const wanted = icons.filter( ( i ) => ! i.custom ).map( ( i ) => String.fromCodePoint( i.codepoint ) );
	const source = readFileSync( SOURCE_FONT );
	const written = [];

	for ( const [ format, file ] of FONT_FORMATS ) {
		const subset = await subsetFont( source, wanted.join( '' ), { targetFormat: format } );
		const before = existsSync( file ) ? readFileSync( file ) : null;

		if ( before && before.equals( subset ) ) {
			written.push( [ file, false, subset.length ] );
			continue;
		}

		writeFileSync( file, subset );
		written.push( [ file, true, subset.length ] );
	}

	return written;
}

// A marker token must appear exactly once. Mentioning one in a file's own
// header comment silently turns that mention into the region boundary, and
// the file grows a duplicate of itself on every run - so refuse instead.
function locateMarker( src, token, file ) {
	const hits = [ ...src.matchAll( new RegExp( token.replace( ':', '\\:' ), 'g' ) ) ].map( ( m ) => m.index );

	if ( hits.length === 0 ) {
		throw new Error( `Missing '${ token }' marker in ${ file }` );
	}
	if ( hits.length > 1 ) {
		throw new Error(
			`'${ token }' appears ${ hits.length } times in ${ file } - it must appear exactly once. ` +
				`Prose referring to the markers must not spell the token out verbatim.`,
		);
	}

	return hits[ 0 ];
}

function writeRegion( file, body ) {
	const src = read( file );
	const start = locateMarker( src, 'hw-icons:start', file );
	const end = locateMarker( src, 'hw-icons:end', file );

	const afterStart = src.indexOf( '\n', start ) + 1;
	const beforeEnd = src.lastIndexOf( '\n', end ) + 1;
	const next = src.slice( 0, afterStart ) + body + src.slice( beforeEnd );

	if ( next === src ) {
		return false;
	}

	writeFileSync( file, next );
	return true;
}

const icons = resolve( parseOptions() );

const results = [
	[ TARGETS.css, cssBody( icons ) ],
	[ TARGETS.scss, scssBody( icons ) ],
	[ TARGETS.iconBlock, iconBlockBody( icons ) ],
	[ TARGETS.php, phpBody( icons ) ],
].map( ( [ file, body ] ) => [ file, writeRegion( file, body ) ] );

const fonts = await writeFonts( icons );

const rel = ( file ) => file.replace( THEME + '/', '' );
const kb = ( bytes ) => `${ Math.round( bytes / 102.4 ) / 10 } KB`;
const custom = icons.filter( ( i ) => i.custom ).length;

process.stdout.write(
	`${ icons.length } icons (${ icons.length - custom } font glyphs, ${ custom } custom SVG)\n`,
);
for ( const [ file, changed ] of results ) {
	process.stdout.write(
		`  ${ changed ? 'updated  ' : 'unchanged' }  ${ rel( file ) }\n`,
	);
}
for ( const [ file, changed, size ] of fonts ) {
	process.stdout.write(
		`  ${ changed ? 'updated  ' : 'unchanged' }  ${ rel( file ) } (${ kb( size ) } subset)\n`,
	);
}
