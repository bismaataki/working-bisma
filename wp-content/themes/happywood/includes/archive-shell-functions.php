<?php
/**
 * Product archive shell helpers.
 *
 * Filter/sort refresh fetches the full catalog page in JS and swaps
 * `.hw-shop-archive-shell` from the response so plugin hooks (e.g. YITH)
 * run through the normal front-end request lifecycle.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
