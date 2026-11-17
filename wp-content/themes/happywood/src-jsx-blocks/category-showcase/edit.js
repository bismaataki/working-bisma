import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, SelectControl, Placeholder, Spinner } from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import ServerSideRender from '@wordpress/server-side-render';

export default function Edit({ attributes, setAttributes }) {
    const { categoryId = 0, productsPerPage = 4 } = attributes;

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

    const options = [
        { label: __('Select a category', 'happywood'), value: 0 },
        ...(categories || []).map((category) => ({
            label: category.name,
            value: category.id,
        })),
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
                        value={categoryId}
                        options={options}
                        onChange={(value) =>
                            setAttributes({ categoryId: Number(value) })
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
                {categoryId > 0 ? (
                    <ServerSideRender
                        block="happywood/category-showcase"
                        attributes={{
                            categoryId,
                            productsPerPage,
                            previewMode: true,
                        }}
                    />
                ) : (
                    <Placeholder
                        icon="grid-view"
                        label={__('Category Showcase', 'happywood')}
                        instructions={__(
                            'Select a product category from the block settings.',
                            'happywood'
                        )}
                    >
                        {isResolving && <Spinner />}
                    </Placeholder>
                )}
            </div>
        </>
    );
}
