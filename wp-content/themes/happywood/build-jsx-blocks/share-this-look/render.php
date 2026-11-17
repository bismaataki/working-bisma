
<?php
/**
 * Server-side output for the Share This Look block.
 *
 * @var array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'is_product' ) || ! is_product() ) {
	return;
}

$product_id = get_queried_object_id();
$product    = wc_get_product( $product_id );

if ( ! $product ) {
	return;
}

$product_url   = get_permalink( $product_id );
$product_title = $product->get_name();

if ( ! $product_url || '' === $product_title ) {
	return;
}

$share_url  = rawurlencode( $product_url );
$share_text = rawurlencode( 'Check out ' . $product_title . '!' );

$wrapper_attributes = get_block_wrapper_attributes(
	array(
		'class' => 'hw-share-this-look',
	)
);
?>

<div <?php echo $wrapper_attributes; ?>>
	<p class="hw-share-this-look-heading">
		<?php esc_html_e( 'Share This Look', 'happywood' ); ?>
	</p>

	<div class="hw-share-this-look-buttons">
		<a
			class="hw-share-button hw-share-whatsapp"
			href="<?php echo esc_url( 'https://wa.me/?text=' . $share_text . '%20' . $share_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			aria-label="<?php esc_attr_e( 'Share product on WhatsApp', 'happywood' ); ?>"
		>
			<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
				<path d="M12 2a10 10 0 0 0-8.5 15.26L2 22l4.9-1.28A10 10 0 1 0 12 2Zm0 18a8 8 0 0 1-4.08-1.12l-.3-.18-3.2.84.86-3.12-.2-.32A8 8 0 1 1 12 20Zm4.4-5.97c-.24-.12-1.42-.7-1.64-.78-.22-.08-.38-.12-.54.12-.16.24-.62.78-.76.94-.14.16-.28.18-.52.06-.24-.12-1.02-.38-1.94-1.2-.72-.64-1.2-1.43-1.34-1.67-.14-.24-.02-.37.1-.49.1-.1.24-.28.36-.42.12-.14.16-.24.24-.4.08-.16.04-.3-.02-.42-.06-.12-.54-1.3-.74-1.78-.2-.46-.4-.4-.54-.4h-.46c-.16 0-.42.06-.64.3-.22.24-.84.82-.84 2s.86 2.32.98 2.48c.12.16 1.7 2.6 4.12 3.64.58.25 1.03.4 1.38.51.58.18 1.1.16 1.51.1.46-.07 1.42-.58 1.62-1.14.2-.56.2-1.04.14-1.14-.06-.1-.22-.16-.46-.28Z"/>
			</svg>
		</a>

		<a
			class="hw-share-button hw-share-facebook"
			href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . $share_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
			aria-label="<?php esc_attr_e( 'Share product on Facebook', 'happywood' ); ?>"
		>
			<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
				<path d="M13.5 21v-8.2h2.76l.41-3.2H13.5V7.56c0-.93.26-1.56 1.6-1.56h1.7V3.14A22 22 0 0 0 14.32 3c-2.46 0-4.14 1.5-4.14 4.26V9.6H7.4v3.2h2.78V21h3.32Z"/>
			</svg>
		</a>

		<button
			class="hw-share-button hw-share-copy"
			type="button"
			data-copy-url="<?php echo esc_url( $product_url ); ?>"
			aria-label="<?php esc_attr_e( 'Copy product link', 'happywood' ); ?>"
		>
			<svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8">
				<rect x="8" y="8" width="12" height="12" rx="1"/>
				<path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3"/>
			</svg>
		</button>

		<span class="hw-share-copy-status" aria-live="polite"></span>
	</div>
</div>
