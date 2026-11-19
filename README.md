# SmartNest — Smart Apartment Management System
PHP 8 · MySQL · Vanilla JS · HTML/CSS (no frameworks, no build step)

## Setup (XAMPP / MAMP / Laragon)
1. Copy this folder into `htdocs` (or `www`) as `smartnest`.
2. Start Apache + MySQL. In phpMyAdmin → **Import** → choose `schema.sql`.
3. Edit `includes/config.php` if your MySQL user/password differ from `root` / empty.
4. Open `http://localhost/smartnest/install.php` once to load demo data, then **delete `install.php`**.
5. Open `http://localhost/smartnest/` — sign in with a demo account (password `password123`):

| Role | Login |
|---|---|
| Admin | admin@smartnest.test |
| Building Manager | manager@smartnest.test |
| Technician | tech@smartnest.test |
| Resident | resident@smartnest.test |

## Features by role
- **Guest**: landing page, flat search/filter (rent/sale/bedrooms/price), contact & tour enquiries
- **Resident**: submit/track complaints, view & pay bills, notices, chat
- **Technician**: assigned tickets, status updates + repair notes (residents notified)
- **Manager**: assign technicians, generate bills from live rates with preview, payment tracking, reports, chat
- **Admin**: dashboard, buildings & flats, user approvals, utility rates, reports (CSV), notice broadcasting

## Security built in
PDO prepared statements, `password_hash`, CSRF tokens on every form, output escaping, session ID regeneration on login, role guards on every page, CSV formula-injection guard.

## v1.1 additions
- **Charts** (dependency-free SVG, `assets/js/charts.js`): smooth area/line charts with hover tooltips (complaint trends, revenue collected vs pending) and a donut (priority distribution).
- **Notice editing**: Edit button on every notice, with optional "notify everyone about this update".
- **Flat photos**: upload on Add Flat, or Change/Remove per flat in Admin → Buildings. JPG/PNG/WebP, 5 MB max, validated by real MIME type, stored in `uploads/flats/` (PHP execution blocked by `uploads/.htaccess`).
- **Low priority** added to complaints.

### Upgrading an existing install
Import `migrate.sql` in phpMyAdmin (adds the photo column and low priority) - your data is kept. For a fresh demo with rich chart data instead, drop the `smartnest_app` database, re-import `schema.sql`, and run `install.php` again.
Make sure `uploads/` is writable by Apache. If uploads fail, raise `upload_max_filesize` / `post_max_size` in php.ini.
