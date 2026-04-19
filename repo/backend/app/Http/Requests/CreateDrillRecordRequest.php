<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\DrDrillOutcome;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class CreateDrillRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'drill_date' => ['required', 'date_format:Y-m-d'],
            'outcome'    => ['required', new Enum(DrDrillOutcome::class)],
            'notes'      => ['nullable', 'string', 'max:2000'],
        ];
    }
}
