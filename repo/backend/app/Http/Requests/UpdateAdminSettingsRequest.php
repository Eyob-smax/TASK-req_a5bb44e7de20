<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateAdminSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'settings'                         => ['required', 'array'],
            'settings.edit_window_minutes'     => ['sometimes', 'integer', 'min:1', 'max:1440'],
            'settings.order_auto_close_minutes' => ['sometimes', 'integer', 'min:1', 'max:1440'],
            'settings.penalty_grace_days'      => ['sometimes', 'integer', 'min:0', 'max:365'],
            'settings.penalty_rate_bps'        => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'settings.fanout_batch_size'       => ['sometimes', 'integer', 'min:1', 'max:500'],
            'settings.backup_retention_days'   => ['sometimes', 'integer', 'min:1', 'max:365'],
            'settings.receipt_number_prefix'   => ['sometimes', 'string', 'max:10'],
        ];
    }
}
