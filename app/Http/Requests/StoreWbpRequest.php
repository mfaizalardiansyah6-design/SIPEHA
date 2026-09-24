<?php

namespace App\Http\Requests;

use App\Enums\WbpStatus;
use App\Rules\UniqueNik;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
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
            'foto' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value instanceof UploadedFile && ! $value->isValid()) {
                        $fail('Foto gagal diunggah. Silakan coba lagi.');
                    }
                },
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nama.required' => 'Nama WBP wajib diisi.',
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits' => 'NIK harus berupa angka dan memiliki format yang valid.',
            'no_register.required' => 'Nomor register wajib diisi.',
            'no_register.unique' => 'Nomor register sudah digunakan. Silakan gunakan nomor register yang berbeda.',
            'blok_kamar.required' => 'Blok/kamar wajib diisi.',
            'no_hp.regex' => 'Nomor HP tidak valid. Silakan masukkan nomor HP yang benar.',
            'agama.required' => 'Agama wajib dipilih.',
            'agama.in' => 'Agama tidak valid.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
            'jenis_kelamin.in' => 'Jenis kelamin tidak valid.',
            'status.required' => 'Status WBP wajib dipilih.',
            'status.enum' => 'Status WBP tidak valid.',
            'foto.image' => 'Format foto tidak didukung. Gunakan JPG, JPEG, atau PNG.',
            'foto.mimes' => 'Format foto tidak didukung. Gunakan JPG, JPEG, atau PNG.',
            'foto.max' => 'Ukuran foto terlalu besar. Silakan gunakan foto dengan ukuran yang lebih kecil.',
        ];
    }
}
