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
- `role:admin` middleware is a custom `App\Http\Middleware\RoleMiddleware` (variadic roles, checks `$user->role->value` against the `App\Enums\UserRole` backed enum)
- All auth routes in `routes/web.php`; auth scaffolding in `routes/auth.php`

### Models & IDs
- **UUID primary key** (`HasUuids`): `Wbp`, `User`, `MonitoringLog`, `BorrowBook`
- **Integer auto-increment** (no `HasUuids`): `ServiceCategory`, `Setting`, `AuditLog`
- `Wbp` and `MonitoringLog` use `SoftDeletes` (queries auto-exclude deleted rows)
- **Enums**: backed string enums live in `app/Enums/` — `UserRole` (`admin`/`user`), `WbpStatus` (`Aktif`/`Non-Aktif`), `MonitoringStatus`, `BorrowStatus` (`Dipinjam`/`Dikembalikan`). Status-ish columns (`users.role`, `wbp.status`, `monitoring_logs.status`) are DB enums mirroring them; validate with `Rule::enum(...)`, never hand-rolled `in:` rules
- **Gotcha (fixed)**: `wbp.agama` was originally a MySQL enum `['Islam','Kristen','Katolik','Hindu','Budha']` (one `d`), but migration `2026_08_09_065201_add_contact_fields_to_wbp_table` widens it to `['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu']` and backfills `Budha`→`Buddha`, so it now matches `StoreWbpRequest`/`WbpFactory`. `migrate --seed` works on fresh MySQL; only pre-existing DBs that never ran that migration still hit `Data truncated for column 'agama'`

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
- **`video-call` is displayed as "Kunjungan"** via the accessor `ServiceCategory::getNamaLayananAttribute()` (slug + `nama_layanan` DB value untouched; all relations via `category_id`). Never change the DB value for that slug — the accessor is the single source of display truth. When eager-loading `category` with a partial select, include `slug` (e.g. `with('category:id,nama_layanan,slug')`) or the accessor can't map
- Editing a session (status/tanggal/waktu/keterangan) goes to `PATCH /monitoring/{id}` → `monitoring.update` (`UpdateMonitoringRequest`, direct `$log->update()`, no upsert); the quick status dropdown uses `monitoring.update-status`
- WBP search combobox for "Catat Sesi Baru" (Kunjungan form) hits `GET /monitoring/wbp/search` → `monitoring.wbp-search` (Alpine `wbpSearch` component in `resources/js/app.js`, min 2 chars, only `Aktif` WBP, limit 20). Don't reintroduce a full-WBP `<select>`
- Per-category status options come from `MonitoringStatus::optionsFor($slug)`; fulfillment check is `MonitoringLog::isTerpenuhi()`
- Pemeriksaan Kesehatan is intentionally simple: options are `Selesai`/`Belum` displayed as **Sudah**/**Belum** per selected date. `MonitoringStatus::labelKesehatan()` maps any non-`Belum` value (incl. legacy `Hadir`/`Tidak Hadir`/`Izin`) to `Sudah` — app-level only, no DB migration
- Perawatan Diri & Kesehatan & Laundry use a **global date picker** (`?tab=...&tanggal=YYYY-MM-DD`); status is upserted per (wbp, category, tanggal) via `monitoring.set-status`. Don't revert the hidden `tanggal` to `now()` — the selected date drives the row
- A WBP's *current* status for a category is its **latest** log (see `Wbp::progressHak()` / `DashboardController`) — used for "terpenuhi" progress badges. **Perawatan Diri is the exception: it's date-scoped** (logs fetched `whereDate('tanggal', $tanggal)`, keyed by `wbp_id`), not latest-log
- Every monitoring index page (video-call, perawatan, pemeriksaan-kesehatan, layanan-laundry, peminjaman-buku) only lists **active** WBP: `Wbp::where('status', WbpStatus::Aktif->value)->orderBy('nama')` — replicate this filter in any new monitoring page
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
