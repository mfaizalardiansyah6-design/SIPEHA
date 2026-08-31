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

### NIK Encryption
- `nik` field encrypted at rest (Eloquent cast)
- `nik_hash` stores deterministic SHA-256 hash for exact-match searches
- Always query by NIK using `Wbp::hashNik($nik)` — never the raw encrypted value
- Uniqueness validated via `App\Rules\UniqueNik` rule (checks `nik_hash`), with optional `$ignoreId` for updates

### Monitoring System
- 8 categories seeded in `ServiceCategorySeeder` (idempotent, keyed by `slug`); only 6 have dedicated pages/controllers
- Page→controller→slug(s): `video-call`; `perawatan` (one controller, `?tab=` flips between `potong-rambut`, `potong-kuku`, `kebutuhan-mandi`); `alat-ibadah`; `senam`; `pencucian-baju`; `peminjaman-buku`
- `service_categories.tipe_layanan` is enum `harian`/`mingguan`
- `MonitoringController::setStatus()` does application-level find-or-create by WBP+category+date (not a database-level upsert); powers perawatan, alat-ibadah, senam, pencucian
- video-call posts to the generic `monitoring.store`; peminjaman-buku has its own `store` that writes `BorrowBook` **plus** a `MonitoringLog` row
- Per-category status options come from `MonitoringStatus::optionsFor($slug)`; fulfillment check is `MonitoringLog::isTerpenuhi()`
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
- `public` disk: WBP photos `uploads/wbp/`, profile photos `uploads/profile-photos/`, settings logo `uploads/settings/`

## Testing

- SQLite in-memory (configured in `phpunit.xml`)
- `RefreshDatabase` trait
- **Factories exist for**: `Wbp`, `User`, `MonitoringLog`, `BorrowBook`, `ServiceCategory`
- **No factory for**: `AuditLog`, `Setting` (avoid `Model::factory()` for these)
- `WbpFactory` fills `nik_hash` automatically from a fake NIK (hash via `Wbp::hashNik`)
- Monitoring controllers do `ServiceCategory::where('slug', ...)->firstOrFail()` — tests must create categories with a seeder slug (e.g. `['slug' => 'senam']`, see `MonitoringTest`); `MonitoringLogFactory::forCategory()` and `withStatus()` help

## Conventions

- Indonesian language for UI and flash messages
- Form requests for validation (`StoreWbpRequest`, `UpdateWbpRequest`, etc.)
- All routes in `routes/web.php` (no route files per resource)

## Laravel Boost

MCP server configured in `opencode.json` — provides AI agent tools via `php artisan boost:mcp`.
