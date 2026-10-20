import { __ } from '@wordpress/i18n';
import { FormTokenField, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore, useEntityRecords } from '@wordpress/core-data';

import { isValidSelectedProduct } from './product-utils';

const MIN_SEARCH_LENGTH = 2;
const SEARCH_DEBOUNCE_MS = 300;

/**
 * @param {Object}                               props
 * @param {Array<{ id: number, title: string }>} props.products
 * @param {Function}                             props.onChange
 */
export default function ProductsPicker( { products = [], onChange } ) {
	const [ search, setSearch ] = useState( '' );
	const [ debouncedSearch, setDebouncedSearch ] = useState( '' );
	const debounceTimerRef = useRef( 0 );
	const suggestionProductsRef = useRef( new Map() );

	const selectedProducts = useMemo(
		() =>
			Array.isArray( products )
				? products.filter( isValidSelectedProduct )
				: [],
		[ products ],
	);
	const selectedIds = useMemo(
		() => selectedProducts.map( ( product ) => product.id ),
		[ selectedProducts ],
	);
	const selectedTitles = useMemo(
		() => selectedProducts.map( ( product ) => product.title ),
		[ selectedProducts ],
	);

	const { records: selectedRecords, isResolving: isResolvingSelected } =
		useEntityRecords( 'postType', 'product', {
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
					'product',
					searchQuery,
				) || [];

			return {
				searchRecords: records,
				isSearching: select( coreStore ).isResolving( 'getEntityRecords', [
					'postType',
					'product',
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

		const syncedProducts = selectedIds
			.map( ( id ) => {
				const record = recordsById.get( id );

				if ( record ) {
					return {
						id: record.id,
						title: record.title?.rendered || record.title || '',
					};
				}

				return (
					selectedProducts.find( ( product ) => product.id === id ) ||
					null
				);
			} )
			.filter( isValidSelectedProduct );

		const titlesChanged = syncedProducts.some( ( product, index ) => {
			return product.title !== selectedProducts[ index ]?.title;
		} );

		if (
			titlesChanged ||
			syncedProducts.length !== selectedProducts.length
		) {
			onChange( syncedProducts );
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

			const product = {
				id: record.id,
				title: record.title?.rendered || record.title || '',
			};

			if ( ! isValidSelectedProduct( product ) ) {
				return;
			}

			suggestionProductsRef.current.set( product.title, product );
			labels.push( product.title );
		} );

		return labels;
	}, [ searchRecords, selectedIds ] );

	const handleChange = ( tokenTitles ) => {
		const nextProducts = tokenTitles
			.map( ( token ) => {
				const existing = selectedProducts.find(
					( product ) => product.title === token,
				);

				if ( isValidSelectedProduct( existing ) ) {
					return existing;
				}

				const suggested = suggestionProductsRef.current.get( token );

				return isValidSelectedProduct( suggested ) ? suggested : null;
			} )
			.filter( isValidSelectedProduct );

		onChange( nextProducts );
		setSearch( '' );
		setDebouncedSearch( '' );
	};

	return (
		<>
			{ isResolvingSelected && selectedIds.length > 0 && <Spinner /> }
			<FormTokenField
				label={ __( 'Fallback products', 'skaflow' ) }
				value={ selectedTitles }
				suggestions={ suggestions }
				onChange={ handleChange }
				onInputChange={ setSearch }
				placeholder={ __( 'Search products…', 'skaflow' ) }
				help={ __(
					'Used when this post has no Shop the Look products selected. Order is preserved.',
					'skaflow',
				) }
				__experimentalExpandOnFocus
				maxSuggestions={ 20 }
				disabled={ isResolvingSelected && ! selectedProducts.length }
			/>
			{ isSearching && debouncedSearch.length >= MIN_SEARCH_LENGTH && (
				<p className="sk-shop-the-look-editor__searching">
					<Spinner />
					{ __( 'Searching products…', 'skaflow' ) }
				</p>
			) }
		</>
	);
}
