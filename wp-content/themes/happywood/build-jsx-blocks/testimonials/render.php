<?php
/**
 * Testimonials block frontend render.
 *
 * Fetches published testimonials from the Happywood Review CPT.
 *
 * @package Happywood
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'get_field' ) ) {
	return;
}

/**
 * Normalize an ACF image value into an attachment URL.
 *
 * @param mixed $image ACF image field value.
 * @return string
 */
$get_testimonial_image_url = static function ( $image ) {
	if ( is_array( $image ) ) {
		if ( ! empty( $image['ID'] ) ) {
			return wp_get_attachment_image_url( (int) $image['ID'], 'full' ) ?: '';
		}

		if ( ! empty( $image['url'] ) ) {
			return $image['url'];
		}
	}

	if ( is_numeric( $image ) ) {
		return wp_get_attachment_image_url( (int) $image, 'full' ) ?: '';
	}

	return '';
};

/**
 * Fetch published testimonials.
 */
$testimonial_query = new WP_Query(
	array(
		'post_type'              => 'happywood_review',
		'post_status'            => 'publish',
		'posts_per_page'         => -1,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => false,
	)
);

if ( ! $testimonial_query->have_posts() ) {
	return;
}

$testimonials = array();

while ( $testimonial_query->have_posts() ) {
	$testimonial_query->the_post();

	$post_id = get_the_ID();

	$name        = get_field( 'customer_name', $post_id );
	$designation = get_field( 'designation', $post_id );
	$review      = get_field( 'review', $post_id );
	$avatar           = get_field( 'customer_avatar', $post_id );
	$testimonial_image = get_field( 'testimonial_image', $post_id );

	$avatar_url = $get_testimonial_image_url( $avatar );
	$image_url  = $get_testimonial_image_url( $testimonial_image );

	// Use the theme placeholder when no testimonial image is selected.
	if ( empty( $image_url ) ) {
		$image_url = get_theme_file_uri( 'assets/images/placeholder-16x9.png' );
	}

	// Skip incomplete testimonials.
	if ( empty( $name ) || empty( $review ) ) {
		continue;
	}

	$testimonials[] = array(
		'name'        => $name,
		'designation' => $designation,
		'review'      => $review,
		'avatar_url'  => $avatar_url,
		'image_url'   => $image_url,
	);
}

wp_reset_postdata();

if ( empty( $testimonials ) ) {
	return;
}

$total = count( $testimonials );
?>

<section
	<?php echo get_block_wrapper_attributes( array( 'class' => 'hw-testimonials' ) ); ?>
	data-testimonials-slider
>

	<h2 class="hw-testimonials__heading">Testimonials</h2>

	<button
		type="button"
		class="hw-testimonials__arrow hw-testimonials__arrow--prev"
		data-testimonial-prev
		aria-label="<?php esc_attr_e( 'Previous testimonial', 'happywood' ); ?>"
	>
		<span aria-hidden="true">&larr;</span>
	</button>

	<div class="hw-testimonials__slides">

		<?php foreach ( $testimonials as $index => $testimonial ) : ?>

			<div
				class="hw-testimonials__slide <?php echo 0 === $index ? 'is-active' : ''; ?>"
				data-testimonial-slide="<?php echo esc_attr( $index ); ?>"
				<?php echo 0 !== $index ? 'hidden' : ''; ?>
			>
				<div class="hw-testimonials__layout <?php echo $testimonial['image_url'] ? '' : 'hw-testimonials__layout--no-image'; ?>">

					<div class="hw-testimonials__content">

						<?php if ( $testimonial['avatar_url'] ) : ?>
							<div class="hw-testimonials__avatar">
								<img
									src="<?php echo esc_url( $testimonial['avatar_url'] ); ?>"
									alt="<?php echo esc_attr( $testimonial['name'] ); ?>"
									loading="lazy"
								/>
							</div>
						<?php endif; ?>

						<div class="hw-testimonials__review">
							<?php echo wp_kses_post( $testimonial['review'] ); ?>
						</div>

						<h3 class="hw-testimonials__name">
							<?php echo esc_html( $testimonial['name'] ); ?>
						</h3>

						<?php if ( $testimonial['designation'] ) : ?>
							<p class="hw-testimonials__designation">
								<?php echo esc_html( $testimonial['designation'] ); ?>
							</p>
						<?php endif; ?>

						<div class="hw-testimonials__dots">
							<?php for ( $dot = 0; $dot < $total; $dot++ ) : ?>
								<button
									type="button"
									class="hw-testimonials__dot <?php echo $dot === $index ? 'is-active' : ''; ?>"
									data-testimonial-to="<?php echo esc_attr( $dot ); ?>"
									aria-label="<?php echo esc_attr( sprintf( 'Go to testimonial %d', $dot + 1 ) ); ?>"
									aria-current="<?php echo $dot === $index ? 'true' : 'false'; ?>"
								></button>
							<?php endfor; ?>
						</div>

					</div>

					<?php if ( $testimonial['image_url'] ) : ?>
						<div class="hw-testimonials__image">
							<img
								src="<?php echo esc_url( $testimonial['image_url'] ); ?>"
								alt="<?php echo esc_attr( $testimonial['name'] ); ?>"
								loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>"
							/>
						</div>
					<?php endif; ?>

				</div>
			</div>

		<?php endforeach; ?>

	</div>

	<button
		type="button"
		class="hw-testimonials__arrow hw-testimonials__arrow--next"
		data-testimonial-next
		aria-label="<?php esc_attr_e( 'Next testimonial', 'happywood' ); ?>"
	>
		<span aria-hidden="true">&rarr;</span>
	</button>

</section>
