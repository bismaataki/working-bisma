# FAQ surfaces

Archive page and product-section merge. Start files: `context/kb/map-edit.md`.
CPT: `skaflow_faq` + `skaflow_faq_category` in `lib/class-content-types.php`.
FAQ categories are admin + REST only (`public` and `publicly_queryable` are
false, `rewrite` is false). Keep `show_in_rest` so the `faq-groups` term
picker still lists them. There is no public `/faq-category/` archive.

## Archive

Rewrite slug is **`faq`** (`/faq/`). Public singles 404
(`skaflow_block_skaflow_faq_singles` on `template_redirect`).

Output is the `skaflow/faq-groups` block (term picker), not a Query Loop.
Empty terms are skipped. The same FAQ may appear in more than one section.

Search is `core/search` plus a client filter on titles (`src/js/faq-search.js`).
FAQPage schema is queued on the archive.

Helpers: `includes/faq/faq-archive-functions.php`. Template:
`templates/archive-skaflow_faq.html`.

## Product section

`skaflow_get_product_faq_ids()` merges, first occurrence wins:

1. Product-specific (`product_faqs` on the product)
2. Category rows from FAQ Configurations, child → parent
3. Global (`faq_global` option)

Shared accordion markup. Block: `skaflow/product-faqs`.
Helpers: `includes/woocommerce/product-faqs-functions.php`.

## Related

- `context/kb/map-edit.md` — CPT registration, Theme Options
- `includes/accordion-functions.php` — shared accordion
