<?php
/**
 * Build an Articles section of the Primary Nav from posts already in the
 * database.
 *
 * This is a bootstrap, not a synchroniser. It builds a sensible starting menu
 * once, then hands ownership to the editor in Appearance > Menus. It does not
 * try to keep the nav in step with the post archive, and re-running it will
 * not overwrite hand edits.
 *
 * What it produces per eligible post category, filling a four-column panel:
 *
 *   [category]                       grid_columns=4
 *     LATEST          span 2, children in 2   recent posts, thumbnails
 *     MORE ARTICLES   1 column                older posts, plain text links
 *     IN THIS TOPIC   1 column                child categories, plain text
 *     (or OTHER TOPICS, when the category is flat)
 *
 * The three groups are deliberate. A single heading would only ever fill half
 * the panel, and the point of seeding is to hand over something that shows
 * what the mega menu can do: two heading widths, two link types, and a mix of
 * posts and terms. Editors then cut it down, not build it up.
 *
 * Nothing here is keyed on a term ID, post ID, or slug. Everything is
 * discovered from the install at run time, because the same script has to
 * survive a fresh install with no posts, a dummy-data install, and a clone of
 * a client's live site, and no baked-in identifier survives all three. Every
 * group is skipped rather than left empty when its source turns up nothing.
 *
 * Ordering is by date, which is a starting point rather than an editorial
 * judgement. Reordering in Appearance > Menus is the expected next step.
 *
 * @package Happywood
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post categories never worth putting in a nav.
 *
 * Mirrors happywood_menu_setup_excluded_product_cats(). "Uncategorized" is where
 * posts land when nobody chose, so it is a dumping ground rather than a
 * section, and on a fresh install it is the only category that exists.
 *
 * @return int[]
 */
function happywood_menu_setup_excluded_categories(): array {
	return array_values( array_filter( array( (int) get_option( 'default_category', 0 ) ) ) );
}

/**
 * Categories eligible for the Articles menu, most populated first.
 *
 * @param int $max_top_level Maximum categories to return.
 * @return WP_Term[]
 */
