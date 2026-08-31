<?php

namespace App\Models;

use App\Enums\BorrowStatus;
use Database\Factories\BorrowBookFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowBook extends Model
{
    /** @use HasFactory<BorrowBookFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'wbp_id',
        'judul_buku',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => BorrowStatus::class,
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
        ];
    }

    public function wbp(): BelongsTo
    {
        return $this->belongsTo(Wbp::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === BorrowStatus::Dipinjam && $this->tanggal_kembali->isBefore(today());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query
            ->where('status', BorrowStatus::Dipinjam->value)
            ->whereDate('tanggal_kembali', '<', today()->toDateString());
    }
}
