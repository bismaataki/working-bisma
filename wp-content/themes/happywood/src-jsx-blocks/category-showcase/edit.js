import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner } from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    const {
        categorySlug = '',
        categoryId = 0,
        productsPerPage = 4,
    } = attributes;

    const { records: categories, isResolving } = useEntityRecords(
        'taxonomy',
        'product_cat',
        {
            per_page: 100,
            hide_empty: false,
            orderby: 'name',
            order: 'asc',
        }
    );

    // Preserve old ID-based selections while migrating to slugs.
    const legacyCategory = (categories || []).find(
        (category) => category.id === Number(categoryId)
    );

    const selectedCategorySlug =
        categorySlug || legacyCategory?.slug || 'computer-table';

    const options = [
        ...((categories || []).map((category) => ({
            label: category.name,
            value: category.slug,
        }))),
    ];

    const blockProps = useBlockProps({
        className: 'hw-category-showcase-editor',
    });

    return (
        <>
            <InspectorControls>
                <PanelBody title={__('Category Settings', 'happywood')}>
                    <SelectControl
                        label={__('Product Category', 'happywood')}
                        value={selectedCategorySlug}
                        options={options}
                        onChange={(value) =>
                            setAttributes({
                                categorySlug: value,
                                categoryId: 0,
                            })
                        }
                        disabled={isResolving}
                    />

                    <SelectControl
                        label={__('Products to show', 'happywood')}
                        value={productsPerPage}
                        options={[
                            { label: '2', value: 2 },
                            { label: '3', value: 3 },
                            { label: '4', value: 4 },
                            { label: '6', value: 6 },
                        ]}
                        onChange={(value) =>
                            setAttributes({ productsPerPage: Number(value) })
                        }
                    />
                </PanelBody>
            </InspectorControls>

            <div {...blockProps}>
                {isResolving ? (
                    <Placeholder
                        icon="grid-view"
                        label={__('Category Showcase', 'happywood')}
                    >
                        <Spinner />
                    </Placeholder>
                ) : (
                    <ServerSideRender
                        block="happywood/category-showcase"
                        attributes={{
                            categorySlug: selectedCategorySlug,
                            productsPerPage,
                        }}
                    />
                )}
            </div>
        </>
    );
}
