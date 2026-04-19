<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BulkMarkReadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids'      => ['sometimes', 'array'],
            'ids.*'    => ['integer', 'min:1'],
            'category' => ['sometimes', 'string', 'in:announcements,mentions,billing,system'],
        ];
    }
}
