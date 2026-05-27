# QuickPrints BMS

Business Management System for print, signage, and fabrication shops. QuickPrints BMS brings sales, production, finance, HR, and client communication into one Laravel application with role-based access and multi-branch support.

**Repository:** [github.com/pmoemi/quickprints](https://github.com/pmoemi/quickprints)

---

## Quick Start

```bash
git clone https://github.com/pmoemi/quickprints.git
cd quickprints
composer install
cp .env.example .env          # edit MySQL credentials before continuing
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan storage:link
bash scripts/verify-install.sh
```

Or run the bundled setup script (after editing `.env` with your MySQL credentials):

```bash
composer setup
```

> **MySQL is required.** Do not use SQLite — it will cause missing-table errors and deployment issues.

---

## Features

### Sales & CRM
- **Job tracker** — manage print jobs from intake to completion
- **Kanban board** — visual workflow across production stages
- **Quotes** — build and export PDF quotes
- **Leads & follow-ups** — capture and nurture prospects
- **Daily sales log** — record branch-level sales activity
- **Commissions & sales targets** — track team performance

### Production
- **Designer, operator, fabrication, and delivery boards** — role-specific queues
- **Artwork uploads** — attach files to jobs
- **Services catalog** — configurable service items and pricing

### Finance
- **Expenses (Opex), payroll, petty cash, and assets**
- **General ledger** and **VAT reports**
- **Bank & cash reconciliation**
- **Procurement, suppliers, purchase orders, and recurring bills**
- **PDF invoices, receipts, and financial reports** (DomPDF)

### HR & Operations
- **Staff management** with login accounts
- **Attendance** and **leave requests** (approve/reject workflow)
- **Payslips**

### Platform
- **Multi-branch filtering** — users see data for their branch or all branches
- **Role-based permissions** — Admin, GM, Operations, Sales, Designer, and more
- **Client portal** — shareable token links for job status
- **Internal messaging & notifications**
- **Audit log** — track sensitive changes
- **Settings** — company profile, branding, invoice design, email templates, finance config
- **REST API** — Sanctum-authenticated endpoints at `/bms/api`
- **Dark / light theme** per user

---

## Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | PHP 8.3, Laravel 13 |
| Auth (API) | Laravel Sanctum |
| Database | MySQL / MariaDB |
| PDF | barryvdh/laravel-dompdf |
| Frontend | Blade templates with inline CSS (no build step) |
| Charts | Chart.js (CDN on dashboard) |

---

## Requirements

- PHP **8.3+** with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`
- Composer 2.x
- **MySQL 5.7+ / MariaDB 10.3+**
- Apache with `mod_rewrite`

> **Node.js / npm are not required.** The BMS UI is served entirely from Blade views.

---

## Installation

### 1. Clone and install

```bash
git clone https://github.com/pmoemi/quickprints.git
cd quickprints
composer install
```

### 2. Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — **must use MySQL**:

```env
APP_NAME=QuickPrints
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quickprints
DB_USERNAME=root
DB_PASSWORD=
```

Create the database first:

```sql
CREATE DATABASE quickprints CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Verify the connection before migrating:

```bash
php artisan config:clear
php artisan db:show    # must show Connection: mysql
```

### 3. Database

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
```

### 4. Run locally

**Recommended — Laravel dev server:**

```bash
php artisan serve
# → http://127.0.0.1:8000/login
```

**XAMPP subdirectory** (`http://localhost/quickprints/`):

1. Place the project in `htdocs/quickprints`
2. Uncomment `RewriteBase /quickprints/` in the **project root** `.htaccess`
3. Visit `http://localhost/quickprints/login`

> On production domains, do **not** set any `RewriteBase` in `public/.htaccess`.

---

## Production Deployment

### Checklist

| Step | Action |
|------|--------|
| 1 | Copy `.env.example` → `.env`, set `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` |
| 2 | Set `DB_CONNECTION=mysql` and MySQL credentials |
| 3 | Point **document root** to `public/` (e.g. `/var/www/quickprints/public`) |
| 4 | `composer install --no-dev --optimize-autoloader` |
| 5 | `php artisan key:generate` |
| 6 | `bash scripts/deploy.sh` |
| 7 | `php artisan db:seed --force` (first deploy only) |
| 8 | Fix permissions (see below) |
| 9 | `bash scripts/verify-install.sh` |

### Document root

The web server **must** serve from the `public/` folder:

```
/www/wwwroot/your-domain.com/public   ✓
/www/wwwroot/your-domain.com          ✗
```

The included `public/.htaccess` uses standard Laravel rewrites with **no hardcoded paths** — safe for any domain.

### File permissions (aaPanel / BT Panel)

The web server user (usually `www`) must own writable directories:

```bash
sudo chown -R www:www storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

Without this, you will get `tempnam()` / HTTP 500 errors because Laravel cannot write compiled views.

### After every deploy

```bash
git pull
composer install --no-dev --optimize-autoloader
composer deploy          # or: bash scripts/deploy.sh
```

Only cache config/routes **after** permissions are correct:

```bash
php artisan config:cache
php artisan route:cache
```

### Common mistakes

| Symptom | Cause | Fix |
|---------|-------|-----|
| `Table '…bms_settings' doesn't exist` | Migrations never ran on MySQL | `php artisan migrate --force` |
| Still shows SQLite in `db:show` | `.env` not updated or config cached | Edit `.env`, run `php artisan config:clear` |
| Apache generic 500 | Wrong document root or old `RewriteBase` | Point vhost to `public/`, pull latest `.htaccess` |
| `tempnam()` ErrorException | `storage/` not writable by web user | `chown -R www:www storage bootstrap/cache` |

---

## Demo Accounts

After seeding, log in with any of these accounts (password for all: `Admin@2024`):

| Role | Email |
|------|-------|
| Admin | admin@quickprints.co.ke |
| General Manager | gm@quickprints.co.ke |
| Operations Manager | ops@quickprints.co.ke |
| Receptionist | grace@quickprints.co.ke |
| Designer | david@quickprints.co.ke |
| Sales | sarah@quickprints.co.ke |

> Change these credentials before deploying to production.

---

## API

The REST API is mounted at **`/bms/api`**.

| Endpoint | Description |
|----------|-------------|
| `GET /bms/api/health` | Health check |
| `GET /bms/api/settings/branding` | Public branding (no auth) |
| `POST /bms/api/auth/login` | Obtain Sanctum token |
| `GET /bms/api/auth/me` | Current user (auth required) |

Authenticated routes expose CRUD for jobs, clients, staff, sales log, quotes, leads, inventory, and finance resources. Send the token as a Bearer header:

```
Authorization: Bearer {your-token}
```

---

## Maintenance & Demo Data

Reset or reload operational data from the CLI or the **Maintenance** page (Admin / GM):

```bash
# Show table counts
php artisan bms:reset-data counts

# Clear all BMS records (keeps users & settings)
php artisan bms:reset-data clear --force

# Reload demo jobs, clients, inventory, etc.
php artisan bms:reset-data demo --force
```

---

## Project Structure

```
app/
  Http/Controllers/Bms/   # Web BMS controllers
  Http/Controllers/Api/   # REST API controllers
  Models/                 # Eloquent models
  Support/                # Permissions, navigation, settings helpers
database/
  migrations/             # Schema
  seeders/BmsDemoSeeder.php
resources/views/          # Blade UI
routes/
  web.php                 # BMS web routes
  api.php                 # API routes (prefix: bms/api)
public/                   # Web document root (production)
scripts/
  deploy.sh               # Production deploy helper
  verify-install.sh       # Post-install checks
```

---

## Common Commands

```bash
# Full local setup
composer setup

# Production deploy (after git pull)
composer deploy

# Verify install / deployment
bash scripts/verify-install.sh

# Run tests
composer test

# Clear caches after .env changes
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Fresh database with demo data
php artisan migrate:fresh --seed

# Reload demo data only (keeps schema & users)
php artisan bms:reset-data demo --force

# Code style (Pint)
./vendor/bin/pint
```

---

## License

This project is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
