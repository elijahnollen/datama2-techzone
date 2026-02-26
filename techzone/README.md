# TechZone Setup Guide (GitHub ZIP + Manual MongoDB + MySQL Workbench)

This guide is for anyone setting up the project from a ZIP downloaded from the GitHub repository.

## 0. Download ZIP From GitHub Repository

1. Open the project repository in GitHub.
2. Click `Code` -> `Download ZIP`.
3. Save the ZIP file locally.
4. Extract the ZIP and rename the extracted folder to `techzone` if needed.

## 1. Required Downloads

Install these first:

1. Node.js LTS (recommended: Node 20+)
2. XAMPP (Apache + PHP)
3. MySQL Server (local installation)
4. MySQL Workbench
5. MongoDB Community Server
6. MongoDB Compass

## 2. Required PHP Version

Minimum PHP version: **PHP 8.1**

Recommended: **PHP 8.2** (stable and common with modern XAMPP builds)

Why:

- The backend uses PHP features such as `never` return type and modern typing, which require PHP 8.1+.

Check your PHP version:

```powershell
php -v
```

If using XAMPP PHP only, check in browser:

- `http://localhost/dashboard/phpinfo.php` (or create a `phpinfo()` file if needed)

## 3. Place Extracted ZIP To Correct Location

1. After extraction, place the project folder at:

`C:\xampp\htdocs\techzone`

2. Confirm these files exist:

- `C:\xampp\htdocs\techzone\backend\techzone_old_inventory.sql`
- `C:\xampp\htdocs\techzone\backend\techzone_new_inventory.sql`
- `C:\xampp\htdocs\techzone\backend\mongodb_seed\product_catalog.json`

Only `product_catalog.json` is needed from `mongodb_seed`.

## 3.1 Project File Structure (What Each Part Is For)

```text
techzone/
|-- backend/
|   |-- app/
|   |   |-- Controllers/
|   |   |-- Models/
|   |   `-- Views/
|   |-- public/
|   |-- scripts/
|   |-- mongodb_collection_schemas/
|   |-- mongodb_seed/
|   |-- .env.example
|   |-- techzone_old_inventory.sql
|   `-- techzone_new_inventory.sql
|-- frontend/
|   |-- src/
|   |   |-- controllers/
|   |   |-- models/
|   |   `-- views/
|   |-- public/
|   |-- package.json
|   `-- vite.config.ts
|-- docs/
|   |-- Application_Database_Documentation.md
|   `-- Project Documentation.md
|-- .gitignore
`-- README.md
```

### Root-Level Paths

| Path | Purpose |
|---|---|
| `backend/` | PHP backend API, SQL scripts, Mongo setup files, and maintenance scripts. |
| `frontend/` | React + Vite web client for customer storefront and admin portal UI. |
| `docs/` | Formal documentation: system/database documentation and project documentation. |
| `.gitignore` | Defines files/folders not committed to Git (example: `.env`, build artifacts, temp files). |
| `README.md` | Main setup tutorial and operational guide. |

### Backend Structure

| Path | Purpose |
|---|---|
| `backend/app/Controllers` | Request handlers and endpoint orchestration logic. |
| `backend/app/Models` | Data-access and business rule interaction layer. |
| `backend/app/Views` | API response templates/formatting layer. |
| `backend/public` | Public entrypoint used by Apache (example: `index.php`, `/health`). |
| `backend/scripts` | One-time and maintenance scripts (sync, verify, rebuild, backfill). |
| `backend/mongodb_collection_schemas` | Schema reference assets kept for documentation/reference purposes. |
| `backend/mongodb_seed` | MongoDB seed files used for setup imports (including `product_catalog.json`). |
| `backend/.env.example` | Safe environment template for backend configuration. |
| `backend/techzone_old_inventory.sql` | Legacy SQL schema/data source imported first. |
| `backend/techzone_new_inventory.sql` | New SQL schema/data imported second; depends on old inventory data. |
| `backend/seed_mongo.js` | Mongo seeding helper script for local setup flows. |

### Frontend Structure

