<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateRefundRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'amount_cents' => ['required', 'integer', 'min:1'],
            'reason_code'  => ['required', 'string', 'max:100'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ];
    }
}
