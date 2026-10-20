export const RESPONSIVE_BACKGROUND_BLOCKS = [ 'core/group', 'core/cover' ];

export const RESPONSIVE_BACKGROUND_ATTRIBUTES = {
	hwHideBackgroundOnSmall: {
		type: 'boolean',
		default: false,
	},
	hwHideBackgroundOnMedium: {
		type: 'boolean',
		default: false,
	},
	hwHideBackgroundOnLarge: {
		type: 'boolean',
		default: false,
	},
};

export const isResponsiveBackgroundBlock = ( blockName ) =>
	RESPONSIVE_BACKGROUND_BLOCKS.includes( blockName );

export const getResponsiveBackgroundClassNames = ( attributes = {} ) => {
	const classNames = [];

	if ( attributes.hwHideBackgroundOnSmall ) {
		classNames.push( 'hw-hide-bg-on-small' );
	}

	if ( attributes.hwHideBackgroundOnMedium ) {
		classNames.push( 'hw-hide-bg-on-medium' );
	}

	if ( attributes.hwHideBackgroundOnLarge ) {
		classNames.push( 'hw-hide-bg-on-large' );
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
