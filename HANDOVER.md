# Happywood Project Handover

## Repository
- Repository: https://github.com/atakidevelopers/happywood
- Branch: working-bisma
- Theme: happywood
- Environment: DDEV

## Required Files
- happywood-database.sql.gz - WordPress and WooCommerce database.
- happywood-uploads.tar.gz - Product images and uploaded media.

## Setup Instructions

### 1. Clone the branch
git clone -b working-bisma https://github.com/atakidevelopers/happywood.git

### 2. Start DDEV
cd happywood
ddev start

### 3. Import database
ddev import-db --file=happywood-database.sql.gz

### 4. Restore uploads
tar -xzf happywood-uploads.tar.gz -C wp-content

### 5. Build theme assets
cd wp-content/themes/happywood
npm ci
npm run build:assets
npm run build:blocks

### 6. Review
- Frontend and product pages
- Product variations and filters
- Cart and checkout
- My Account and wishlist
- Header and footer

Note: Database and uploads archives are shared separately.
Do not commit private database exports, uploads archives, or credentials to GitHub.
