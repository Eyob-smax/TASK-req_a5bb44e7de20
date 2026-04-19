<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ModerationActionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:hide,restore,lock,unlock'],
            'notes'  => ['nullable', 'string', 'max:1000'],
        ];
    }
}
