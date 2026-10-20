import { __ } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { PanelBody, Placeholder, SelectControl } from '@wordpress/components';
import ServerSideRender from '@wordpress/server-side-render';

import FaqCategoryTermPicker from './term-picker';
import {
	getFaqGroupsOrderByOptions,
	getFaqGroupsOrderOptions,
	isValidSelectedTerm,
} from './term-utils';
import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const {
		terms = [],
		orderBy = 'manual',
		order = 'asc',
		accordionTitleTag = 'h3',
	} = attributes;

	const selectedTerms = Array.isArray( terms )
		? terms.filter( isValidSelectedTerm )
		: [];

	const blockProps = useBlockProps( {
		className: 'sk-faq-groups-editor',
	} );

	const previewAttributes = {
		terms: selectedTerms,
		orderBy,
		order,
		accordionTitleTag,
		previewMode: true,
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'FAQ Categories', 'skaflow' ) }
					initialOpen={ true }
				>
					<FaqCategoryTermPicker
						terms={ selectedTerms }
						onChange={ ( nextTerms ) =>
							setAttributes( { terms: nextTerms } )
						}
					/>
					<SelectControl
						label={ __( 'Order by', 'skaflow' ) }
						value={ orderBy }
						options={ getFaqGroupsOrderByOptions() }
						onChange={ ( value ) =>
							setAttributes( { orderBy: value || 'manual' } )
						}
						help={ __(
							'As selected preserves the token order above. Other options use WordPress term query ordering.',
							'skaflow',
						) }
					/>
					{ orderBy !== 'manual' && (
						<SelectControl
							label={ __( 'Order', 'skaflow' ) }
							value={ order }
							options={ getFaqGroupsOrderOptions() }
							onChange={ ( value ) =>
								setAttributes( { order: value || 'asc' } )
							}
						/>
					) }
				</PanelBody>
				<PanelBody
					title={ __( 'Accordion', 'skaflow' ) }
					initialOpen={ false }
				>
					<SelectControl
						label={ __( 'Question title tag', 'skaflow' ) }
						value={ accordionTitleTag }
						options={ [
							{
								label: __( 'Heading 3 (h3)', 'skaflow' ),
								value: 'h3',
							},
							{
								label: __( 'Span (span)', 'skaflow' ),
								value: 'span',
							},
						] }
						onChange={ ( value ) =>
							setAttributes( {
								accordionTitleTag: value || 'h3',
							} )
						}
						help={ __(
							'HTML tag for each FAQ question in the accordion. Use h3 for proper heading hierarchy on the FAQ page.',
							'skaflow',
						) }
					/>
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ selectedTerms.length > 0 ? (
					<ServerSideRender
						block="skaflow/faq-groups"
						attributes={ previewAttributes }
					/>
				) : (
					<Placeholder
						icon="editor-help"
						label={ __( 'FAQ Groups', 'skaflow' ) }
						instructions={ __(
							'Select FAQ categories in the block sidebar to build grouped accordions.',
							'skaflow',
						) }
					>
						<p className="sk-faq-groups-editor__hint">
							{ __(
								'Use the FAQ Categories panel to search and add terms.',
								'skaflow',
							) }
						</p>
					</Placeholder>
				) }
			</div>
		</>
	);
}
