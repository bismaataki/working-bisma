/**
 * Theme palette slugs used for list (and future) icon color classes:
 * `.sk-icon-has-color-{slug}` → `--wp--preset--color--{slug}` in Sass.
 *
 * Keep in sync with theme.json `settings.color.palette` (exclude non-paint
 * tokens such as transparent / inherit / currentColor).
 */
export const SKAFLOW_ICON_COLOR_SLUGS = [
	{ slug: 'primary', label: 'Primary' },
	{ slug: 'secondary', label: 'Secondary' },
	{ slug: 'heading', label: 'Heading' },
	{ slug: 'body', label: 'Body' },
	{ slug: 'tertiary', label: 'Tertiary' },
	{ slug: 'quaternary', label: 'Quaternary' },
	{ slug: 'surface', label: 'Surface' },
	{ slug: 'foreground', label: 'Foreground' },
	{ slug: 'outline', label: 'Outline' },
	{ slug: 'neutral', label: 'Neutral' },
	{ slug: 'white', label: 'White' },
	{ slug: 'black', label: 'Black' },
];

/**
 * @param {string}                                 slug    Palette slug.
 * @param {Array<{ slug: string, color: string }>} palette Editor theme colors.
 * @return {string|undefined} Hex value for PanelColorSettings.
 */
export function getThemeColorHexBySlug( slug, palette ) {
	if ( ! slug || ! Array.isArray( palette ) ) {
		return undefined;
	}

	const match = palette.find( ( color ) => color.slug === slug );

	return match ? match.color : undefined;
}

/**
 * @param {string|undefined}                       hex     Selected palette hex from ColorPalette.
 * @param {Array<{ slug: string, color: string }>} palette Editor theme colors.
 * @return {string} Palette slug for sk-icon-has-color-* classes.
 */
export function getThemeColorSlugByHex( hex, palette ) {
	if ( ! hex || ! Array.isArray( palette ) ) {
		return '';
	}

	const normalized = hex.toLowerCase();
	const match = palette.find(
		( color ) => color.color && color.color.toLowerCase() === normalized,
	);

	return match ? match.slug : '';
}