| Path | Purpose |
|---|---|
| `frontend/src/controllers` | Client-side control/state orchestration for pages/features. |
| `frontend/src/models` | Types, API models, and frontend data contracts. |
| `frontend/src/views` | UI views/components/layouts shown to users and admins. |
| `frontend/public` | Static assets served by Vite. |
| `frontend/package.json` | Frontend dependencies and npm scripts (`dev`, `build`, etc.). |
| `frontend/vite.config.ts` | Vite dev/build configuration. |

### Documentation Files

| Path | Purpose |
|---|---|
| `docs/Application_Database_Documentation.md` | Full application, architecture, API, security, testing, and database documentation. |
| `docs/Project Documentation.md` | Project lifecycle documentation: scope, sprints, timeline, communications, outcomes. |


## 4. Start Required Services

1. Open XAMPP Control Panel.
2. Start Apache.
3. Start your local MySQL service (example service name: `MySQL80`).
4. Start MongoDB service.

Notes:

- MySQL Workbench is a client tool; it does not start MySQL server.
- MongoDB Compass is a client tool; it does not start MongoDB service.

## 5. Configure Backend `.env`

From project root (`C:\xampp\htdocs\techzone`):

```powershell
Copy-Item .\backend\.env.example .\backend\.env -Force
```

Edit `backend/.env` and set these:

- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=techzone_new_inventory`
- `DB_USER=root`
- `DB_PASS=your_mysql_password`
- `MONGO_URI=mongodb://127.0.0.1:27017`
- `MONGO_DB=techzone`
- `APP_SECRET=replace-with-a-long-random-secret`

Important for setup:

- Everyone must change credentials in their own `.env` (especially `DB_USER`, `DB_PASS`, and `APP_SECRET`).
- Do not rely on values inside `.env.example`; it is only a template (wag pansinin).

## 6. MySQL Setup In MySQL Workbench (Required Order)

Import order is strict:

1. `backend/techzone_old_inventory.sql`
2. `backend/techzone_new_inventory.sql`

Reason:

- `techzone_new_inventory.sql` reads data from `techzone_old_inventory`.

Step-by-step:

1. Open MySQL Workbench.
2. Open your local MySQL connection (example: `localhost:3306`).
3. Import old database first:
- Click `File` -> `Open SQL Script`.
- Select `backend/techzone_old_inventory.sql`.
- Click execute (lightning icon).
4. Import new database second:
- Click `File` -> `Open SQL Script`.
- Select `backend/techzone_new_inventory.sql`.
- Click execute.
5. Verify in Workbench by running:

```sql
SHOW DATABASES LIKE 'techzone_old_inventory';
SHOW DATABASES LIKE 'techzone_new_inventory';
USE techzone_new_inventory;
SHOW TABLES;
```

## 7. MongoDB Setup In Compass (Manual Collections + Product Catalog Only)

You must manually create all required collections.
Only one file is imported: `product_catalog.json`.

### 7.1 Connect To MongoDB

1. Open MongoDB Compass.
2. Connect with:

`mongodb://127.0.0.1:27017`

### 7.2 Create Database

1. Click `Create Database`.
2. Database name: `techzone`.
3. Initial collection name: `product_catalog`.
4. Click `Create Database`.

### 7.3 Create The Remaining Collections Manually

Inside database `techzone`, click `Create Collection` and add these collection names one by one:

1. `admin_audit_log`
2. `customer_audit_log`
3. `customer_inquiry`
4. `order`
5. `product_review`
6. `return_request`
7. `shopping_cart`

At this point, all required collections should exist.

### 7.4 Import Only `product_catalog.json`

1. Open collection: `techzone.product_catalog`
2. Click `Add Data` -> `Import File`
3. Select file:

`C:\xampp\htdocs\techzone\backend\mongodb_seed\product_catalog.json`

4. File type: `JSON`
5. Click `Import`

Do not import other JSON files.

### 7.5 Verify MongoDB Setup

In Compass, check:

1. Database `techzone` exists
2. All 8 collections exist
3. `product_catalog` has documents
4. Other collections can remain empty initially

## 8. Enable MongoDB Extension In PHP

