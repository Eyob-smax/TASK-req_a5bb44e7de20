<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFeeCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'       => ['sometimes', 'string', 'max:50'],
            'label'      => ['sometimes', 'string', 'max:255'],
            'is_taxable' => ['sometimes', 'boolean'],
        ];
    }
}
