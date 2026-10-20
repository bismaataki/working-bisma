# Product grid

How product cards compose on archives, search, upsells, and related.
Start files: `context/kb/map-edit.md`. Strip surface: `context/kb/plugin-woocommerce-surface.md`.

## Canonical card

Markup comes from **`skaflow_get_product_card_blocks_markup()`**
(`includes/woocommerce/product-card-functions.php`), rendered by the locked
**`skaflow/product-card`** block (`.wp-block-skaflow-product-card`).

Do **not** re-register `parts/product-card.html`. That template part was
removed. FSE uses theme files only.

| FSE consumer | Role |
|---|---|
| `parts/product-collection.html` | Shop / category archive. `inherit: true` + Load More |
| `templates/product-search-results.html` | Product search |

`templates/archive-product.html` pulls in `product-collection`. Upsells and
related products are **inline in `templates/single-product.html`**, not template
parts. Page content that uses Product Collection should insert
`skaflow/product-card` inside `woocommerce/product-template`.

## What sits on the card

| Feature | Block path | Notes |
|---|---|---|
| Media | `.sk-card-media` group | `position: relative` so Quick View can overlay |
| Quick View | `skaflow/quick-view-button` | Context `postId`. REST `GET /wp-json/skaflow/v1/quick-view/{id}` (`public/class-api-quick-view.php`). JS: `src/js/quick-view.js`, delegated. Assets when `skaflow_boots_product_card_features()` |
| Swatches | `skaflow/product-swatches` | Render → `Product_Swatches::render_grid_swatches()`. JS: `src/js/shared/product-grid-swatches.js` |
| Load More | Not on the card | Page-fetch append, see below |

## Load More

Page-fetch, not REST. Frontend `GET`s `/shop/page/N/` (or the current
archive URL), extracts `.wc-block-product-template > li`, appends to the
page-1 grid.

```
product-collection → skaflow/product-card → skaflow_get_product_card_blocks_markup()
  → page 2 HTML fetch → extract <li> → append
```

| Piece | Location |
|---|---|
| Frontend | `src/js/shared/load-more.js` |
| Query helpers | `includes/load-more-functions.php` (generic) + `includes/woocommerce/load-more-functions.php` |
| Button | `lib/class-load-more.php` when `loadMore: true` |

v1 scope: inherited Product Collection on shop / category / attribute
archives. Appended cards must use delegated events
(`src/js/shared/delegated-events.js`).

## Classic loop

Separate PHP path: `woocommerce/content-product.php` plus
`Classic_WC_Compatibility` hooks. It puts **`.wp-block-skaflow-product-card`**
on the inner wrapper so Sass matches the block card. It does **not** render
the block.

| Feature | Block cards | Classic loop |
|---|---|---|
| Shell | `skaflow/product-card` | `content-product.php` + hooks |
| Swatches | `skaflow/product-swatches` | `render_loop_swatches()` |
| Quick View | `skaflow/quick-view-button` | PHP helper markup |
| Load More | Page-fetch of FSE SSR | Not used |

## Related

- `context/kb/map-edit.md` — Product card, Load More, Quick View, filters
- `context/kb/plugin-wc-shop-archive.md` — filter refresh, view toggle, category banner
- `context/kb/query.md` — blog Query Loop (not this grid)
