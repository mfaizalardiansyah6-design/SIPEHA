<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_layanan',
        'slug',
        'tipe_layanan',
    ];

    public function monitoringLogs(): HasMany
    {
        return $this->hasMany(MonitoringLog::class);
    }

    /**
     * Label tampilan layanan. Slug lama `video-call` tetap dipakai sebagai
     * identitas internal & relasi, tetapi ditampilkan sebagai "Kunjungan".
     * Data database tidak diubah agar kompatibel dengan data lama.
     */
    protected function getNamaLayananAttribute(?string $value): ?string
    {
        return $this->slug === 'video-call' ? 'Kunjungan' : $value;
    }
}
