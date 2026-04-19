<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class GenerateBillRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'user_id'          => ['required', 'integer', 'exists:users,id'],
            'type'             => ['required', 'string', 'in:initial,supplemental'],
            'bill_schedule_id' => ['required_if:type,initial', 'nullable', 'integer', 'exists:bill_schedules,id'],
            'amount_cents'     => ['required_if:type,supplemental', 'nullable', 'integer', 'min:1'],
            'reason'           => ['required_if:type,supplemental', 'nullable', 'string', 'max:500'],
        ];
    }
}
