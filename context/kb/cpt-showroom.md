# Showroom

Locator and single-page contracts. Start files: `context/kb/map-edit.md`.
Registration: `lib/class-content-types.php` (`showrooms` + `showroom-locations`).

## Locator

Tabs are **posts**. Markers are **`showroom-locations` terms**.

| Control | Resolves to |
|---|---|
| Tab click | One published showroom (`post_title` on the tab) |
| Marker click | Every post assigned to that state; Swiper when N > 1 |

Card heading uses ACF **`warehouse`**, not the post title
(`lib/class-showroom-locator.php`). One state per showroom (first assigned
term). Marker coordinates live in PHP config keyed by term slug; render a
marker only when that term has posts.

Blocks: `skaflow/showroom-locator`, `skaflow/showroom-locator-full`.
Card markup: `includes/showroom/showroom-card-functions.php`.

When removing an ACF field from JSON, **do not delete the post meta**.
Locator cards still read legacy fields (including `time` for hours on the
card).

## Single

Editor-first: thin FSE template `templates/single-showrooms.html` plus post
content. Dynamic blocks only:

| Block | Role |
|---|---|
| `skaflow/showroom-status-bar` | Open today / address / phone pills |
| `skaflow/showroom-location-details` | Address and hours |
| `skaflow/showroom-map-link` | Directions |

Hours on the single are a fixed 7-day `opening_hours` group, not a repeater
(`includes/showroom/showroom-hours-functions.php`).

## Related

- `context/kb/map-edit.md` — Showroom single
- `includes/showroom/bootstrap.php` — what loads
