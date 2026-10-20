/**
 * @param {{ id?: number, title?: string } | null | undefined} product
 * @return {product is { id: number, title: string }} Whether the value is a selected product object.
 */
export function isValidSelectedProduct( product ) {
	return (
		Boolean( product ) &&
		typeof product.id === 'number' &&
		product.id > 0 &&
		typeof product.title === 'string' &&
		product.title.length > 0
	);
}
