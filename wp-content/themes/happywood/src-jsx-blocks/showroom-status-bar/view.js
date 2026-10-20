import {
	CONTENT_UPDATED_EVENT,
	isQueryRoot,
} from '../../src/js/shared/delegated-events';

const STATUS_SELECTOR = '[data-hw-showroom-hours-status]';
const STATUS_TEXT_SELECTOR = '[data-hw-showroom-hours-status-text]';

const STATUS_CLASSES = [
	'is-open-now',
	'is-opens-today',
	'is-closed-now',
	'is-closed-today',
	'is-unavailable',
];

const WEEKDAY_ORDER = [
	'monday',
	'tuesday',
	'wednesday',
	'thursday',
	'friday',
	'saturday',
	'sunday',
];

/**
 * @param {string} hhmm 24-hour HH:MM value.
 * @return {string} 12-hour clock string.
 */
function formatDisplayTime( hhmm ) {
	const [ hourPart, minutePart ] = hhmm
		.split( ':' )
		.map( ( value ) => Number( value ) );
	const period = hourPart >= 12 ? 'pm' : 'am';
	const hour12 = hourPart % 12 || 12;

	return `${ hour12 }:${ String( minutePart ).padStart( 2, '0' ) }${ period }`;
}

/**
 * @param {string} opens  HH:MM.
 * @param {string} closes HH:MM.
 * @return {string} Opening-hours range for display.
 */
function formatDisplayRange( opens, closes ) {
	return `${ formatDisplayTime( opens ) } – ${ formatDisplayTime( closes ) }`;
}

/**
 * @param {string} hhmm HH:MM.
 * @return {number} Minutes since midnight.
 */
function timeToMinutes( hhmm ) {
	const [ hours, minutes ] = hhmm.split( ':' ).map( ( value ) => Number( value ) );

	return ( hours * 60 ) + minutes;
}

/**
 * @param {string} timezone IANA timezone.
 * @return {{ weekday: string, minutes: number }} Current weekday and minutes in the showroom timezone.
 */
function getNowInTimezone( timezone ) {
	const formatter = new Intl.DateTimeFormat( 'en-US', {
		timeZone: timezone,
		weekday: 'long',
		hour: '2-digit',
		minute: '2-digit',
		hour12: false,
	} );

	const parts = formatter.formatToParts( new Date() );

	return {
		weekday: (
			parts.find( ( part ) => part.type === 'weekday' )?.value || 'monday'
		).toLowerCase(),
		minutes: timeToMinutes(
			`${ parts.find( ( part ) => part.type === 'hour' )?.value || '00' }:${ parts.find( ( part ) => part.type === 'minute' )?.value || '00' }`,
		),
	};
}

/**
 * @param {Record<string, string>} labels  Label templates from PHP.
 * @param {string}                 key     Label key.
 * @param {string}                 [value] Replacement for `%s`.
 * @return {string} Label with `{value}` replaced.
 */
function formatLabel( labels, key, value = '' ) {
	const template = labels?.[ key ] || '';

	return template.includes( '%s' ) ? template.replace( '%s', value ) : template;
}

/**
 * @param {Record<string, {opens: string, closes: string}|null>} schedule       Weekly schedule.
 * @param {Record<string, string>}                               dayLabels      Translated weekday names.
 * @param {string}                                               currentWeekday Lowercase weekday slug for "today".
 * @return {string} Next opening time as a display string.
 */
function findNextOpeningDisplay( schedule, dayLabels, currentWeekday ) {
	const startIndex = WEEKDAY_ORDER.indexOf( currentWeekday );

	if ( startIndex === -1 ) {
		return '';
	}

	for ( let offset = 1; offset <= 7; offset++ ) {
		const slug =
			WEEKDAY_ORDER[ ( startIndex + offset ) % WEEKDAY_ORDER.length ];
		const day = schedule?.[ slug ];

		if ( day?.opens ) {
			const dayName = dayLabels?.[ slug ] || slug;

			return `${ dayName } ${ formatDisplayTime( day.opens ) }`;
		}
	}

	return '';
}

