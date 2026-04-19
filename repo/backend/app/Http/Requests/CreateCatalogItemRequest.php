<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateCatalogItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'fee_category_id'  => ['required', 'integer', 'exists:fee_categories,id'],
            'sku'              => ['required', 'string', 'max:100', 'unique:catalog_items,sku'],
            'name'             => ['required', 'string', 'max:255'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'unit_price_cents' => ['required', 'integer', 'min:0'],
            'is_active'        => ['sometimes', 'boolean'],
        ];
    }
}
