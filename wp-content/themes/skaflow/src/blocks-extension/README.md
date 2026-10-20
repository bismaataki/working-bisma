# Block extensions

Behavioral `addFilter()` modules for core and third-party blocks. Wired from
`src/index.js` → `index.js` here. Style variants (`is-style-*`) stay in
`src/js-admin/block-styles.js`. PHP `render_block` hooks stay in capability
directories.

| Module | Block(s) | What it adds |
|---|---|---|
| `extend-core-button/` | `core/button` | Lucide icon picker + print-order attribute. Save classes: `sk-has-icon`, `sk-icon-*`. |
| `extend-core-columns/` | `core/columns` | Tablet stack + mobile reverse. Classes: `is-stacked-on-tablet`, `sk-columns-reverse-on-mobile`. |
| `extend-core-list/` | `core/list` | Block-level Lucide icon + palette color. Save classes: `sk-has-icon`, `sk-icon-*`. |
| `extend-core-query/` | `core/query` | Related Posts inspector. Editor only — query rewrite is PHP. |
| `extend-core-query-pagination/` | `core/query-pagination` | Load More inspector. Render is `lib/class-load-more.php`. |
| `extend-responsive-background/` | `core/group`, `core/cover` | Hide background by breakpoint. Classes: `sk-hide-bg-on-*`. |

Shared catalogs (`lucide-icon-options.js`, `icon-color-options.js`) stay in
`src/js-admin/` — Icon Block registration also reads them.
