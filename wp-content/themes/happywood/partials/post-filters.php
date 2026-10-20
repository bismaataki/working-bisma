<?php
/**
 * Blog listing filters partial.
 *
 * @package Happywood
 *
 * @var array<string, mixed> $args Template part args.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$attributes = ( isset( $args ) && is_array( $args ) && ! empty( $args['attributes'] ) && is_array( $args['attributes'] ) )
	? $args['attributes']
	: array();

$show_search            = ! empty( $attributes['showSearch'] );
$show_category_dropdown = ! empty( $attributes['showCategoryDropdown'] );
$show_sort              = ! empty( $attributes['showSort'] );
$show_pills             = ! empty( $attributes['showPills'] );
$show_count             = ! empty( $attributes['showCount'] );

$state      = happywood_get_blog_listing_filter_state();
$categories = happywood_get_blog_listing_filter_categories();
$sort_opts  = happywood_get_blog_listing_sort_options();
$base_url   = happywood_get_blog_listing_base_url();
$count      = happywood_get_blog_listing_results_count();

$active_category = $state['category_name'];
$active_sort_key = $state['sort_key'];
$search_value    = $state['s'];

$toolbar_visible = $show_search || $show_category_dropdown || $show_sort || $show_count;
?>
<?php if ( $toolbar_visible ) : ?>
	<div class="hw-post-filters__toolbar">
		<?php if ( $show_search ) : ?>
			<form
				class="hw-post-filters__search"
				method="get"
				action="<?php echo esc_url( $base_url ); ?>"
				data-hw-post-filters-clear-url="<?php echo esc_url( happywood_build_blog_listing_url( array( 's' => '' ) ) ); ?>"
			>
				<div class="hw-post-filters__search-field">
					<span class="hw-post-filters__search-icon" aria-hidden="true"></span>
					<label class="screen-reader-text" for="hw-post-filters-search">
						<?php esc_html_e( 'Search articles', 'happywood' ); ?>
					</label>
					<input
						id="hw-post-filters-search"
						class="hw-post-filters__search-input"
						type="search"
						name="s"
						value="<?php echo esc_attr( $search_value ); ?>"
						placeholder="<?php esc_attr_e( 'Search articles...', 'happywood' ); ?>"
					/>
				</div>
				<?php if ( '' !== $active_category ) : ?>
					<input type="hidden" name="category_name" value="<?php echo esc_attr( $active_category ); ?>" />
				<?php endif; ?>
				<?php if ( 'date-desc' !== $active_sort_key && isset( $sort_opts[ $active_sort_key ] ) ) : ?>
					<input type="hidden" name="orderby" value="<?php echo esc_attr( $sort_opts[ $active_sort_key ]['orderby'] ); ?>" />
					<input type="hidden" name="order" value="<?php echo esc_attr( $sort_opts[ $active_sort_key ]['order'] ); ?>" />
				<?php endif; ?>
			</form>
		<?php endif; ?>

		<?php if ( $show_category_dropdown || $show_sort || $show_count ) : ?>
			<div class="hw-post-filters__controls">
				<?php if ( $show_category_dropdown ) : ?>
					<div class="hw-post-filters__control">
						<label class="hw-post-filters__control-label" for="hw-post-filters-category">
							<?php esc_html_e( 'Category', 'happywood' ); ?>
						</label>
						<select
							id="hw-post-filters-category"
							class="hw-post-filters__select"
							data-hw-post-filters-url
						>
							<option value="<?php echo esc_url( happywood_build_blog_listing_url( array( 'category_name' => '' ) ) ); ?>" <?php selected( '', $active_category ); ?>>
								<?php esc_html_e( 'All', 'happywood' ); ?>
							</option>
							<?php foreach ( $categories as $term ) : ?>
								<option
									value="<?php echo esc_url( happywood_build_blog_listing_url( array( 'category_name' => $term->slug ) ) ); ?>"
									<?php selected( $active_category, $term->slug ); ?>
								>
									<?php echo esc_html( $term->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( $show_sort ) : ?>
					<div class="hw-post-filters__control">
						<label class="hw-post-filters__control-label" for="hw-post-filters-sort">
							<?php esc_html_e( 'Sort', 'happywood' ); ?>
						</label>
						<select
							id="hw-post-filters-sort"
							class="hw-post-filters__select"
							data-hw-post-filters-url
						>
							<?php foreach ( $sort_opts as $sort_key => $sort_option ) : ?>
								<option
									value="
									<?php
									echo esc_url(
										happywood_build_blog_listing_url(
											array(
												'sort_key' => $sort_key,
											)
										)
									);
									?>
									"
									<?php selected( $active_sort_key, $sort_key ); ?>
								>
									<?php echo esc_html( $sort_option['label'] ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

				<?php if ( $show_count ) : ?>
					<p class="hw-post-filters__count hw-post-filters__control-label">
						<?php
						printf(
							/* translators: %s: number of articles */
							esc_html( _n( '%s Article', '%s Articles', $count, 'happywood' ) ),
							esc_html( number_format_i18n( $count ) )
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ( $show_pills && ! empty( $categories ) ) : ?>
	<div class="hw-post-filters__pills" role="group" aria-label="<?php esc_attr_e( 'Filter by category', 'happywood' ); ?>">
		<button
			type="button"
			class="hw-post-filters__pill<?php echo '' === $active_category ? ' is-active' : ''; ?>"
			data-hw-post-filters-url="<?php echo esc_url( happywood_build_blog_listing_url( array( 'category_name' => '' ) ) ); ?>"
			aria-pressed="<?php echo '' === $active_category ? 'true' : 'false'; ?>"
		>
			<?php esc_html_e( 'All', 'happywood' ); ?>
		</button>
		<?php foreach ( $categories as $term ) : ?>
			<button
				type="button"
				class="hw-post-filters__pill<?php echo $active_category === $term->slug ? ' is-active' : ''; ?>"
				data-hw-post-filters-url="<?php echo esc_url( happywood_build_blog_listing_url( array( 'category_name' => $term->slug ) ) ); ?>"
				aria-pressed="<?php echo $active_category === $term->slug ? 'true' : 'false'; ?>"
			>
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
