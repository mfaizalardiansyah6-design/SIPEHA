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
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Wbp::where('nik_hash', Wbp::hashNik((string) $value));

        if ($this->ignoreId !== null) {
            $query->whereKeyNot($this->ignoreId);
        }

        if ($query->exists()) {
            $fail('validation.unique')->translate();
        }
    }
}
