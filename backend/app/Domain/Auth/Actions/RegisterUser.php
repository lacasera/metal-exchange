<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\AuthenticatedUserData;
use App\Domain\Auth\Events\UserRegistered;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class RegisterUser
{
    public function execute(RegisterRequest $request): AuthenticatedUserData
    {
        $user = User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        event(new UserRegistered($user));

        return new AuthenticatedUserData($user, $token);
    }
}
