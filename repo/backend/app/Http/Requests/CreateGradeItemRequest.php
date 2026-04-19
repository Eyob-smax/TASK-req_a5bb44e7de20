<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateGradeItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'      => ['required', 'string', 'max:255'],
            'max_score'  => ['required', 'numeric', 'min:0'],
            'weight_bps' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
