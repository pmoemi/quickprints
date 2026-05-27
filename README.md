# QuickPrints BMS

Business Management System for print, signage, and fabrication shops. QuickPrints BMS brings sales, production, finance, HR, and client communication into one Laravel application with role-based access and multi-branch support.

**Repository:** [github.com/pmoemi/quickprints](https://github.com/pmoemi/quickprints)

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

- PHP **8.3+** with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`
- Composer 2.x
- MySQL/MariaDB
- Apache with `mod_rewrite` (XAMPP works out of the box)

> **Node.js / npm are not required.** The BMS UI is served entirely from Blade views. Vite and Tailwind in this repo are unused Laravel boilerplate unless you extend the frontend pipeline yourself.

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/pmoemi/quickprints.git
cd quickprints
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` for your environment. For **XAMPP / MySQL**:

```env
APP_NAME=QuickPrints
APP_URL=http://localhost/quickprints

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quickprints
DB_USERNAME=root
DB_PASSWORD=
```

Create the database before migrating:

```sql
CREATE DATABASE quickprints CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Database

```bash
php artisan migrate
php artisan db:seed
```

The seeder loads demo company settings, sample jobs, clients, inventory, and staff accounts.

### 5. Storage link

```bash
php artisan storage:link
```

### 6. Run the application

**With XAMPP Apache**, point the document root to the project folder (the included `.htaccess` rewrites requests to `public/`). Visit:

```
http://localhost/quickprints/login
```

**Or use Laravel's built-in server:**

```bash
php artisan serve
```

Then open `http://127.0.0.1:8000/login`.

**Optional — Laravel dev stack** (includes queue worker, log tail, and Vite; only needed if you add Vite-based assets):

```bash
composer dev
```

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
public/                   # Web root (via .htaccess rewrite)
```

---

## Common Commands

```bash
# Run tests
composer test

# Clear caches after config changes
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

## Deployment Notes

1. Set `APP_ENV=production`, `APP_DEBUG=false`, and a strong `APP_KEY`.
2. Configure real mail credentials (`MAIL_*`) for invoice and notification emails.
3. **Set the web server document root to `public/`** (e.g. `/www/wwwroot/quickprints.work/public`). Do not point the vhost at the project root on production.
4. Ensure `storage/` and `bootstrap/cache/` are owned by the web server user (`www` on aaPanel/BT):

   ```bash
   sudo chown -R www:www storage bootstrap/cache
   sudo chmod -R 775 storage bootstrap/cache
   ```

5. Run migrations and seed on MySQL:

   ```bash
   php artisan config:clear
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```

6. Run `php artisan config:cache` and `php artisan route:cache` in production **after** permissions are correct.
7. Ensure HTTPS and rotate all demo passwords.

### XAMPP subdirectory

If running under `http://localhost/quickprints/`, uncomment `RewriteBase /quickprints/` in the project root `.htaccess`.

---

## License

This project is open-source software licensed under the [MIT License](https://opensource.org/licenses/MIT).
