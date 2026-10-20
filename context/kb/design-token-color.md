# Colour token mapping

**This is a worked example, not the current brand.** The `--cf-*` and `--nd-*`
token names below come from the design system of the build this starter was
extracted from. They are kept because the *method* is what transfers: how a
Figma palette gets reconciled against a fixed set of `theme.json` slugs, and what
to do with colours that have no slug.

On a new project, replace the Figma columns with that project's tokens. Do not
add `theme.json` slugs to accommodate a Figma palette; map onto the existing
slugs and list the leftovers, as the second table does.

Live source of truth is always `wp-content/themes/skaflow/theme.json`, never this
file.

Two rules that made the original mapping work, and should carry over:

- Keep the existing `theme.json` palette slugs. Adding a slug per Figma colour
  produces a palette the editor UI cannot present usefully.
- Product finish swatches are not brand colours. Leave them out.

## Linked colours (Figma -> theme.json)

| Figma Color Name | Figma Token | Hex | theme.json Slug | theme.json Name |
|---|---|---|---|---|
| Navy | `--cf-navy` | `#053B7A` | `primary` | Primary |
| Red | `--cf-red` | `#B3001B` | `secondary` | Secondary |
| Ink Strong | `--cf-ink-2` | `#242424` | `heading` | Heading |
| Ink (Nordic) | `--nd-ink` | `#16181B` | `body` | Body |
| Grey 1 | `--nd-grey` | `#F5F6F7` | `background` | Background |
| Green | `--nd-green` | `#2E7D52` | `tertiary` | Tertiary |
| Stone | `--cf-stone` | `#E5E2D9` | `quaternary` | Quaternary |
| Surface Alt | `--cf-surface-2` | `#F7F7F7` | `surface` | Surface |
| Star Gold | (none in PDF) | `#E8A400` | `foreground` | Foreground |
| Nordic Muted | `--nd-muted` | `#5E6166` | `neutral` | Neutral |
| White | `--cf-white` | `#FFFFFF` | `white` | White |
| Black | `--cf-black` | `#000000` | `black` | Black |
| Line 2 | `--nd-line-2` | `#D6D9DD` | `outline` | Outline |

Notes:
- `outline` now matches `--nd-line-2` exactly (`#D6D9DD`).
- `transparent`, `currentColor`, and `inherit` remain utility values in `theme.json`.

## Colors Not Yet Linked to theme.json

| Figma Color Name | Figma Token | Hex |
|---|---|---|
| Ink | `--cf-ink` | `#333333` |
| Off-white | `--cf-bg` | `#FAFAFA` |
| Mist | `--cf-mist` | `#E7EFF1` |
| Muted | `--cf-muted` | `#767676` |
| Sand | `--cf-sand` | `#F1EEE7` |
| Taupe | `--cf-taupe` | `#B6A79C` |
| Grey 2 | `--nd-grey-2` | `#ECEEF0` |
| Grey 3 | `--nd-grey-3` | `#E2E5E8` |
| Previous Outline (pending review) | (former `outline`) | `#E3E5E8` |

