<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateGradeItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'      => ['sometimes', 'string', 'max:255'],
            'max_score'  => ['sometimes', 'numeric', 'min:0'],
            'weight_bps' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
