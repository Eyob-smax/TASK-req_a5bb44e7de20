<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'lines'                    => ['required', 'array', 'min:1'],
            'lines.*.catalog_item_id'  => ['required', 'integer', 'exists:catalog_items,id'],
            'lines.*.quantity'         => ['required', 'integer', 'min:1'],
        ];
    }
}
