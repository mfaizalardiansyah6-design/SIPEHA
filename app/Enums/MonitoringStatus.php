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
            'senam' => ['Hadir', 'Tidak Hadir', 'Izin'],
            'pencucian-baju' => ['Proses', 'Selesai'],
            'alat-ibadah' => ['Diterima', 'Belum Diterima'],
            default => ['Selesai', 'Belum'],
        };
    }
}
