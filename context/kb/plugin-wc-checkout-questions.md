# Checkout, cart, and order questions

Delivery and collection answers: what is stored, who collects them, who
displays them. Start files: `context/kb/map-edit.md`.

Shipping **rates** are not part of this. The theme collects answers and displays
them; pricing them needs a companion plugin, which no project ships by default.
`includes/woocommerce/delivery-estimate-functions.php` documents that contract.

## The contract

`includes/woocommerce/order-questions-functions.php` owns the keys, allowed
values, and English labels. Display surfaces read only that file.

`includes/woocommerce/checkout-questions-functions.php` and `src/checkout.js`
are collection. They can be replaced. Display must not import them.

| Group | Keys → order meta |
|---|---|
| Delivery | `forklift` → `_skaflow_forklift`, `premises` → `_skaflow_premises`, `delivery_instructions` → `_skaflow_delivery_instructions` |
| Collection | `collected_by` → `_skaflow_collected_by`, plus `carrier_*` |

Underscore prefix keeps keys out of the HPOS Custom Fields metabox. Order
meta is never auto-rendered; each surface prints deliberately.

Warehouse pickup is WooCommerce `pickup_location` (shipping line meta +
`woocommerce_order_shipping_to_display`). It is not theme order meta.

`skaflow_answer_was_asked()` plus the group map drop stale answers when the
shopper switches method. Nothing required gates Place Order.

## Do not use the additional-fields API

`woocommerce_register_additional_checkout_field()` dumps every field in one
footer section, has no textarea, and cannot sit one question beside pickup
and another beside delivery.

Checkout UI is SlotFills via `registerPlugin( 'skaflow-checkout-questions' )`
in `src/checkout.js`. Schema registers on `init`. Use `wc.blocksComponents`
controls. Theme form Sass must exclude
`.wc-block-components-radio-control__option`.

Store API namespace is `skaflow`. Register it on `init`, not
`woocommerce_blocks_loaded`.

## Cart

Tiles open two popups (`src/js/cart/delivery-popup.js`, `pickup-popup.js`).
Address and rate go through the Store API. The eight answers go through
`POST /wc/store/v1/cart/extensions`. Checkout stays editable; there is no
"change this in the cart" link.

## Cart guard

`includes/woocommerce/blocks-cart-guard-functions.php` strips `wp-editor`
from WooCommerce Blocks script deps. If `core/editor` exists on the front
end, the Cart block serves the demo fixture and never hits
`/wp-json/wc/store/v1/cart`.

## Confirmation

Theme FSE template `templates/order-confirmation.html`. WooCommerce blocks
own line items and totals. Layout uses `is-style-has-sidebar-right`.
Fulfillment block: `src-jsx-blocks/order-confirmation-fulfillment/`.

Admin order screen, emails, and confirmation all hang off the contract file.
Emails use `woocommerce_get_order_item_totals`. Confirmation hooks come from
WooCommerce order-confirmation **blocks** and break if those blocks are
removed from the template.

## Related

- `context/kb/map-edit.md` — Cart / checkout questions, Order confirmation
- `includes/woocommerce/delivery-estimate-functions.php` — the rate-lookup
  contract a companion shipping plugin has to satisfy
