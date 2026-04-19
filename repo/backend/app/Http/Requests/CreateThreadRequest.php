<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateThreadRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'section_id'  => ['required', 'integer', 'exists:sections,id'],
            'type'        => ['required', 'string', 'in:discussion,announcement'],
            'title'       => ['required', 'string', 'max:255'],
            'body'        => ['required', 'string', 'max:65535'],
        ];
    }
}
