<?php
// This file is generated. Do not modify it manually.
return array(
	'copyright-date-block' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/copyright-date-block',
		'version' => '1.0.0',
		'title' => 'Copyright Date Block',
		'category' => 'widgets',
		'icon' => 'calendar-alt',
		'description' => 'Display the Copyright Block with the starting year.',
		'example' => array(
			
		),
		'supports' => array(
			'html' => true,
			'jsx' => true,
			'align' => true,
			'color' => array(
				'gradients' => true,
				'text' => true,
				'background' => true
			),
			'typography' => array(
				'textAlign' => true
			),
			'dimensions' => array(
				'aspectRatio' => true,
				'minHeight' => true
			),
			'background' => array(
				'backgroundImage' => true,
				'backgroundSize' => true
			),
			'layout' => array(
				'default' => array(
					'type' => 'flex',
					'flexWrap' => 'wrap'
				),
				'allowVerticalAlignment' => true,
				'allowJustification' => true
			)
		),
		'attributes' => array(
			'startingYear' => array(
				'type' => 'string',
				'default' => '2000'
			)
		),
		'textdomain' => 'happywood',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'delivery-estimate' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/delivery-estimate',
		'version' => '1.0.0',
		'title' => 'Delivery Estimate',
		'category' => 'woocommerce',
		'description' => 'Postcode and quantity lookup that returns a freight price or a quote fallback for the current product.',
		'keywords' => array(
			'delivery',
			'shipping',
			'freight',
			'postcode',
			'estimate'
		),
		'textdomain' => 'happywood',
		'icon' => 'location',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'multiple' => false,
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'editor-placeholder' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/editor-placeholder',
		'version' => '1.0.0',
		'title' => 'Editor Placeholder',
		'category' => 'theme',
		'description' => 'Site Editor notice for PHP-driven template areas. Pass title, description, and links as attributes.',
		'keywords' => array(
			'placeholder',
			'dynamic',
			'header',
			'chrome'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => ''
			),
			'description' => array(
				'type' => 'string',
				'default' => ''
			),
			'links' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'align' => array(
				'type' => 'string',
				'default' => 'wide'
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => false,
			'reusable' => false,
			'lock' => false,
			'multiple' => false,
			'align' => array(
				'wide'
			)
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'faq-groups' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/faq-groups',
		'version' => '1.0.0',
		'title' => 'FAQ Groups',
		'category' => 'widgets',
		'description' => 'Grouped FAQ accordions by selected FAQ categories.',
		'keywords' => array(
			'faq',
			'accordion',
			'questions',
			'help'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'terms' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'manual',
				'enum' => array(
					'manual',
					'menu_order',
					'name',
					'slug',
					'term_id',
					'count',
					'description',
					'term_group'
				)
			),
			'order' => array(
				'type' => 'string',
				'default' => 'asc',
				'enum' => array(
					'asc',
					'desc'
				)
			),
			'accordionTitleTag' => array(
				'type' => 'string',
				'default' => 'h3',
				'enum' => array(
					'h3',
					'span'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'hero-section' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/hero-section',
		'version' => '1.0.0',
		'title' => 'Hero Section',
		'category' => 'design',
		'icon' => 'cover-image',
		'description' => 'Full-width hero section with a background image and editable content box.',
		'keywords' => array(
			'hero',
			'banner',
			'cover',
			'section'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'align' => array(
				'type' => 'string',
				'default' => 'full'
			),
			'backgroundImageId' => array(
				'type' => 'number',
				'default' => 0
			),
			'backgroundImageUrl' => array(
				'type' => 'string',
				'default' => ''
			),
			'focalPoint' => array(
				'type' => 'object',
				'default' => array(
					'x' => 0.5,
					'y' => 0.5
				)
			),
			'style' => array(
				'type' => 'object',
				'default' => array(
					'spacing' => array(
						'padding' => array(
							'top' => 'var:preset|spacing|section-xl',
							'right' => 'var:preset|spacing|section-s',
							'bottom' => 'var:preset|spacing|section-xl',
							'left' => 'var:preset|spacing|section-s'
						)
					)
				)
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'align' => array(
				'wide',
				'full'
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'reusable' => true
		),
		'editorScript' => 'file:./index.js',
		'style' => 'file:./style-index.css'
	),
	'icon-box' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/icon-box',
		'version' => '1.0.0',
		'title' => 'Icon Box',
		'category' => 'design',
		'icon' => 'info',
		'description' => 'Display an icon with a heading and short description.',
		'keywords' => array(
			'icon',
			'feature',
			'benefit',
			'service',
			'trust'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'iconPosition' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'alignment' => array(
				'type' => 'string',
				'default' => 'center'
			),
			'layout' => array(
				'type' => 'object',
				'default' => array(
					'type' => 'flex',
					'orientation' => 'horizontal'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'reusable' => true,
			'layout' => array(
				'allowSwitching' => false,
				'allowInheriting' => false,
				'allowEditing' => false,
				'default' => array(
					'type' => 'flex'
				)
			),
			'spacing' => array(
				'blockGap' => true,
				'margin' => true,
				'padding' => true
			),
			'color' => array(
				'background' => true,
				'text' => true,
				'gradients' => false
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'info-box' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/info-box',
		'version' => '2.0.0',
		'title' => 'Info Box',
		'category' => 'design',
		'icon' => 'info',
		'description' => 'An icon with flexible inner content. The icon is a locked child block — choose the icon via the Icon Block controls. Use the toolbar to switch icon position (left / right / top).',
		'keywords' => array(
			'info',
			'icon',
			'feature',
			'benefit',
			'service',
			'infobox'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'iconPosition' => array(
				'type' => 'string',
				'default' => 'left'
			),
			'alignment' => array(
				'type' => 'string',
				'default' => 'center'
			),
			'layout' => array(
				'type' => 'object',
				'default' => array(
					'type' => 'flex',
					'orientation' => 'horizontal'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'reusable' => true,
			'layout' => array(
				'allowSwitching' => false,
				'allowInheriting' => false,
				'allowEditing' => false,
				'default' => array(
					'type' => 'flex'
				)
			),
			'color' => array(
				'background' => true,
				'text' => true,
				'gradients' => true,
				'link' => true,
				'button' => true,
				'heading' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'fontFamily' => true
			),
			'spacing' => array(
				'blockGap' => true,
				'margin' => true,
				'padding' => true
			),
			'border' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true
			),
			'dimensions' => array(
				'minHeight' => true
			),
			'shadow' => true
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'order-confirmation-fulfillment' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/order-confirmation-fulfillment',
		'version' => '1.0.0',
		'title' => 'Order Confirmation Fulfillment',
		'category' => 'woocommerce',
		'description' => 'Delivery or pickup card: address or warehouse, payment, method, contact, and the eight answers.',
		'keywords' => array(
			'order',
			'confirmation',
			'delivery',
			'pickup',
			'woocommerce'
		),
		'textdomain' => 'happywood',
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'multiple' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'post-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/post-card',
		'version' => '1.0.0',
		'title' => 'Post Card',
		'category' => 'widgets',
		'description' => 'Canonical Happywood blog post card for the current post in a Query Loop.',
		'keywords' => array(
			'post',
			'blog',
			'article',
			'card'
		),
		'textdomain' => 'happywood',
		'icon' => 'admin-post',
		'ancestor' => array(
			'core/post-template'
		),
		'usesContext' => array(
			'postId',
			'postType',
			'query',
			'queryId'
		),
		'attributes' => array(
			'titleLevel' => array(
				'type' => 'number',
				'default' => 3
			),
			'dateFormat' => array(
				'type' => 'string',
				'default' => 'F j, Y'
			),
			'showImage' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showCategory' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSingleCategoryTerm' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showReadingTime' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showExcerpt' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showAuthor' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showDate' => array(
				'type' => 'boolean',
				'default' => true
			),
			'hasBorder' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'post-filters' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/post-filters',
		'version' => '1.0.0',
		'title' => 'Post Filters',
		'category' => 'widgets',
		'description' => 'Search, category, and sort controls for the blog listing page.',
		'keywords' => array(
			'post',
			'blog',
			'filters',
			'search',
			'category'
		),
		'textdomain' => 'happywood',
		'icon' => 'filter',
		'attributes' => array(
			'showSearch' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showCategoryDropdown' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSort' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showPills' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showCount' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'multiple' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'product-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-card',
		'version' => '1.0.0',
		'title' => 'Happywood Product Card',
		'category' => 'woocommerce',
		'description' => 'Canonical Happywood product card, renders the shared product-card template part for each item in a Product Collection loop.',
		'keywords' => array(
			'woocommerce',
			'product',
			'card',
			'grid',
			'collection'
		),
		'textdomain' => 'happywood',
		'ancestor' => array(
			'woocommerce/product-template'
		),
		'usesContext' => array(
			'postId',
			'postType',
			'query',
			'queryId',
			'displayLayout'
		),
		'attributes' => array(
			'titleTag' => array(
				'type' => 'string',
				'default' => 'h2',
				'enum' => array(
					'h2',
					'h3'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'render' => 'file:./render.php'
	),
	'product-cat-banner' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-cat-banner',
		'version' => '1.0.0',
		'title' => 'Product Category Banner',
		'category' => 'woocommerce',
		'description' => 'Renders the configured banner for the current product category archive.',
		'keywords' => array(
			'woocommerce',
			'category',
			'banner',
			'archive'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'render' => 'file:./render.php'
	),
	'product-cat-child-swiper' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-cat-child-swiper',
		'version' => '1.0.0',
		'title' => 'Product Category Child Swiper',
		'category' => 'woocommerce',
		'description' => 'Horizontal swiper of immediate child product categories on taxonomy archives.',
		'keywords' => array(
			'woocommerce',
			'category',
			'swiper',
			'archive'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'showArrowsDesktop' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showArrowsMobile' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showProgressBar' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showLabels' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'styles' => array(
			array(
				'name' => 'default',
				'label' => 'Thumbnails',
				'isDefault' => true
			),
			array(
				'name' => 'card',
				'label' => 'Category cards'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'render' => 'file:./render.php'
	),
	'product-cat-swiper' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-cat-swiper',
		'version' => '1.0.0',
		'title' => 'Product Category Swiper',
		'category' => 'woocommerce',
		'description' => 'Horizontal swiper of selected product categories.',
		'keywords' => array(
			'woocommerce',
			'category',
			'swiper',
			'carousel'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'terms' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'showArrowsDesktop' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showArrowsMobile' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showProgressBar' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showLabels' => array(
				'type' => 'boolean',
				'default' => true
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'manual',
				'enum' => array(
					'manual',
					'menu_order',
					'name',
					'slug',
					'term_id',
					'count',
					'description',
					'term_group'
				)
			),
			'order' => array(
				'type' => 'string',
				'default' => 'asc',
				'enum' => array(
					'asc',
					'desc'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'styles' => array(
			array(
				'name' => 'default',
				'label' => 'Thumbnails',
				'isDefault' => true
			),
			array(
				'name' => 'card',
				'label' => 'Category cards'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'render' => 'file:./render.php'
	),
	'product-faqs' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-faqs',
		'version' => '1.0.0',
		'title' => 'Product FAQs',
		'category' => 'woocommerce',
		'description' => 'Accordion list of FAQs resolved from global, category, and product-specific configuration.',
		'keywords' => array(
			'woocommerce',
			'product',
			'faq',
			'accordion'
		),
		'textdomain' => 'happywood',
		'usesContext' => array(
			'postId'
		),
		'attributes' => array(
			'initialVisibleCount' => array(
				'type' => 'number',
				'default' => 5
			),
			'includeFaqSchema' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'product-filters' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-filters',
		'version' => '1.0.0',
		'title' => 'Product Filters',
		'category' => 'theme',
		'description' => 'Dynamic filter bar, panel, and chips on product archives.',
		'keywords' => array(
			'filters',
			'shop',
			'woocommerce',
			'archive'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'part' => array(
				'type' => 'string',
				'default' => 'chrome',
				'enum' => array(
					'chrome',
					'sidebar'
				)
			),
			'title' => array(
				'type' => 'string',
				'default' => 'Product filters'
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Filter bar, panel, and chips are rendered from PHP on the storefront.'
			),
			'links' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => false,
			'reusable' => false,
			'lock' => false,
			'multiple' => true
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'product-reviews' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-reviews',
		'version' => '1.0.0',
		'title' => 'Product Reviews',
		'category' => 'woocommerce',
		'description' => 'Review list, pagination, and submission form for the current product.',
		'keywords' => array(
			'woocommerce',
			'product',
			'reviews',
			'comments'
		),
		'textdomain' => 'happywood',
		'usesContext' => array(
			'postId'
		),
		'attributes' => array(
			'reviewsPerPage' => array(
				'type' => 'number',
				'default' => 3
			),
			'displayReviewsSummary' => array(
				'type' => 'boolean',
				'default' => true
			),
			'backgroundColor' => array(
				'type' => 'string',
				'default' => 'background'
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			),
			'color' => array(
				'background' => true
			),
			'spacing' => array(
				'padding' => true
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'product-reviews-slider' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-reviews-slider',
		'version' => '1.0.0',
		'title' => 'Product Reviews Slider',
		'category' => 'woocommerce',
		'description' => 'Marketing Swiper carousel of verified WooCommerce product reviews.',
		'keywords' => array(
			'woocommerce',
			'review',
			'testimonial',
			'swiper',
			'carousel'
		),
		'textdomain' => 'happywood',
		'icon' => 'slides',
		'attributes' => array(
			'reviewsToShow' => array(
				'type' => 'number',
				'default' => 8
			),
			'orderBy' => array(
				'type' => 'string',
				'default' => 'rating',
				'enum' => array(
					'rating',
					'date'
				)
			),
			'productId' => array(
				'type' => 'number',
				'default' => 0
			),
			'categoryIds' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'verifiedOnly' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showSummary' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showReviewRating' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showReviewerName' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showReviewContent' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showProductName' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showReviewDate' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showArrowsDesktop' => array(
				'type' => 'boolean',
				'default' => true
			),
			'showArrowsMobile' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showProgressBar' => array(
				'type' => 'boolean',
				'default' => true
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js',
		'render' => 'file:./render.php'
	),
	'product-reviews-summary' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-reviews-summary',
		'version' => '1.0.0',
		'title' => 'Product Reviews Summary',
		'category' => 'woocommerce',
		'description' => 'Average rating and histogram for the current product (single-product template).',
		'keywords' => array(
			'woocommerce',
			'product',
			'reviews',
			'rating',
			'summary'
		),
		'textdomain' => 'happywood',
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'product-swatches' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-swatches',
		'version' => '1.0.0',
		'title' => 'Product Swatches',
		'category' => 'woocommerce',
		'description' => 'Color swatches for variable products in product grids (Mode A image swap).',
		'keywords' => array(
			'woocommerce',
			'product',
			'swatches',
			'colour',
			'color'
		),
		'textdomain' => 'happywood',
		'ancestor' => array(
			'woocommerce/product-template'
		),
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'product-view-switcher' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/product-view-switcher',
		'version' => '1.0.0',
		'title' => 'Product View Switcher',
		'category' => 'theme',
		'description' => 'Grid and list toggle for WooCommerce product archive toolbars.',
		'keywords' => array(
			'shop',
			'grid',
			'list',
			'view',
			'woocommerce',
			'archive'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => 'Product view switcher'
			),
			'description' => array(
				'type' => 'string',
				'default' => 'Grid and list icons render on product archives. Preference is saved in the browser.'
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'multiple' => true
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'quick-view-button' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/quick-view-button',
		'version' => '1.0.0',
		'title' => 'Quick View Button',
		'category' => 'woocommerce',
		'description' => 'Eye icon trigger that opens the quick view popup for a product card.',
		'keywords' => array(
			'woocommerce',
			'product',
			'quick view',
			'popup'
		),
		'textdomain' => 'happywood',
		'ancestor' => array(
			'woocommerce/product-template'
		),
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'review-card' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/review-card',
		'version' => '1.0.0',
		'title' => 'Review Card',
		'category' => 'woocommerce',
		'description' => 'Verified WooCommerce product review card for the marketing reviews slider.',
		'keywords' => array(
			'review',
			'testimonial',
			'woocommerce',
			'verified'
		),
		'textdomain' => 'happywood',
		'icon' => 'star-filled',
		'attributes' => array(
			'commentId' => array(
				'type' => 'number',
				'default' => 0
			),
			'variant' => array(
				'type' => 'string',
				'default' => 'carousel',
				'enum' => array(
					'carousel',
					'single'
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => false,
			'reusable' => false,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'shop-the-look' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/shop-the-look',
		'version' => '1.0.0',
		'title' => 'Shop the Look',
		'category' => 'widgets',
		'description' => 'Sidebar widget showing hand-picked products from the current post with template fallbacks.',
		'keywords' => array(
			'shop',
			'look',
			'product',
			'sidebar',
			'blog',
			'post'
		),
		'textdomain' => 'happywood',
		'icon' => 'cart',
		'usesContext' => array(
			'postId',
			'postType'
		),
		'attributes' => array(
			'fallbackHeading' => array(
				'type' => 'string',
				'default' => 'Shop the Look'
			),
			'fallbackMessage' => array(
				'type' => 'string',
				'default' => 'Transform your space with these handpicked pieces.'
			),
			'fallbackProducts' => array(
				'type' => 'array',
				'default' => array(
					
				)
			),
			'showViewAll' => array(
				'type' => 'boolean',
				'default' => true
			),
			'viewAllLabel' => array(
				'type' => 'string',
				'default' => 'View All'
			),
			'viewAllUrl' => array(
				'type' => 'string',
				'default' => ''
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false,
			'align' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'site-header' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/site-header',
		'version' => '1.0.0',
		'title' => 'Site Header',
		'category' => 'theme',
		'description' => 'PHP site header on the frontend; editor notice in wp-admin.',
		'keywords' => array(
			'header',
			'navigation',
			'menu'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'title' => array(
				'type' => 'string',
				'default' => ''
			),
			'description' => array(
				'type' => 'string',
				'default' => ''
			),
			'links' => array(
				'type' => 'array',
				'default' => array(
					
				)
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => false,
			'reusable' => false,
			'lock' => false,
			'multiple' => false
		),
		'editorScript' => 'file:./index.js',
		'render' => 'file:./render.php'
	),
	'stat-counter' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/stat-counter',
		'version' => '1.0.0',
		'title' => 'Stat Counter',
		'category' => 'design',
		'icon' => 'chart-bar',
		'description' => 'Animated stat counter with prefix, number, suffix and label. Counts from zero when it enters the viewport.',
		'keywords' => array(
			'counter',
			'stat',
			'number',
			'statistic',
			'count',
			'animate',
			'stats'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'prefix' => array(
				'type' => 'string',
				'default' => ''
			),
			'number' => array(
				'type' => 'number',
				'default' => 0
			),
			'numberSeparator' => array(
				'type' => 'string',
				'default' => ','
			),
			'suffix' => array(
				'type' => 'string',
				'default' => '+'
			),
			'label' => array(
				'type' => 'string',
				'default' => ''
			),
			'animationDuration' => array(
				'type' => 'number',
				'default' => 2000
			)
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'textAlign' => true,
			'color' => array(
				'background' => true,
				'text' => true,
				'gradients' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'fontFamily' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'border' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true
			),
			'shadow' => true
		),
		'styles' => array(
			array(
				'name' => 'default',
				'label' => 'Default',
				'isDefault' => true
			),
			array(
				'name' => 'card',
				'label' => 'Card'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'viewScript' => 'file:./view.js'
	),
	'tag' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/tag',
		'version' => '1.0.0',
		'title' => 'Tag',
		'category' => 'text',
		'icon' => 'tag',
		'description' => 'Display a styled tag label with preset styles for promos, stock, and product badges.',
		'parent' => array(
			'happywood/tags'
		),
		'keywords' => array(
			'tag',
			'badge',
			'label',
			'pill'
		),
		'example' => array(
			'attributes' => array(
				'content' => 'New',
				'tagName' => 'span',
				'className' => 'is-style-new'
			)
		),
		'styles' => array(
			array(
				'name' => 'sale',
				'label' => 'Sale',
				'isDefault' => true
			),
			array(
				'name' => 'best',
				'label' => 'Best'
			),
			array(
				'name' => 'stock',
				'label' => 'Stock'
			),
			array(
				'name' => 'new',
				'label' => 'New'
			)
		),
		'selectors' => array(
			'root' => '.wp-block-happywood-tag',
			'color' => array(
				'text' => '.hw-tag__label',
				'background' => '.hw-tag__label'
			),
			'typography' => array(
				'selector' => '.hw-tag__label'
			),
			'spacing' => array(
				'padding' => '.hw-tag__label',
				'margin' => '.wp-block-happywood-tag'
			),
			'border' => '.hw-tag__label'
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'align' => true,
			'color' => array(
				'gradients' => false,
				'text' => true,
				'background' => true
			),
			'typography' => array(
				'fontSize' => true,
				'lineHeight' => true,
				'fontWeight' => true,
				'fontStyle' => true,
				'textTransform' => true,
				'letterSpacing' => true,
				'textDecoration' => true,
				'fontFamily' => true,
				'textAlign' => true
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true
			),
			'border' => array(
				'color' => true,
				'radius' => true,
				'style' => true,
				'width' => true
			)
		),
		'attributes' => array(
			'content' => array(
				'type' => 'string',
				'source' => 'html',
				'selector' => '.hw-tag__label',
				'default' => ''
			),
			'tagName' => array(
				'type' => 'string',
				'default' => 'span'
			),
			'align' => array(
				'type' => 'string'
			)
		),
		'textdomain' => 'happywood',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css'
	),
	'tags' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/tags',
		'version' => '1.0.0',
		'title' => 'Tags',
		'category' => 'text',
		'icon' => 'tag',
		'description' => 'Display a horizontal group of tag labels.',
		'keywords' => array(
			'tags',
			'badges',
			'labels',
			'pills'
		),
		'supports' => array(
			'html' => false,
			'anchor' => true,
			'align' => array(
				'wide',
				'full'
			),
			'layout' => array(
				'allowSwitching' => false,
				'allowInheriting' => false,
				'allowEditing' => false,
				'default' => array(
					'type' => 'flex',
					'flexWrap' => 'wrap'
				)
			),
			'spacing' => array(
				'blockGap' => true,
				'margin' => true,
				'padding' => true
			)
		),
		'textdomain' => 'happywood',
		'editorScript' => 'file:./index.js'
	),
	'taxonomy-terms' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/taxonomy-terms',
		'version' => '1.0.0',
		'title' => 'Taxonomy Terms',
		'category' => 'theme',
		'description' => 'Display assigned taxonomy terms for the current post in the loop.',
		'keywords' => array(
			'taxonomy',
			'terms',
			'category',
			'tag',
			'label'
		),
		'textdomain' => 'happywood',
		'icon' => 'tag',
		'usesContext' => array(
			'postId',
			'postType',
			'queryId'
		),
		'attributes' => array(
			'taxonomy' => array(
				'type' => 'string',
				'default' => 'category'
			),
			'limit' => array(
				'type' => 'number'
			),
			'isLink' => array(
				'type' => 'boolean',
				'default' => false
			)
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'anchor' => true,
			'align' => array(
				'wide',
				'full'
			),
			'spacing' => array(
				'margin' => true,
				'blockGap' => true
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'verified-reviews-summary' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/verified-reviews-summary',
		'version' => '1.0.0',
		'title' => 'Verified Reviews Summary',
		'category' => 'woocommerce',
		'description' => 'Site-wide average rating and verified WooCommerce review count.',
		'keywords' => array(
			'woocommerce',
			'review',
			'rating',
			'summary',
			'verified'
		),
		'textdomain' => 'happywood',
		'icon' => 'star-filled',
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => true,
			'lock' => false,
			'align' => array(
				'wide',
				'full'
			)
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	),
	'wishlist-button' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'happywood/wishlist-button',
		'version' => '1.0.0',
		'title' => 'Wishlist Button',
		'category' => 'woocommerce',
		'description' => 'Heart icon toggle for adding or removing a product from the customer wishlist.',
		'keywords' => array(
			'woocommerce',
			'product',
			'wishlist',
			'heart'
		),
		'textdomain' => 'happywood',
		'attributes' => array(
			'context' => array(
				'type' => 'string',
				'default' => 'card',
				'enum' => array(
					'card',
					'summary'
				)
			)
		),
		'usesContext' => array(
			'postId'
		),
		'supports' => array(
			'html' => false,
			'inserter' => true,
			'reusable' => false,
			'lock' => false
		),
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
