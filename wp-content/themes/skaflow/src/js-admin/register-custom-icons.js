/**
 * Registers the theme's curated Lucide icon set with the Icon Block plugin
 * (outermost/icon-block), so editors can insert them via that block's own
 * inserter/search modal, grouped under a "Skaflow" category - in
 * addition to the plugin's own default icon library.
 *
 * This is a separate icon-picking surface from the Button block's icon
 * picker (see src/blocks-extension/extend-core-button/button-icons.js) - they intentionally share the same curated
 * icon set/labels (see skaflow-icon-block-icons.js) but are otherwise
 * independent integrations, since the Icon Block plugin owns its own
 * rendering/attributes and isn't related to `core/button`.
 *
 * @see https://nickdiego.com/adding-custom-icons-to-the-icon-block
 */

import { SKAFLOW_ICON_BLOCK_ICONS } from './skaflow-icon-block-icons';

wp.domReady( () => {
	const { __ } = wp.i18n;
	const { addFilter } = wp.hooks;

	function skaflowAddCustomIcons( icons ) {
		const customIconCategories = [
			{
				name: 'skaflow',
				title: __( 'Skaflow', 'skaflow' ),
			},
		];

		const customIconType = [
			{
				isDefault: true,
				type: 'skaflow',
				title: __( 'Skaflow', 'skaflow' ),
				icons: SKAFLOW_ICON_BLOCK_ICONS,
				categories: customIconCategories,
			},
		];

		return [].concat( icons, customIconType );
	}

	addFilter(
		'iconBlock.icons',
		'skaflow/icon-block-custom-icons',
		skaflowAddCustomIcons,
	);
} );
