import { __ } from '@wordpress/i18n';
import { ComboboxControl, Spinner } from '@wordpress/components';
import { useEffect, useMemo, useRef, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';

const MIN_SEARCH_LENGTH = 2;
const SEARCH_DEBOUNCE_MS = 300;
const NONE_VALUE = '0';

/**
 * @param {Object}   props
 * @param {number}   props.productId Selected product ID (0 = all products).
 * @param {Function} props.onChange  Callback when selection changes.
 */
export default function ProductPicker( { productId = 0, onChange } ) {
	const [ search, setSearch ] = useState( '' );
	const [ debouncedSearch, setDebouncedSearch ] = useState( '' );
	const debounceTimerRef = useRef( 0 );

	const selectedId = productId > 0 ? productId : 0;

	const { selectedProduct, isResolvingSelected } = useSelect(
		( select ) => {
			if ( selectedId <= 0 ) {
				return {
					selectedProduct: null,
					isResolvingSelected: false,
				};
			}

			return {
				selectedProduct: select( coreStore ).getEntityRecord(
					'postType',
					'product',
					selectedId,
				),
				isResolvingSelected: select( coreStore ).isResolving(
					'getEntityRecord',
					[ 'postType', 'product', selectedId ],
				),
			};
		},
		[ selectedId ],
	);

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
			exclude: selectedId > 0 ? [ selectedId ] : [],
		};
	}, [ debouncedSearch, selectedId ] );

	const { searchProducts, isSearching } = useSelect(
		( select ) => {
			if ( ! searchQuery ) {
				return {
					searchProducts: [],
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
				searchProducts: records,
				isSearching: select( coreStore ).isResolving( 'getEntityRecords', [
					'postType',
					'product',
					searchQuery,
				] ),
			};
		},
		[ searchQuery ],
	);

	const options = useMemo( () => {
		const items = [
			{
				value: NONE_VALUE,
				label: __( 'All products', 'skaflow' ),
			},
		];

		if ( selectedProduct?.id ) {
			items.push( {
				value: String( selectedProduct.id ),
				label:
					selectedProduct.title?.rendered || `#${ selectedProduct.id }`,
			} );
		} else if ( selectedId > 0 && ! isResolvingSelected ) {
			items.push( {
				value: String( selectedId ),
				label: `#${ selectedId }`,
			} );
		}

		searchProducts.forEach( ( product ) => {
			if ( ! product?.id || product.id === selectedId ) {
				return;
			}

			items.push( {
				value: String( product.id ),
				label: product.title?.rendered || `#${ product.id }`,
			} );
		} );

		const seen = new Set();

		return items.filter( ( item ) => {
			if ( seen.has( item.value ) ) {
				return false;
			}

			seen.add( item.value );
			return true;
		} );
	}, [ isResolvingSelected, searchProducts, selectedId, selectedProduct ] );

	const handleChange = ( value ) => {
		const nextId = value && value !== NONE_VALUE ? parseInt( value, 10 ) : 0;

		onChange( Number.isFinite( nextId ) ? nextId : 0 );
		setSearch( '' );
		setDebouncedSearch( '' );
	};

	const showSearching =
		( isResolvingSelected && selectedId > 0 ) ||
		( isSearching && debouncedSearch.length >= MIN_SEARCH_LENGTH );

	return (
		<>
			<ComboboxControl
				label={ __( 'Filter by product', 'skaflow' ) }
				value={ selectedId > 0 ? String( selectedId ) : NONE_VALUE }
				options={ options }
				onChange={ handleChange }
				onFilterValueChange={ setSearch }
				allowReset={ false }
				help={ __(
					'Search and select a product, or choose All products.',
					'skaflow',
				) }
			/>
			{ showSearching && (
				<p className="sk-product-reviews-slider-editor__searching">
					<Spinner />
					{ __( 'Searching products…', 'skaflow' ) }
				</p>
			) }
		</>
	);
}
