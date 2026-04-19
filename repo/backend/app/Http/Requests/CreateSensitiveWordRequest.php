<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateSensitiveWordRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'pattern'    => ['required', 'string', 'max:100'],
            'match_type' => ['required', 'string', 'in:exact,substring'],
        ];
    }
}
