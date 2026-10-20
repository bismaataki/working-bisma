export const RESPONSIVE_BACKGROUND_BLOCKS = [ 'core/group', 'core/cover' ];

export const RESPONSIVE_BACKGROUND_ATTRIBUTES = {
	skHideBackgroundOnSmall: {
		type: 'boolean',
		default: false,
	},
	skHideBackgroundOnMedium: {
		type: 'boolean',
		default: false,
	},
	skHideBackgroundOnLarge: {
		type: 'boolean',
		default: false,
	},
};

export const isResponsiveBackgroundBlock = ( blockName ) =>
	RESPONSIVE_BACKGROUND_BLOCKS.includes( blockName );

export const getResponsiveBackgroundClassNames = ( attributes = {} ) => {
	const classNames = [];

	if ( attributes.skHideBackgroundOnSmall ) {
		classNames.push( 'sk-hide-bg-on-small' );
	}

	if ( attributes.skHideBackgroundOnMedium ) {
		classNames.push( 'sk-hide-bg-on-medium' );
	}

	if ( attributes.skHideBackgroundOnLarge ) {
		classNames.push( 'sk-hide-bg-on-large' );
	}

	return classNames.join( ' ' );
};

export const mergeResponsiveBackgroundClassName = ( className, attributes ) => {
	const responsiveClassNames = getResponsiveBackgroundClassNames( attributes );

	if ( ! responsiveClassNames ) {
		return className;
	}

	return [ className, responsiveClassNames ].filter( Boolean ).join( ' ' );
};
