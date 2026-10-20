/**
 * Curated list of Lucide icons available in the Button block's icon
 * picker (see src/blocks-extension/extend-core-button/button-icons.js). Intentionally a curated
 * subset - not the full Lucide library (~2,000 icons) - to match the
 * project's Figma design system.
 *
 * THIS FILE IS THE SINGLE SOURCE OF TRUTH for the curated set. Four other
 * surfaces are generated from it - do not hand-edit those:
 *
 *   assets/css/button-icon-font.css
 *   src/sass/icon-font/_variables.scss
 *   src/js-admin/happywood-icon-block-icons.js
 *   includes/menu/happywood-lucide-icon-choices.php
 *
 * To add or remove an icon: edit the array below, then run
 * `npm run build:icons`. See .cursor/rules/project-lucide-icons.mdc.
 *
 * Rendering itself is handled purely via CSS - see
 * assets/css/button-icon-font.css (enqueued globally by
 * Happywood\Lucide_Icons in lib/class-lucide-icons.php) - so this file
 * only needs to expose the slug/label list, not any icon components.
 *
 * `custom: true` marks an icon that is NOT a glyph in the Lucide font and
 * is rendered from assets/icons/{slug}.svg via a CSS mask instead. Such an
 * icon has no codepoint, so it is skipped in the Sass codepoint map.
 */
export const HAPPYWOOD_LUCIDE_ICON_OPTIONS = [
	// Navigation & UI
	{ slug: 'arrow-left', label: 'Arrow Left' },
	{ slug: 'arrow-right', label: 'Arrow Right' },
	{ slug: 'chevron-up', label: 'Chevron Up' },
	{ slug: 'chevron-down', label: 'Chevron Down' },
	{ slug: 'chevron-left', label: 'Chevron Left' },
	{ slug: 'chevron-right', label: 'Chevron Right' },
	{ slug: 'menu', label: 'Menu' },
	{ slug: 'x', label: 'X' },
	{ slug: 'check', label: 'Check' },
	// 'circle-check-big' is the same glyph as 'check-circle' in the Lucide
	// font (both U+E07C), so only one of the pair is carried here.
	{ slug: 'check-circle', label: 'Check Circle' },
	{ slug: 'badge-check', label: 'Badge Check' },
	{ slug: 'clipboard-list', label: 'Clipboard List' },
	{ slug: 'filter', label: 'Filter' },
	{ slug: 'minus', label: 'Minus' },
	{ slug: 'plus', label: 'Plus' },
	{ slug: 'search', label: 'Search' },
	{ slug: 'sliders-horizontal', label: 'Sliders Horizontal' },
	{ slug: 'trash-2', label: 'Trash 2' },
	{ slug: 'grid-2x2', label: 'Grid 2x2' },
	{ slug: 'grid-3x3', label: 'Grid 3x3' },
	{ slug: 'list', label: 'List' },
	{ slug: 'eye', label: 'Eye' },

	// People & sentiment
	{ slug: 'hand-heart', label: 'Hand Heart' },
	{ slug: 'handshake', label: 'Handshake' },
	{ slug: 'heart', label: 'Heart' },
	{ slug: 'smile', label: 'Smile' },
	{ slug: 'smile-plus', label: 'Smile Plus' },
	{ slug: 'thumbs-up', label: 'Thumbs Up' },
	{ slug: 'user', label: 'User' },
	{ slug: 'user-plus', label: 'User Plus' },

	// Communication
	{ slug: 'mail', label: 'Mail' },
	{ slug: 'mail-open', label: 'Mail Open' },
	{ slug: 'megaphone', label: 'Megaphone' },
	{ slug: 'message-circle-check', label: 'Message Circle Check' },
	{ slug: 'messages-square', label: 'Messages Square' },
	{ slug: 'notebook-pen', label: 'Notebook Pen' },
	{ slug: 'phone', label: 'Phone' },
	{ slug: 'layers', label: 'Layers' },
	{ slug: 'paper-plane', label: 'Paper Plane', custom: true },

	// Commerce
	{ slug: 'briefcase', label: 'Briefcase' },
	{ slug: 'credit-card', label: 'Credit Card' },
	{ slug: 'package', label: 'Package' },
	{ slug: 'percent', label: 'Percent' },
	{ slug: 'shopping-bag', label: 'Shopping Bag' },
	{ slug: 'shopping-cart', label: 'Shopping Cart' },
	{ slug: 'store', label: 'Store' },
	{ slug: 'tag', label: 'Tag' },
	{ slug: 'truck', label: 'Truck' },
	{ slug: 'wallet', label: 'Wallet' },

	// Content & location
	{ slug: 'album', label: 'Album' },
	{ slug: 'book-open', label: 'Book Open' },
	{ slug: 'file-text', label: 'File Text' },
	{ slug: 'pencil', label: 'Pencil' },
	{ slug: 'map-pin', label: 'Map Pin' },
	{ slug: 'map-pinned', label: 'Map Pinned' },
	{ slug: 'map-plus', label: 'Map Plus' },
	{ slug: 'clock', label: 'Clock' },
	{ slug: 'gauge', label: 'Gauge' },
	{ slug: 'trending-up', label: 'Trending Up' },

	// Awards & recognition
	{ slug: 'award', label: 'Award' },
	{ slug: 'circle-star', label: 'Circle Star' },
	{ slug: 'medal', label: 'Medal' },
	{ slug: 'star', label: 'Star' },
	{ slug: 'trophy', label: 'Trophy' },

	// Trades & services
	{ slug: 'shield', label: 'Shield' },
	{ slug: 'shield-check', label: 'Shield Check' },
	{ slug: 'drill', label: 'Drill' },
	{ slug: 'gavel', label: 'Gavel' },
	{ slug: 'hammer', label: 'Hammer' },
	{ slug: 'leaf', label: 'Leaf' },
	{ slug: 'lightbulb', label: 'Lightbulb' },
	{ slug: 'recycle', label: 'Recycle' },
	{ slug: 'wrench', label: 'Wrench' },
	{ slug: 'wrench-off', label: 'Wrench Off' },
	{ slug: 'zap', label: 'Zap' },
];
