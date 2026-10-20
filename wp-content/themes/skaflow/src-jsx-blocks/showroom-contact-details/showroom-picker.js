import { __ } from '@wordpress/i18n';
import { FormTokenField, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore, useEntityRecords } from '@wordpress/core-data';

import { isValidSelectedShowroom } from './showroom-utils';

const MIN_SEARCH_LENGTH = 2;
const SEARCH_DEBOUNCE_MS = 300;

/**
 * @param {Object}                               props
 * @param {Array<{ id: number, title: string }>} props.showrooms
 * @param {Function}                             props.onChange
 */
export default function ShowroomPicker( { showrooms = [], onChange } ) {
	const [ search, setSearch ] = useState( '' );
	const [ debouncedSearch, setDebouncedSearch ] = useState( '' );
	const debounceTimerRef = useRef( 0 );
	const suggestionShowroomsRef = useRef( new Map() );

	const selectedShowrooms = useMemo(
		() =>
			Array.isArray( showrooms )
				? showrooms.filter( isValidSelectedShowroom )
				: [],
		[ showrooms ],
	);
	const selectedIds = useMemo(
		() => selectedShowrooms.map( ( showroom ) => showroom.id ),
		[ selectedShowrooms ],
	);
	const selectedTitles = useMemo(
		() => selectedShowrooms.map( ( showroom ) => showroom.title ),
		[ selectedShowrooms ],
	);

	const { records: selectedRecords, isResolving: isResolvingSelected } =
		useEntityRecords( 'postType', 'showrooms', {
			include: selectedIds.length ? selectedIds : [ 0 ],
			per_page: Math.max( selectedIds.length, 1 ),
			status: 'publish',
		} );

	useEffect( () => {
		window.clearTimeout( debounceTimerRef.current );
		debounceTimerRef.current = window.setTimeout( () => {
			setDebouncedSearch( search.trim() );
		}, SEARCH_DEBOUNCE_MS );

		return () => {
			window.clearTimeout( debounceTimerRef.current );
		};
	}, [ search ] );

	const searchQuery = useMemo( () => {
		if ( debouncedSearch.length < MIN_SEARCH_LENGTH ) {
			return null;
		}

		return {
			search: debouncedSearch,
			per_page: 20,
			orderby: 'title',
			order: 'asc',
			status: 'publish',
			exclude: selectedIds,
		};
	}, [ debouncedSearch, selectedIds ] );

	const { searchRecords, isSearching } = useSelect(
		( select ) => {
			if ( ! searchQuery ) {
				return {
					searchRecords: [],
					isSearching: false,
				};
			}

			const records =
				select( coreStore ).getEntityRecords(
					'postType',
					'showrooms',
					searchQuery,
				) || [];

			return {
				searchRecords: records,
				isSearching: select( coreStore ).isResolving( 'getEntityRecords', [
					'postType',
					'showrooms',
					searchQuery,
				] ),
			};
		},
		[ searchQuery ],
	);

	useEffect( () => {
		if ( ! selectedRecords?.length || ! selectedIds.length ) {
			return;
		}

		const recordsById = new Map(
			selectedRecords.map( ( record ) => [ record.id, record ] ),
		);

		const syncedShowrooms = selectedIds
			.map( ( id ) => {
				const record = recordsById.get( id );

				if ( record ) {
					return {
						id: record.id,
						title: record.title?.rendered || record.title || '',
					};
				}

				return (
					selectedShowrooms.find( ( showroom ) => showroom.id === id ) ||
					null
				);
			} )
			.filter( isValidSelectedShowroom );

		const titlesChanged = syncedShowrooms.some( ( showroom, index ) => {
			return showroom.title !== selectedShowrooms[ index ]?.title;
		} );

		if (
			titlesChanged ||
			syncedShowrooms.length !== selectedShowrooms.length
		) {
			onChange( syncedShowrooms );
		}
		// eslint-disable-next-line react-hooks/exhaustive-deps -- sync labels when core-data records resolve.
	}, [ selectedRecords, selectedIds.join( ',' ) ] );

	const suggestions = useMemo( () => {
		if ( ! searchRecords.length ) {
			return [];
		}

		const labels = [];

		searchRecords.forEach( ( record ) => {
			if ( selectedIds.includes( record.id ) ) {
				return;
			}

			const showroom = {
				id: record.id,
				title: record.title?.rendered || record.title || '',
			};

			if ( ! isValidSelectedShowroom( showroom ) ) {
				return;
			}

			suggestionShowroomsRef.current.set( showroom.title, showroom );
			labels.push( showroom.title );
		} );

		return labels;
	}, [ searchRecords, selectedIds ] );

	const handleChange = ( tokenTitles ) => {
		const nextShowrooms = tokenTitles
			.map( ( token ) => {
				const existing = selectedShowrooms.find(
					( showroom ) => showroom.title === token,
				);

				if ( isValidSelectedShowroom( existing ) ) {
					return existing;
				}

				const suggested = suggestionShowroomsRef.current.get( token );

				return isValidSelectedShowroom( suggested ) ? suggested : null;
			} )
			.filter( isValidSelectedShowroom );

		onChange( nextShowrooms );
		setSearch( '' );
		setDebouncedSearch( '' );
	};

	return (
		<>
			{ isResolvingSelected && selectedIds.length > 0 && <Spinner /> }
			<FormTokenField
				label={ __( 'Showrooms', 'skaflow' ) }
				value={ selectedTitles }
				suggestions={ suggestions }
				onChange={ handleChange }
				onInputChange={ setSearch }
				placeholder={ __( 'Search showrooms…', 'skaflow' ) }
				help={ __(
					'Leave empty to include all showrooms. Order here is preserved in the dropdown.',
					'skaflow',
				) }
				__experimentalExpandOnFocus
				maxSuggestions={ 20 }
				disabled={ isResolvingSelected && ! selectedShowrooms.length }
			/>
			{ isSearching && debouncedSearch.length >= MIN_SEARCH_LENGTH && (
				<p className="sk-showroom-contact-details-editor__searching">
					<Spinner />
					{ __( 'Searching showrooms…', 'skaflow' ) }
				</p>
			) }
		</>
	);
}
