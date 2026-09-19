<?php

namespace App\Enums;

enum MonitoringStatus: string
{
    case Selesai = 'Selesai';
    case Belum = 'Belum';
    case Dibatalkan = 'Dibatalkan';
    case Hadir = 'Hadir';
    case TidakHadir = 'Tidak Hadir';
    case Izin = 'Izin';
    case Proses = 'Proses';
    case Diterima = 'Diterima';
    case BelumDiterima = 'Belum Diterima';

    /**
     * Status yang menandakan hak/ layanan telah terpenuhi.
     */
    public function isTerpenuhi(): bool
    {
        return in_array($this, [self::Selesai, self::Hadir, self::Diterima], true);
    }

    /**
     * Daftar status yang valid untuk sebuah kategori layanan.
     *
     * @return array<int, string>
     */
    public static function optionsFor(string $categorySlug): array
    {
        return match ($categorySlug) {
            'layanan-laundry' => ['Proses', 'Selesai'],
            default => ['Selesai', 'Belum'],
        };
    }

    /**
     * Label sederhana Sudah/Belum untuk halaman Pemeriksaan Kesehatan.
     * Hanya 'Belum' yang berarti belum diperiksa; nilai lain (termasuk
     * data lama Hadir/Izin/Tidak Hadir) dianggap sudah ada data → 'Sudah'.
     */
    public function labelKesehatan(): string
    {
        return $this === self::Belum ? 'Belum' : 'Sudah';
    }
}
