<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateTaxRuleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'rate_bps'       => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'effective_from' => ['sometimes', 'date'],
            'effective_to'   => ['nullable', 'date'],
        ];
    }
}
