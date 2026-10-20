# My Account

Theme chrome around WooCommerce's shortcode account. Start files:
`context/kb/map-edit.md`.

## Engine

Do **not** replace this with the WooCommerce block My Account. The engine
stays `[woocommerce_my_account]`. Theme PHP lives under
`includes/woocommerce/my-account/`. Template overrides are selective
(`woocommerce/myaccount/my-account.php`, `navigation.php`).

Nav comes from `wc_get_account_menu_items()`. Keep Downloads, Quotes, or
anything else WooCommerce or a plugin emits.

## Assets

Dedicated webpack entry: `src/my-account.js` → `build/my-account.*`.
Enqueued only on `is_account_page()`. Do not import `public.js` or
`sass/index.scss` into that entry (a single-entry `wp-scripts` build would
also wipe `build/`).

## Wishlist

Logged-in only. Table `{prefix}skaflow_wishlist_items`. REST under
`skaflow/v1/wishlist`. Account endpoint:
`includes/woocommerce/my-account/my-account-wishlist-functions.php`.
Engine: `lib/class-wishlist.php`.

## Related

- `context/kb/map-edit.md` — Wishlist, Cart / checkout questions
- `context/kb/grid-product.md` — heart on the card
