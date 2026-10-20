import { __ } from '@wordpress/i18n';

/**
 * @param {{ id?: number, title?: string } | null | undefined} showroom
 * @return {showroom is { id: number, title: string }} Whether the value is a selected showroom object.
 */
export function isValidSelectedShowroom( showroom ) {
	return (
		Boolean( showroom ) &&
		typeof showroom.id === 'number' &&
		showroom.id > 0 &&
		typeof showroom.title === 'string' &&
		showroom.title.length > 0
	);
}

/**
 * @return {Array<{ label: string, value: string }>} CTA choices for the showroom card inspector.
 */
export function getShowroomCardCtaOptions() {
	return [
		{ label: __( 'Get Directions', 'happywood' ), value: 'directions' },
		{ label: __( 'Learn More', 'happywood' ), value: 'learn-more' },
	];
}
