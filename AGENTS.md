# AGENTS.md

## Project Overview

Laravel 13 app for managing WBP (Warga Binaan Pemasyarakatum) — Indonesian prison inmate data and monitoring services.

## Commands

```bash
composer setup        # install + migrate + npm install + build
composer dev          # server + queue + pail + vite concurrently
composer test         # config:clear then php artisan test
php artisan test --filter=TestClassName::testMethodName  # single test
./vendor/bin/pint     # lint/format (Laravel Pint)
```

## Database setup (MySQL)

- Dev/production DB is **MySQL**: `.env` must set `DB_CONNECTION=mysql` (+ `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/`DB_PASSWORD`). `.env.example` already ships MySQL values (database `semhak_db`). `.env` is gitignored and absent from the repo.
- First run: copy `.env.example` → `.env`, adjust MySQL creds, then `php artisan key:generate && php artisan config:clear && php artisan migrate --seed`.
- **Gotcha**: with no `.env`, config falls back to SQLite at `database/database.sqlite`, and **any** `php artisan` command fails with "Database file ... does not exist" — including `composer install`, whose `package:discover` hook boots the app and hits `Schema::hasTable('settings')`. `composer setup` cannot fix this (it runs `composer install` before the `.env` copy step). Either configure `.env` first or create the empty file:

```bash
New-Item -ItemType File -Path "database\database.sqlite"
```
- After `migrate --seed`, admin login is `admin@simhak.test` / `password` (all seeded petugas also use `password`).

## Architecture

### Roles & Middleware
- **Admin** (`admin`): Full access — CRUD WBP, petugas, settings, audit logs
- **User** (`user`): Read-only data, monitoring operations only
- `role:admin` middleware is a custom `App\Http\Middleware\RoleMiddleware` (variadic roles, checks `$user->role->value`)
- All auth routes in `routes/web.php`; auth scaffolding in `routes/auth.php`

### Models & IDs
- **UUID primary key** (`HasUuids`): `Wbp`, `User`, `MonitoringLog`, `BorrowBook`
- **Integer auto-increment** (no `HasUuids`): `ServiceCategory`, `Setting`, `AuditLog`
- `Wbp` and `MonitoringLog` use `SoftDeletes` (queries auto-exclude deleted rows)
- **Gotcha**: `wbp.agama` is a DB `enum ['Islam','Kristen','Katolik','Hindu','Budha']` (one `d`), but `StoreWbpRequest` and `WbpFactory` allow `'Buddha'`/`'Konghucu'`. Creating/seeding a WBP with those on strict MySQL fails (`Data truncated for column 'agama'`) — and `WbpSeeder` uses the factory, so a fresh `migrate --seed` on MySQL is virtually guaranteed to fail; SQLite (tests) doesn't enforce it.

### NIK Encryption
- `nik` field encrypted at rest (Eloquent cast)
- `nik_hash` stores deterministic SHA-256 hash for exact-match searches
- Always query by NIK using `Wbp::hashNik($nik)` — never the raw encrypted value
- Uniqueness validated via `App\Rules\UniqueNik` rule (checks `nik_hash`), with optional `$ignoreId` for updates

### Monitoring System
- 6 categories seeded in `ServiceCategorySeeder` (idempotent, keyed by `slug`); each has a dedicated page/controller
- Page→controller→slug(s): `video-call`; `perawatan` (one controller, `?tab=` flips between `potong-rambut`, `potong-kuku`); `pemeriksaan-kesehatan`; `layanan-laundry`; `peminjaman-buku`
- `service_categories.tipe_layanan` is enum `harian`/`mingguan`
- `MonitoringController::setStatus()` does application-level find-or-create by WBP+category+date (not a database-level upsert); powers perawatan, pemeriksaan-kesehatan, layanan-laundry
- video-call posts to the generic `monitoring.store`; peminjaman-buku has its own `store` that writes `BorrowBook` **plus** a `MonitoringLog` row
- Per-category status options come from `MonitoringStatus::optionsFor($slug)`; fulfillment check is `MonitoringLog::isTerpenuhi()`
- A WBP's *current* status for a category is its **latest** log (e.g. `PerawatanController` groups logs by wbp then takes `->map->last()`) — used for "terpenuhi" progress badges
- **No unique constraint** on `monitoring_logs` for (wbp_id, category_id, tanggal) — uniqueness enforced in app code only

### Settings
- `settings` table, cached 300s via `App\Services\Settings`
- `ApplyAppSettings` middleware runs on every web request — overrides locale, timezone, `app.name` (from `instansi.nama`) and `session.lifetime` (from `sistem.auto_lock` / `session_timeout`), so don't rely on `.env` for those
- Groups: `instansi`, `tampilan`, `sistem`, `dokumen`, `backup`
- Defaults in `Settings::defaults()` (always merge/read with defaults — rows may be partial)
- `backup:database` artisan command writes SQL dumps to `storage/app/backups`; scheduled in `routes/console.php` per the `backup` settings group

### Audit Logging
- `App\Services\AuditLogger` logs CRUD for WBP, petugas (`users`), settings, and user profile/settings (checks `sistem.audit_log` setting; if disabled, silently skips)

### Exports
- `/laporan/excel` — PhpSpreadsheet, `/laporan/pdf` — DomPDF

### File Uploads
- **`uploads` disk** (root `public/uploads/`, url `/uploads`) stores public images: WBP photos `wbp/`, profile photos `profile-photos/`, settings logo `settings/` — no `storage:link`/symlink needed (shared-host friendly)
- URL & delete via `App\Services\ImageUrl::url()/delete()`; helper normalizes legacy `uploads/...` DB paths, so old rows keep working without a DB migration
- `public/uploads/.htaccess` blocks script execution; `.gitignore` keeps only that file

## Testing

- SQLite in-memory (configured in `phpunit.xml`)
- `RefreshDatabase` trait
- **Factories exist for**: `Wbp`, `User`, `MonitoringLog`, `BorrowBook`, `ServiceCategory`
- **No factory for**: `AuditLog`, `Setting` (avoid `Model::factory()` for these)
- `WbpFactory` fills `nik_hash` automatically from a fake NIK (hash via `Wbp::hashNik`)
- Monitoring controllers do `ServiceCategory::where('slug', ...)->firstOrFail()` — tests must create categories with a seeded slug (e.g. `ServiceCategory::factory()->create(['slug' => 'video-call'])`, see `MonitoringTest`); `MonitoringLogFactory::forCategory()` and `withStatus()` help

## Conventions

- Indonesian language for UI and flash messages
- Form requests for validation (`StoreWbpRequest`, `UpdateWbpRequest`, etc.)
- All routes in `routes/web.php` (no route files per resource)
- Frontend: Blade + Tailwind 4 + Alpine.js; ApexCharts/FullCalendar mount in Alpine components from hidden `<script>`-fed elements using `Js::from()` — the emitted JS is an expression (`JSON.parse('...')`), not raw JSON, and gets executed via `new Function('return (...)')()` (see `chart`/`fullCalendar` in `resources/js/app.js`)

## Laravel Boost

MCP server configured in `opencode.json` — provides AI agent tools via `php artisan boost:mcp`.
