<?php

namespace App\Http\Requests;

use App\Enums\WbpStatus;
use App\Rules\UniqueNik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWbpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16', new UniqueNik],
            'no_register' => ['required', 'string', 'max:50', 'unique:wbp,no_register'],
            'blok_kamar' => ['required', 'string', 'max:20'],
            'no_hp' => ['nullable', 'string', 'regex:/^[0-9+\-\s]+$/', 'max:20'],
            'agama' => ['required', Rule::in(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'])],
            'jenis_kelamin' => ['nullable', Rule::in(['Laki-laki', 'Perempuan'])],
            'status' => ['required', Rule::enum(WbpStatus::class)],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
