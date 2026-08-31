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
}
