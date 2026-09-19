# SIPEHA

Aplikasi **SIPEHA** (Sistem Informasi Pemasyarakatan) adalah aplikasi web berbasis **Laravel 13** untuk mengelola data **WBP** (Warga Binaan Pemasyarakatan) beserta layanan dan monitoring harian di lingkungan lapas/rutan.

## Fitur

- **Manajemen Data WBP** — CRUD data warga binaan (dengan enkripsi NIK di database, foto, dan pencarian via hash NIK)
- **Manajemen Petugas** — CRUD akun petugas dengan role `admin` / `user`
- **Monitoring Layanan** — pencatatan harian untuk layanan:
  - Video Call
  - Perawatan (potong rambut & potong kuku)
  - Pemeriksaan Kesehatan
  - Layanan Laundry
  - Peminjaman Buku
- **Jadwal** — kalender jadwal kegiatan (FullCalendar)
- **Statistik Dashboard** — grafik data WBP dan monitoring (ApexCharts)
- **Laporan** — ekspor ke Excel (PhpSpreadsheet) dan PDF (DomPDF)
- **Pengaturan Aplikasi** — grup `instansi`, `tampilan`, `sistem`, `dokumen`, `backup` (dengan cache 300 detik)
- **Audit Log** — catatan aktivitas CRUD (bisa diaktifkan/nonaktifkan lewat pengaturan)
- **Backup Database** — perintah artisan untuk dumping SQL ke `storage/app/backups`

## Persyaratan

- PHP `^8.3`
- Composer
- MySQL (dev/production) — SQLite dipakai untuk testing
- Node.js & npm (untuk aset frontend)

## Instalasi

```bash
# 1. Salin file environment dan sesuaikan kredensial MySQL
cp .env.example .env

# 2. Generate key aplikasi
php artisan key:generate

# 3. Jalankan migration + seed data awal (ada seeder kategori layanan, pengaturan, dan data contoh)
php artisan migrate --seed

# 4. Install dependensi frontend dan build aset
npm install
npm run build
```

> **Catatan**: jika belum ada file `.env`, konfigurasi akan jatuh ke SQLite sehingga perintah `php artisan` gagal dengan pesan `Database file ... does not exist`. Siapkan `.env` terlebih dahulu, atau buat file kosong `database\database.sqlite`.

Setelah `migrate --seed`, login admin default:

- Email: `admin@simhak.test`
- Password: `password`

Semua petugas yang di-seed juga menggunakan password `password`.

## Troubleshooting

- **`php artisan` gagal saat `composer install` atau setup** — biasanya karena belum ada file `.env`, sehingga Laravel jatuh ke SQLite dan `database/database.sqlite` tidak ada. Buat `.env` dulu, atau ciptakan file kosong `database\database.sqlite`.
- **`migrate --seed` gagal dengan `Data truncated for column 'agama'` di MySQL** — kolom `wbp.agama` adalah enum DB `['Islam','Kristen','Katolik','Hindu','Budha']` (satu huruf `d`), sedangkan `WbpFactory` mengizinkan `'Buddha'`/`'Konghucu'`. Karena `WbpSeeder` memakai factory, seeding 50 WBP berisiko gagal secara acak. Coba jalankan ulang, atau sesuaikan factory/seeder untuk menggunakan nilai enum yang valid. SQLite (pada testing) tidak menegakkan batasan ini.

## Menjalankan di Pengembangan

```bash
composer dev
```

Menjalankan server, queue worker, log, dan Vite secara bersamaan.

## Perintah Berguna

```bash
composer setup            # install + migrate + npm install + build
composer test             # config:clear lalu php artisan test
php artisan test --filter=TestClassName::testMethodName   # single test
./vendor/bin/pint         # format code (Laravel Pint)
```

## Struktur & Arsitektur Utama

- **Role & Middleware** — `admin` (full akses, termasuk CRUD), `user` (read-only + monitoring). Middleware `role:admin` adalah `App\Http\Middleware\RoleMiddleware` (variadic roles).
- **Model & ID** — `Wbp`, `User`, `MonitoringLog`, `BorrowBook` memakai UUID (`HasUuids`); `ServiceCategory`, `Setting`, `AuditLog` memakai integer auto-increment. `Wbp` dan `MonitoringLog` memakai `SoftDeletes`.
- **Enkripsi NIK** — kolom `nik` dienkripsi saat disimpan; `nik_hash` (SHA-256) untuk pencarian tepat. Selalu query NIK lewat `Wbp::hashNik()`.
- **Monitoring** — status harian per WBP per kategori dibuat dengan find-or-create di level aplikasi (tidak ada unique constraint di database). Status opsi per kategori dari `MonitoringStatus::optionsFor($slug)`.
- **Pengaturan** — `App\Services\Settings` dengan defaults; middleware `ApplyAppSettings` diterapkan di tiap request web (locale, timezone, nama aplikasi, session lifetime).
- **Upload** — disk `uploads` (root `public/uploads/`) untuk foto/logo; bantuan `App\Services\ImageUrl::url()/delete()`.

## Testing

- SQLite in-memory (dikonfigurasi di `phpunit.xml`) dengan trait `RefreshDatabase`.
- Factory tersedia untuk: `Wbp`, `User`, `MonitoringLog`, `BorrowBook`, `ServiceCategory` (hindari `Model::factory()` untuk `AuditLog` dan `Setting`).
- Tes monitoring memerlukan kategori dengan slug yang di-seed, contoh: `ServiceCategory::factory()->create(['slug' => 'video-call'])`.

## Teknologi

- [Laravel 13](https://laravel.com)
- Blade + Tailwind CSS 4 + Alpine.js
- ApexCharts (statistik) & FullCalendar (jadwal)
- PhpSpreadsheet (ekspor Excel) & DomPDF (ekspor PDF)
- Laravel Breeze (autentikasi scaffolding)
- Laravel Boost (MCP server untuk AI coding assistant)

## Lisensi

Proyek ini dibuat menggunakan skeleton [Laravel](https://laravel.com) dan dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).