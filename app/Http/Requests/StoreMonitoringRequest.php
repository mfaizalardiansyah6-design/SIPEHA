<?php

namespace App\Http\Requests;

use App\Enums\MonitoringStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMonitoringRequest extends FormRequest
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
            'wbp_id' => ['required', 'exists:wbp,id'],
            'category_id' => ['required', 'exists:service_categories,id'],
            'status' => ['required', Rule::enum(MonitoringStatus::class)],
            'tanggal' => ['required', 'date'],
            'waktu_mulai' => ['nullable', 'date_format:H:i'],
            'waktu_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:waktu_mulai'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
