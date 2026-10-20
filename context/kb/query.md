# Query contracts — related posts and blog listing

Derived from shipped code. Promoted from investigation notes on 2026-09-07.

## Related posts (`core/query`)

Do not add a new block or a Query Loop variation. Extend `core/query`.

| Layer | Where |
|---|---|
| Editor controls | `src/blocks-extension/extend-core-query/query-related-posts.js` |
| Frontend query | `includes/post/post-archive-hooks.php` (`skaflow_post_query_loop_block_query_vars`) |
| Example markup | `patterns/section-related-posts.php` |

Extra keys live on the existing `query` attribute:

- `isRelated` — when true, PHP adds a `tax_query` from the current post’s terms
- `relatedTaxonomy` — which taxonomy (default `category`)
- `relatedFallbackToDefault` — if the post has no terms, keep the default query

Hook: `query_loop_block_query_vars`. If `isRelated` is false, return the query
untouched. If true: current post → terms in `relatedTaxonomy` → `tax_query` →
exclude current post.

That hook is **frontend only**. The editor preview uses REST and will not
show true related results unless REST is extended later. That is accepted.

Card inside the loop: `skaflow/post-card`.

## Blog listing URL and query vars

The posts page (`page_for_posts`) is the blog index (`is_home()`). On this
starter the intended slug is `/blogs/`. Confirm with Reading settings; do
not hard-code a page ID.

`templates/index.html` uses `core/query` with `inherit: true`, so public
query vars on that URL filter the main query. No `pre_get_posts` is required
for `cat`, `s`, `orderby`, or `order`.

| URL | `is_home()` | `is_category()` | Notes |
|---|---|---|---|
| `/blogs/` | true | false | All posts |
| `/blogs/?cat=…` or `?category_name=…` | true | false | Still the posts page, still `index.html` |
| `/blogs/?s=term` | true | false | Blog-scoped search. Not `is_search()` |
| `/?s=term` | false | false | Site search. Commerce scopes this to products (`includes/woocommerce/search-hooks.php`) |
| `/category/style/` | false | true | Native archive. `archive.html`. Do not use for the listing toolbar |

Toolbar filters stay on the posts page permalink (`add_query_arg` on
`get_permalink( get_option( 'page_for_posts' ) )`). `get_term_link()` leaves
`/blogs/` and is the wrong shape for that toolbar.

Blog search must POST or link `?s=` **on the posts page URL**, not the
header store search.

Filter UI: `includes/post/post-filters-functions.php`,
`src-jsx-blocks/post-filters/`.

## Related

- `context/kb/map-edit.md` — start files
- `src/blocks-extension/README.md` — other core-block extensions
