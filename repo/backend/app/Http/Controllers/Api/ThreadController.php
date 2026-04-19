<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\EnrollmentStatus;
use App\Enums\RoleName;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateThreadRequest;
use App\Http\Requests\UpdateThreadRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\Enrollment;
use App\Models\Thread;
use App\Services\ContentSubmissionService;
use CampusLearn\Auth\ScopeContext;
use CampusLearn\Auth\ScopeResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ThreadController extends Controller
{
    public function __construct(
        private readonly ContentSubmissionService $contentService,
        private readonly ScopeResolutionService $scopeService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Thread::class);

        $user = $request->user();

        $isGlobalAdmin = $this->scopeService->canPerform($user->id, RoleName::Administrator, ScopeContext::global());
        $isRegistrar   = $this->scopeService->hasRole($user->id, RoleName::Registrar);
        $isTeacher     = $this->scopeService->hasRole($user->id, RoleName::Teacher);

        $threads = Thread::with('author')
            ->when(!$isGlobalAdmin && !$isRegistrar, function ($q) use ($user, $isTeacher) {
                if ($isTeacher) {
                    $scope = $this->scopeService->teacherScopeIds($user->id);
                    if (!$scope['global']) {
                        $q->where(function ($sub) use ($scope) {
                            $sub->whereIn('section_id', $scope['section_ids'])
                                ->orWhereIn('course_id', $scope['course_ids']);
                        });
                    }
                } else {
                    // Student: only enrolled sections
                    $sectionIds = Enrollment::where('user_id', $user->id)
                        ->where('status', EnrollmentStatus::Enrolled)
                        ->pluck('section_id');
                    $q->whereIn('section_id', $sectionIds);
                }
            })
            ->when($request->query('section_id'), fn ($q, $v) => $q->where('section_id', $v))
            ->orderByDesc('created_at')
            ->paginate(20);

        return ApiEnvelope::data($threads);
    }

    public function store(CreateThreadRequest $request): JsonResponse
    {
        $this->authorize('create', Thread::class);

        $thread = $this->contentService->createThread($request->user(), $request->validated());
        return ApiEnvelope::data($thread, 201);
    }

    public function show(Thread $thread): JsonResponse
    {
        $this->authorize('view', $thread);

        return ApiEnvelope::data($thread->load(['author', 'posts.author']));
    }

    public function update(UpdateThreadRequest $request, Thread $thread): JsonResponse
    {
        $this->authorize('update', $thread);

        $thread = $this->contentService->updateThread($request->user(), $thread, $request->validated());
        return ApiEnvelope::data($thread);
    }
}
