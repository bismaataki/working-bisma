# Happywood Theme Handover

## Repository
- Repository: https://github.com/bismaataki/working-bisma
- Branch: working-bisma
- Theme: happywood

## Important
This branch is intended for theme changes only.

Do not import any database.
Do not replace the existing wp-content/uploads directory.
Keep existing products, pages, menus, media and WooCommerce content.

## Setup on an Existing WordPress Site

1. Make sure the existing site is backed up.
2. Fetch the working-bisma branch.
3. Check out the branch on a compatible Happywood repository.
4. Keep the existing database and uploads directory untouched.
5. Activate the Happywood theme if it is not already active.
6. Install or enable the required plugins.
7. If building assets is necessary, run:

cd wp-content/themes/happywood
npm ci
npm run build:assets
npm run build:blocks

## Content Requirements

- Existing products and pages remain on the site.
- Product attributes and variations must be configured in WooCommerce.
- Menus should use the existing site's menu assignments.
- Theme design images are stored inside the theme and loaded using get_theme_file_uri().
- Product images and uploaded media remain in the site's existing uploads directory.

## Testing
- Homepage and theme patterns
- Header and footer
- Product pages and galleries
- Product variations and color swatches
- Product filters and collections
- Cart and checkout
- My Account and wishlist
- Newsletter integration
