<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiEnvelope;
use App\Models\Mention;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MentionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Mention::class);

        $mentions = Mention::where('mentioned_user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return ApiEnvelope::data($mentions);
    }
}
