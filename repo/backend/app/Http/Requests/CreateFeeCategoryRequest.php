<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateFeeCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'       => ['required', 'string', 'max:50', 'unique:fee_categories,code'],
            'label'      => ['required', 'string', 'max:255'],
            'is_taxable' => ['sometimes', 'boolean'],
        ];
    }
}
