<?php
/**
 * Filter panel sections (accordion layout).
 *
 * @package Happywood
 *
 * @var array<int, array<string, mixed>> $filter_groups Filter groups from happywood_get_archive_filter_groups().
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$filter_groups = $filter_groups ?? happywood_get_archive_filter_groups();

if ( empty( $filter_groups ) ) {
	return;
}

$desktop_orientation = happywood_get_filters_panel_desktop();
$mobile_orientation  = happywood_get_filters_panel_mobile();
?>
<div
	class="hw-filters-panel hw-filters-panel--<?php echo esc_attr( $desktop_orientation ); ?> hw-filters-panel--desktop-<?php echo esc_attr( $desktop_orientation ); ?> hw-filters-panel--mobile-<?php echo esc_attr( $mobile_orientation ); ?>"
	hidden
	aria-hidden="true"
	inert
>
	<div class="hw-filters-panel__backdrop" data-hw-filters-close hidden aria-hidden="true"></div>
	<div
		class="hw-filters-panel__inner"
		role="dialog"
		aria-modal="true"
		aria-labelledby="hw-filters-panel-title"
		tabindex="-1"
	>
		<div class="hw-filters-panel__header">
			<p class="hw-filters-panel__title" id="hw-filters-panel-title"><?php esc_html_e( 'Filters', 'happywood' ); ?></p>
			<div class="hw-filters-panel__header-actions">
				<button
					type="button"
					class="hw-filters-panel__accordion-toggle"
					data-hw-filters-accordion-toggle
					aria-expanded="false"
					aria-label="<?php esc_attr_e( 'Expand all filter sections', 'happywood' ); ?>"
				>
					<span class="hw-filters-panel__accordion-toggle-icon" aria-hidden="true"></span>
				</button>
				<button type="button" class="hw-filters-panel__close" data-hw-filters-close aria-label="<?php esc_attr_e( 'Close filters', 'happywood' ); ?>">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>

		<?php get_template_part( 'partials/product-filters', 'chips', array( 'context' => 'panel' ) ); ?>

		<div class="hw-filters-panel__sections hw-scrollbar">
			<?php foreach ( $filter_groups as $group ) : ?>
				<?php
				$slug       = (string) ( $group['slug'] ?? '' );
				$type       = (string) ( $group['type'] ?? 'attribute' );
				$label      = (string) ( $group['label'] ?? $slug );
				$is_colour  = 'attribute' === $type && happywood_is_colour_filter_attribute( $slug );
				$body_id    = 'hw-filter-accordion-body-' . $slug;
				$trigger_id = 'hw-filter-accordion-trigger-' . $slug;
				?>
				<section
					class="hw-filter-section hw-filter-section--<?php echo esc_attr( $type ); ?><?php echo $is_colour ? ' hw-filter-section--swatches' : ''; ?> hw-filter-accordion"
					data-filter-card="<?php echo esc_attr( $slug ); ?>"
					id="hw-filter-card-<?php echo esc_attr( $slug ); ?>"
				>
					<p class="hw-filter-section__heading">
						<button
							type="button"
							class="hw-filter-accordion__trigger"
							id="<?php echo esc_attr( $trigger_id ); ?>"
							aria-expanded="false"
							aria-controls="<?php echo esc_attr( $body_id ); ?>"
						>
							<span class="hw-filter-section__title"><?php echo esc_html( $label ); ?></span>
							<span class="hw-filter-accordion__icon" aria-hidden="true"></span>
						</button>
					</p>

					<div
						class="hw-filter-accordion__body"
						id="<?php echo esc_attr( $body_id ); ?>"
						role="region"
						aria-labelledby="<?php echo esc_attr( $trigger_id ); ?>"
						hidden
					>
						<?php if ( 'price' === $type ) : ?>
							<?php
							$min_bound = (float) ( $group['min_bound'] ?? 0 );
							$max_bound = (float) ( $group['max_bound'] ?? 0 );
							$min_value = (float) ( $group['min'] ?? $min_bound );
							$max_value = (float) ( $group['max'] ?? $max_bound );

							if ( $max_bound <= $min_bound ) {
								$max_bound = max( $min_bound + 1, 10000 );
							}

							if ( $min_value < $min_bound ) {
								$min_value = $min_bound;
							}

							if ( $max_value <= 0 || $max_value > $max_bound ) {
								$max_value = $max_bound;
							}
							?>
							<div
								class="hw-filter-price"
								data-min-bound="<?php echo esc_attr( (string) $min_bound ); ?>"
								data-max-bound="<?php echo esc_attr( (string) $max_bound ); ?>"
							>
								<div class="hw-filter-price__inputs">
									<label class="hw-filter-price__field">
										<span class="screen-reader-text"><?php esc_html_e( 'Minimum price', 'happywood' ); ?></span>
										<input
											type="number"
											class="hw-filter-price__input hw-filter-price__input--min"
											min="<?php echo esc_attr( (string) $min_bound ); ?>"
											max="<?php echo esc_attr( (string) $max_bound ); ?>"
											value="<?php echo esc_attr( (string) (int) $min_value ); ?>"
											step="1"
											inputmode="numeric"
										/>
									</label>
									<span class="hw-filter-price__separator" aria-hidden="true">&ndash;</span>
									<label class="hw-filter-price__field">
										<span class="screen-reader-text"><?php esc_html_e( 'Maximum price', 'happywood' ); ?></span>
										<input
											type="number"
											class="hw-filter-price__input hw-filter-price__input--max"
											min="<?php echo esc_attr( (string) $min_bound ); ?>"
											max="<?php echo esc_attr( (string) $max_bound ); ?>"
											value="<?php echo esc_attr( (string) (int) $max_value ); ?>"
											step="1"
											inputmode="numeric"
										/>
									</label>
								</div>
								<button type="button" class="hw-filter-price__apply">
									<?php esc_html_e( 'Apply', 'happywood' ); ?>
								</button>
							</div>
						<?php elseif ( $is_colour ) : ?>
							<div class="hw-filter-card__terms hw-filter-card__terms--swatches hw-scrollbar hw-swatches-grid hw-swatches--style-3 hw-swatches--dis-style-3 hw-swatches--size-m hw-swatches--shape-round">
								<?php foreach ( (array) ( $group['terms'] ?? array() ) as $term ) : ?>
									<?php
									$taxonomy    = (string) ( $group['taxonomy'] ?? '' );
									$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
									echo happywood_render_filter_swatch_term( $taxonomy, $term, $filter_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									?>
								<?php endforeach; ?>
							</div>
						<?php else : ?>
							<ul class="hw-filter-card__terms hw-scrollbar">
								<?php foreach ( (array) ( $group['terms'] ?? array() ) as $term ) : ?>
									<?php
									$term_slug   = (string) ( $term['slug'] ?? '' );
									$term_label  = (string) ( $term['label'] ?? $term_slug );
									$term_count  = (int) ( $term['count'] ?? 0 );
									$is_chosen   = ! empty( $term['chosen'] );
									$taxonomy    = (string) ( $group['taxonomy'] ?? '' );
									$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $taxonomy );
									?>
									<li class="hw-filter-card__term">
										<button
											type="button"
											class="hw-filter-term<?php echo $is_chosen ? ' is-active' : ''; ?>"
											data-filter-name="<?php echo esc_attr( $filter_name ); ?>"
											data-term-slug="<?php echo esc_attr( $term_slug ); ?>"
											data-toggle-url="<?php echo esc_url( (string) ( $term['toggle_url'] ?? '' ) ); ?>"
											aria-pressed="<?php echo $is_chosen ? 'true' : 'false'; ?>"
										>
											<span class="hw-filter-term__label"><?php echo esc_html( $term_label ); ?></span>
											<span class="hw-filter-term__count"><?php echo esc_html( number_format_i18n( $term_count ) ); ?></span>
										</button>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	</div>
</div>
