<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateCatalogItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fee_category_id'  => ['sometimes', 'integer', 'exists:fee_categories,id'],
            'sku'              => ['sometimes', 'string', 'max:100'],
            'name'             => ['sometimes', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'unit_price_cents' => ['sometimes', 'integer', 'min:0'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
