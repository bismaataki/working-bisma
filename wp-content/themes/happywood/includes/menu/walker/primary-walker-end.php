<?php
/**
 * Closes submenu / mega menu containers.
 *
 * @package Happywood
 */

$args    = $args ?? array();
$depth   = isset( $args['depth'] ) ? (int) $args['depth'] : 0;
$context = $args['context'] ?? 'desktop-nav';
?>
</ul>
<?php if ( 0 === $depth && 'desktop-nav' === $context ) : ?>
		</div>
	</div>
<?php endif; ?>
