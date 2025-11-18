<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Savings\Contracts\CreateSavingsPlanInterface;
use App\Domain\Savings\Contracts\ExecuteSavingsPlanInterface;
use App\Domain\Savings\Models\SavingsPlan;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\SavingsPlanRequest;
use App\Http\Resources\SavingsPlanResource;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final class SavingsPlanController extends Controller
{
    public function index(#[CurrentUser] $user): JsonResponse
    {
        $plans = SavingsPlan::query()->where('user_id', $user->id)
            ->with(['metal', 'executions'])
            ->get();

        return ApiResponse::success(
            SavingsPlanResource::collection($plans),
            'Savings plans retrieved successfully'
        );
    }

    public function store(#[CurrentUser] $user, SavingsPlanRequest $request, CreateSavingsPlanInterface $action): JsonResponse
    {
        $plan = $action->execute([...$request->validated(), 'user_id' => $user->id]);

        return ApiResponse::created(
            new SavingsPlanResource($plan),
            'Savings plan created successfully'
        );
    }

    public function show(#[CurrentUser] $user, SavingsPlan $plan): JsonResponse
    {
        if ($plan->user_id !== $user->id) {
            return ApiResponse::forbidden('You do not have access to this savings plan');
        }

        $plan->load(['metal']);

        return ApiResponse::success(
            new SavingsPlanResource($plan),
            'Savings plan retrieved successfully'
        );
    }

    public function execute(SavingsPlan $plan, ExecuteSavingsPlanInterface $action): JsonResponse
    {
        return ApiResponse::success(
            $action->execute($plan),
            'Savings plan executed successfully'
        );
    }

    public function destroy(#[CurrentUser] $user, SavingsPlan $plan): JsonResponse
    {
        if ($plan->user_id !== $user->id) {
            return ApiResponse::forbidden('You do not have access to this savings plan');
        }

        $plan->delete();

        return ApiResponse::success(
            null,
            'Savings plan deleted successfully'
        );
    }

    public function pause(#[CurrentUser] $user, SavingsPlan $plan): JsonResponse
    {
        if ($plan->user_id !== $user->id) {
            return ApiResponse::forbidden('You do not have access to this savings plan');
        }

        $plan->update(['status' => 'paused']);

        return ApiResponse::success(
            new SavingsPlanResource($plan->fresh(['metal'])),
            'Savings plan paused successfully'
        );
    }

    public function resume(#[CurrentUser] $user, SavingsPlan $plan): JsonResponse
    {
        if ($plan->user_id !== $user->id) {
            return ApiResponse::forbidden('You do not have access to this savings plan');
        }

        $plan->update(['status' => 'active']);

        return ApiResponse::success(
            new SavingsPlanResource($plan->fresh(['metal'])),
            'Savings plan resumed successfully'
        );
    }
}