/**
 * @param {string}                                               timezone  IANA timezone identifier.
 * @param {Record<string, {opens: string, closes: string}|null>} schedule  Weekly schedule.
 * @param {Record<string, string>}                               labels    Translated label templates.
 * @param {Record<string, string>}                               dayLabels Translated weekday names.
 * @return {{ state: string, label: string, className: string }} Live open/closed state and label.
 */
function resolveLiveStatus( timezone, schedule, labels, dayLabels ) {
	let now;

	try {
		now = getNowInTimezone( timezone );
	} catch {
		return {
			state: 'unavailable',
			label: formatLabel( labels, 'hours_unavailable' ),
			className: 'is-unavailable',
		};
	}

	const today = schedule?.[ now.weekday ] ?? null;

	if ( ! today?.opens || ! today?.closes ) {
		return {
			state: 'closed_today',
			label: formatLabel( labels, 'closed_today' ),
			className: 'is-closed-today',
		};
	}

	const opensMinutes = timeToMinutes( today.opens );
	const closesMinutes = timeToMinutes( today.closes );
	const range = formatDisplayRange( today.opens, today.closes );

	if ( now.minutes >= opensMinutes && now.minutes < closesMinutes ) {
		return {
			state: 'open_now',
			label: formatLabel( labels, 'open_now', range ),
			className: 'is-open-now',
		};
	}

	if ( now.minutes < opensMinutes ) {
		return {
			state: 'opens_today',
			label: formatLabel( labels, 'opens_today', range ),
			className: 'is-opens-today',
		};
	}

	const nextOpen = findNextOpeningDisplay( schedule, dayLabels, now.weekday );

	if ( nextOpen ) {
		return {
			state: 'closed_now',
			label: formatLabel( labels, 'closed_now_opens_next', nextOpen ),
			className: 'is-closed-now',
		};
	}

	return {
		state: 'closed_now',
		label: formatLabel( labels, 'closed_now', range ),
		className: 'is-closed-now',
	};
}

/**
 * @param {HTMLElement} pill Status pill element.
 */
function updateStatusPill( pill ) {
	const timezone = pill.dataset.timezone || '';
	let schedule = {};
	let labels = {};
	let dayLabels = {};

	try {
		schedule = JSON.parse( pill.dataset.hours || '{}' );
		labels = JSON.parse( pill.dataset.labels || '{}' );
		dayLabels = JSON.parse( pill.dataset.dayLabels || '{}' );
	} catch {
		return;
	}

	if ( ! timezone ) {
		return;
	}

	const status = resolveLiveStatus( timezone, schedule, labels, dayLabels );
	const textEl = pill.querySelector( STATUS_TEXT_SELECTOR );
	const root = pill.closest( '.hw-showroom-status-bar' );

	pill.classList.remove( ...STATUS_CLASSES );
	pill.classList.add( status.className );

	if ( root ) {
		STATUS_CLASSES.forEach( ( className ) => {
			root.classList.remove(
				`hw-showroom-status-bar--${ className.replace( 'is-', '' ) }`,
			);
		} );
		root.classList.add(
			`hw-showroom-status-bar--${ status.state.replace( /_/g, '-' ) }`,
		);
	}

	if ( textEl ) {
		textEl.textContent = status.label;
	}
}

/**
 * @param {Element|Document|DocumentFragment} root Search root.
 */
function initShowroomStatusBars( root = document ) {
	root.querySelectorAll( STATUS_SELECTOR ).forEach( ( pill ) => {
		updateStatusPill( pill );
	} );
}

document.addEventListener( 'DOMContentLoaded', () => {
	initShowroomStatusBars();
} );

document.addEventListener( CONTENT_UPDATED_EVENT, ( event ) => {
	const container = event.detail?.container;

	initShowroomStatusBars( isQueryRoot( container ) ? container : document );
} );
