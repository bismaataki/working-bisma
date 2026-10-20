<?php
/**
 * Title: Showroom Gallery
 * Slug: skaflow/showroom-gallery
 * Categories: skaflow, section
 * Description: Lightbox gallery generated from the showroom ACF gallery field.
 * Post Types: showrooms
 * Keywords: showroom, gallery, lightbox, photos
 */
echo skaflow_get_showroom_gallery_pattern_markup( (int) get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
