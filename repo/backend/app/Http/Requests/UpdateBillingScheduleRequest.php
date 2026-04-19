<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBillingScheduleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'string', 'in:active,paused,closed'],
            'end_on' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
