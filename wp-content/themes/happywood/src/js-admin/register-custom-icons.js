/**
 * Registers the theme's curated Lucide icon set with the Icon Block plugin
 * (outermost/icon-block), so editors can insert them via that block's own
 * inserter/search modal, grouped under a "Happy Wood" category - in
 * addition to the plugin's own default icon library.
 *
 * This is a separate icon-picking surface from the Button block's icon
 * picker (see src/blocks-extension/extend-core-button/button-icons.js) - they intentionally share the same curated
 * icon set/labels (see happywood-icon-block-icons.js) but are otherwise
 * independent integrations, since the Icon Block plugin owns its own
 * rendering/attributes and isn't related to `core/button`.
 *
 * @see https://nickdiego.com/adding-custom-icons-to-the-icon-block
 */

import { HAPPYWOOD_ICON_BLOCK_ICONS } from './happywood-icon-block-icons';

wp.domReady( () => {
	const { __ } = wp.i18n;
	const { addFilter } = wp.hooks;

	function happywoodAddCustomIcons( icons ) {
		const customIconCategories = [
			{
				name: 'happywood',
				title: __( 'Happy Wood', 'happywood' ),
			},
		];

		const customIconType = [
			{
				isDefault: true,
				type: 'happywood',
				title: __( 'Happy Wood', 'happywood' ),
				icons: HAPPYWOOD_ICON_BLOCK_ICONS,
				categories: customIconCategories,
			},
		];

		return [].concat( icons, customIconType );
	}

	addFilter(
		'iconBlock.icons',
		'happywood/icon-block-custom-icons',
		happywoodAddCustomIcons,
	);
} );