The backend uses PHP MongoDB classes. Enable MongoDB extension in PHP.

1. Open your active `php.ini` (XAMPP PHP).
2. Ensure this line is present and enabled:

```ini
extension=php_mongodb.dll
```

3. Restart Apache.
4. Verify:

```powershell
php -m | Select-String mongodb
```

If nothing is returned, extension is not loaded.

## 9. Run Frontend

From the frontend folder:

```powershell
cd .\frontend
npm install
npm run dev
```

Optional frontend env override:

- `VITE_API_BASE_URL`
- Default: `http://localhost/techzone/backend/public/index.php`

## 10. Verify Application

Open backend health URL:

- `http://localhost/techzone/backend/public/health`

Expected:

- JSON response containing `"ok": true`

Open frontend URLs:

- Customer portal: `http://localhost:5173/`
- Admin portal: `http://localhost:5173/?portal=admin`

## 11. Run Backend PHP Scripts (Required After Setup)

Run these from project root:

`C:\xampp\htdocs\techzone`

If `php` is not recognized in terminal, use XAMPP PHP directly:

`C:\xampp\php\php.exe`

### 11.1 Recommended Script Order

```powershell
php .\backend\scripts\sync_product_catalog_from_mysql.php
php .\backend\scripts\verify_product_catalog_sync.php
php .\backend\scripts\rebuild_order_collection.php
php .\backend\scripts\rebuild_return_request_collection.php
php .\backend\scripts\backfill_mongo_histories.php
php .\backend\scripts\backfill_refund_payments.php
php .\backend\scripts\check_catalog_mapping.php
```

### 11.2 Dry-Run Options (Safe Preview)

```powershell
php .\backend\scripts\backfill_mongo_histories.php --dry-run
php .\backend\scripts\backfill_refund_payments.php --dry-run
```

### 11.3 What Each Script Does

1. `sync_product_catalog_from_mysql.php`  
   Syncs MySQL products into MongoDB `product_catalog`.
2. `verify_product_catalog_sync.php`  
   Verifies product ID mapping consistency between MySQL and MongoDB.
3. `rebuild_order_collection.php`  
   Rebuilds MongoDB `order` documents from MySQL sales data.
4. `rebuild_return_request_collection.php`  
   Rebuilds MongoDB `return_request` documents from MySQL return data.
5. `backfill_mongo_histories.php`  
   Backfills missing status-history arrays in MongoDB order/return docs.
6. `backfill_refund_payments.php`  
   Backfills missing MySQL refund payment records for finalized returns.
7. `check_catalog_mapping.php`  
   Final cross-check for catalog mapping alignment.

## 12. Quick Checks

MySQL check:

```powershell
mysql -u root -p -e "SHOW DATABASES LIKE 'techzone_new_inventory';"
```

## 13. Common Problems

1. MySQL import fails
- Confirm MySQL service is running.
- Confirm Workbench username/password.
- Confirm import order is old SQL first, new SQL second.

2. MongoDB Compass cannot connect
- Confirm MongoDB service is running.
- Use exact URI: `mongodb://127.0.0.1:27017`.

3. `product_catalog` import fails
- Confirm file path is correct.
- Confirm selected file type is JSON.
- Retry import into `techzone.product_catalog`.

4. Backend MongoDB errors
- Confirm `MONGO_URI` and `MONGO_DB=techzone` in `.env`.
- Confirm PHP MongoDB extension is enabled.

5. Frontend cannot call backend
- Confirm health endpoint works.
- Confirm Apache is running.
- Check `CORS_ALLOW_ORIGINS` in `backend/.env`.

## 14. Fast Setup Summary

1. Download ZIP from GitHub (`Code` -> `Download ZIP`) and extract to `C:\xampp\htdocs\techzone`
2. Start Apache + MySQL service + MongoDB service
3. Copy `.env` and set DB/Mongo credentials
4. Import 2 SQL files in Workbench (old then new)
5. In Compass:
- Create DB `techzone`
- Create 8 collections manually
- Import only `product_catalog.json` into `product_catalog`
6. Run:

```powershell
cd .\frontend
npm install
npm run dev
```
