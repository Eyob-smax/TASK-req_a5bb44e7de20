<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Responses\ApiEnvelope;
use App\Models\Appointment;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AppointmentController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Appointment::class);

        $appointments = $this->appointmentService->list($request->user());
        return ApiEnvelope::data($appointments);
    }

    public function store(CreateAppointmentRequest $request): JsonResponse
    {
        $this->authorize('create', Appointment::class);

        $owner       = User::findOrFail($request->integer('owner_user_id'));
        $appointment = $this->appointmentService->create($request->user(), $owner, $request->validated());
        return ApiEnvelope::data($appointment, 201);
    }

    public function show(Appointment $appointment): JsonResponse
    {
        $this->authorize('view', $appointment);

        return ApiEnvelope::data($appointment->load('owner'));
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('update', $appointment);

        $appointment = $this->appointmentService->update($request->user(), $appointment, $request->validated());
        return ApiEnvelope::data($appointment);
    }

    public function destroy(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorize('delete', $appointment);

        $this->appointmentService->cancel($request->user(), $appointment);
        return ApiEnvelope::data(null, 204);
    }
}
