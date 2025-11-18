<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Domain\Auth\Actions\LoginUser;
use App\Domain\Auth\Actions\LogoutUser;
use App\Domain\Auth\Actions\RegisterUser;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function register(RegisterRequest $request, RegisterUser $registerUser): JsonResponse
    {
        $result = $registerUser->execute($request);

        return ApiResponse::created(
            $result->toArray(),
            'User registered successfully'
        );
    }

    public function login(LoginRequest $request, LoginUser $loginUser): JsonResponse
    {
        try {
            $result = $loginUser->execute($request);

            return ApiResponse::success(
                $result->toArray(),
                'Login successful'
            );
        } catch (ValidationException $e) {
            return ApiResponse::validationError(
                $e->errors(),
                'Invalid credentials'
            );
        }
    }

    public function logout(Request $request, LogoutUser $logoutUser): JsonResponse
    {
        $logoutUser->execute($request);

        return ApiResponse::success(null, 'Logged out successfully');
    }

    public function me(#[CurrentUser] $user, Request $request): JsonResponse
    {
        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ], 'User details retrieved successfully');
    }
}
