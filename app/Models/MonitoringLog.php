<?php

namespace App\Models;

use App\Enums\MonitoringStatus;
use Database\Factories\MonitoringLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonitoringLog extends Model
{
    /** @use HasFactory<MonitoringLogFactory> */
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'wbp_id',
        'user_id',
        'category_id',
        'status',
        'tanggal',
        'waktu_mulai',
        'waktu_selesai',
        'keterangan',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => MonitoringStatus::class,
            'tanggal' => 'date',
        ];
    }

    public function wbp(): BelongsTo
    {
        return $this->belongsTo(Wbp::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function isTerpenuhi(): bool
    {
        return $this->status->isTerpenuhi();
    }

    public function scopeForCategory(Builder $query, ServiceCategory|string $category): Builder
    {
        return $query->where('category_id', $category instanceof ServiceCategory ? $category->id : $category);
    }

    public function scopeBetween(Builder $query, ?string $start, ?string $end): Builder
    {
        if ($start) {
            $query->whereDate('tanggal', '>=', $start);
        }

        if ($end) {
            $query->whereDate('tanggal', '<=', $end);
        }

        return $query;
    }
}
