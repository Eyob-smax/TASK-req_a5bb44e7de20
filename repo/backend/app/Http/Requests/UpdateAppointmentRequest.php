<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'resource_type'   => ['sometimes', 'string', 'in:room,lab,advisor,equipment'],
            'resource_ref'    => ['nullable', 'string', 'max:255'],
            'scheduled_start' => ['sometimes', 'date'],
            'scheduled_end'   => ['sometimes', 'date'],
            'notes'           => ['nullable', 'string', 'max:2000'],
            'status'          => ['sometimes', 'string', 'in:scheduled,rescheduled,canceled,completed'],
        ];
    }
}
