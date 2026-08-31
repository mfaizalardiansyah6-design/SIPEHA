<?php

namespace App\Http\Requests;

use App\Enums\MonitoringStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMonitoringStatusRequest extends FormRequest
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
            'status' => ['required', Rule::enum(MonitoringStatus::class)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