function happywood_menu_setup_article_categories( int $max_top_level = 4 ): array {
	if ( ! taxonomy_exists( 'category' ) ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'count',
			'order'      => 'DESC',
			'number'     => max( 1, $max_top_level ),
			'exclude'    => happywood_menu_setup_excluded_categories(),
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * Most recent published posts in a category.
 *
 * @param int $term_id   Category term ID.
 * @param int $max_posts Maximum posts to return.
 * @return WP_Post[]
 */
function happywood_menu_setup_article_posts( int $term_id, int $max_posts = 8 ): array {
	return get_posts(
		array(
			'post_type'        => 'post',
			'post_status'      => 'publish',
			'numberposts'      => max( 1, $max_posts ),
			'orderby'          => 'date',
			'order'            => 'DESC',
			'category'         => $term_id,
			'suppress_filters' => false,
		)
	);
}

/**
 * Terms for the panel's third column, with the heading that suits them.
 *
 * Child categories when the taxonomy is hierarchical, which is the more useful
 * answer. Post categories are commonly flat, so the fallback is the other
 * top-level categories, letting a reader jump sideways between topics.
 *
 * @param int       $term_id  Category term ID.
 * @param WP_Term[] $siblings Other eligible top-level categories.
 * @param int       $max      Maximum terms to return.
 * @return array{label: string, terms: WP_Term[]}
 */
function happywood_menu_setup_article_topics( int $term_id, array $siblings, int $max = 4 ): array {
	$children = get_terms(
		array(
			'taxonomy'   => 'category',
			'parent'     => $term_id,
			'hide_empty' => true,
			'number'     => max( 1, $max ),
			'exclude'    => happywood_menu_setup_excluded_categories(),
		)
	);

	if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
		return array(
			'label' => __( 'IN THIS TOPIC', 'happywood' ),
			'terms' => $children,
		);
	}

	$others = array();

	foreach ( $siblings as $sibling ) {
		if ( (int) $sibling->term_id !== $term_id ) {
			$others[] = $sibling;
		}
	}

	return array(
		'label' => __( 'OTHER TOPICS', 'happywood' ),
		'terms' => array_slice( $others, 0, max( 1, $max ) ),
	);
}

/**
 * Report what could be derived, without changing anything.
 *
 * @param int $max_top_level Maximum categories considered.
 * @return array<string, int>
 */
function happywood_menu_setup_survey_articles( int $max_top_level = 4 ): array {
	$categories = happywood_menu_setup_article_categories( $max_top_level );
	$posts      = 0;

	foreach ( $categories as $term ) {
		$posts += (int) $term->count;
	}

	return array(
		'categories'      => count( $categories ),
		'posts_in_scope'  => $posts,
		'posts_published' => (int) wp_count_posts( 'post' )->publish,
	);
}

/**
 * Build one category's mega-menu panel.
 *
 * Split out because it is the shape worth reading on its own: three groups,
 * two heading widths, two link types.
 *
 * @param int       $menu_id   Nav menu term ID.
 * @param int       $parent_id Top-level menu item ID.
 * @param WP_Post[] $featured  Posts for the thumbnail group.
 * @param WP_Post[] $more      Posts for the plain-text group.
 * @param array     $topics    Third-column terms, as returned by
 *                             happywood_menu_setup_article_topics().
 */
function happywood_menu_setup_build_article_panel(
	int $menu_id,
	int $parent_id,
	array $featured,
	array $more,
	array $topics
): void {

	// Two grid columns wide, children laid out in two, which is how a longer
	// list stays readable inside a four-column panel.
	$latest_id = happywood_menu_setup_add_heading(
		$menu_id,
		$parent_id,
		__( 'LATEST', 'happywood' ),
		array(
			'column_span'   => '2',
			'child_columns' => '2',
		)
	);

	$items = array();

	foreach ( $featured as $post ) {
		$items[] = array( (int) $post->ID, get_the_title( $post ) );
	}

	happywood_menu_setup_add_post_thumbnail_items( $menu_id, $latest_id, $items );

	// A heading with no field overrides takes a single column.
	if ( ! empty( $more ) ) {
		$more_id = happywood_menu_setup_add_heading( $menu_id, $parent_id, __( 'MORE ARTICLES', 'happywood' ) );

		foreach ( $more as $post ) {
			happywood_menu_setup_add_post(
				$menu_id,
				$more_id,
				(int) $post->ID,
				get_the_title( $post ),
				array( 'link_type' => 'default' )
			);
		}
	}

	if ( ! empty( $topics['terms'] ) ) {
		$topics_id = happywood_menu_setup_add_heading( $menu_id, $parent_id, $topics['label'] );

		foreach ( $topics['terms'] as $topic ) {
			happywood_menu_setup_add_term(
				$menu_id,
				$topics_id,
				(int) $topic->term_id,
				$topic->name,
				array( 'link_type' => 'default' ),
				'category'
			);
		}
	}
}

/**
 * Build the Articles sections into an existing Primary Nav menu.
 *
 * @param int $primary_menu_id Nav menu term ID.
 * @param int $max_top_level   Maximum top-level categories.
 * @param int $per_group       Maximum items in each of the panel's groups.
 * @return int Number of top-level items created.
 */
function happywood_menu_setup_build_articles(
	int $primary_menu_id,
	int $max_top_level = 4,
	int $per_group = 4
): int {
	$categories = happywood_menu_setup_article_categories( $max_top_level );

	if ( empty( $categories ) ) {
		happywood_menu_setup_log(
			'No post categories with published posts, ignoring the default category. Nothing to build.'
		);

		return 0;
	}

	$created = 0;

	foreach ( $categories as $term ) {
		$posts = happywood_menu_setup_article_posts( (int) $term->term_id, $per_group * 2 );

		// A category can report a count while holding nothing publishable, for
		// instance when its only posts are drafts. Skip rather than create a
		// top-level item that opens an empty panel.
		if ( empty( $posts ) ) {
			happywood_menu_setup_log(
				sprintf( 'Skipping %s: no published posts to list.', $term->name )
			);

			continue;
		}

		$featured = array_slice( $posts, 0, $per_group );
		$more     = array_slice( $posts, $per_group );
		$topics   = happywood_menu_setup_article_topics( (int) $term->term_id, $categories, $per_group );

		// Asked before building, because the builder returns the same ID
		// whether it created the item or skipped one already present.
		$already_present = (bool) happywood_menu_setup_find_top_level_term(
			$primary_menu_id,
			(int) $term->term_id,
			'category'
		);

		happywood_menu_setup_build_top_level_term(
			$primary_menu_id,
			(int) $term->term_id,
			$term->name,
			array(
				'grid_columns' => '4',
				/* translators: %s: post category name. */
				'label_mobile' => sprintf( __( 'Explore %s', 'happywood' ), $term->name ),
			),
			function ( int $menu_id, int $parent_id ) use ( $featured, $more, $topics ): void {
				happywood_menu_setup_build_article_panel( $menu_id, $parent_id, $featured, $more, $topics );
			},
			'category'
		);

		if ( ! $already_present ) {
			++$created;
		}
	}

	return $created;
}

/**
 * Resolve the Primary Nav menu by location, then build the Articles sections.
 *
 * @param int $max_top_level Maximum top-level categories.
 * @param int $per_group     Maximum items in each of the panel's groups.
 * @return array{primary_menu_id: int, created: int}
 */
function happywood_menu_setup_run_articles( int $max_top_level = 4, int $per_group = 4 ): array {
	$locations = happywood_menu_setup_locations();

	$primary_menu_id = happywood_menu_setup_get_or_create_for_location( $locations['primary'], 'Primary Nav' );

	$created = happywood_menu_setup_build_articles( $primary_menu_id, $max_top_level, $per_group );

	return array(
		'primary_menu_id' => $primary_menu_id,
		'created'         => $created,
	);
}
