<?php

namespace App\Models;

use App\Enums\WbpStatus;
use Database\Factories\WbpFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class Wbp extends Model
{
    /** @use HasFactory<WbpFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'wbp';

    protected $fillable = [
        'nama',
        'nik',
        'nik_hash',
        'no_register',
        'blok_kamar',
        'no_hp',
        'agama',
        'jenis_kelamin',
        'status',
        'foto_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nik' => 'encrypted',
            'status' => WbpStatus::class,
        ];
    }

    public function monitoringLogs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class);
    }

    public function borrowBooks(): HasMany
    {
        return $this->hasMany(BorrowBook::class);
    }

    /**
     * Hash deterministik (SHA-256) untuk pencarian NIK secara tepat
     * tanpa membuka data terenkripsi.
     */
    public static function hashNik(string $nik): string
    {
        return hash('sha256', $nik);
    }

    /**
     * Persentase kategori layanan yang sudah terpenuhi berdasarkan
     * monitoring log terbaru pada masing-masing kategori.
     *
     * @param  Collection<int, MonitoringLog>  $logs
     */
    public function progressHak($logs, int $totalCategories): int
    {
        if ($totalCategories === 0) {
            return 0;
        }

        $terpenuhi = $logs
            ->groupBy('category_id')
            ->map(fn ($items) => $items->sortByDesc('tanggal')->first())
            ->filter(fn (MonitoringLog $log) => $log->isTerpenuhi())
            ->count();

        return (int) round(($terpenuhi / $totalCategories) * 100);
    }
}
