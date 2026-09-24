<?php

namespace App\Rules;

use App\Models\Wbp;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueNik implements ValidationRule
{
    public function __construct(private readonly ?string $ignoreId = null) {}

    /**
     * Validasi keunikan NIK terhadap nilai hash, karena NIK disimpan terenkripsi.
     *
     * Dibedakan antara data WBP aktif yang masih terdaftar dan data yang
     * sudah dihapus (soft delete), agar pesan yang ditampilkan jelas.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Wbp::where('nik_hash', Wbp::hashNik((string) $value));

        if ($this->ignoreId !== null) {
            $query->whereKeyNot($this->ignoreId);
        }

        if ($query->exists()) {
            $fail('NIK sudah terdaftar. Silakan gunakan NIK yang berbeda.');

            return;
        }

        $trashed = Wbp::onlyTrashed()->where('nik_hash', Wbp::hashNik((string) $value));

        if ($this->ignoreId !== null) {
            $trashed->whereKeyNot($this->ignoreId);
        }

        if ($trashed->exists()) {
            $fail('NIK tersebut pernah digunakan pada data WBP yang telah dihapus. Silakan periksa data WBP terlebih dahulu.');
        }
    }
}
