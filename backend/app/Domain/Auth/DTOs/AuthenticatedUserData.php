<?php

declare(strict_types=1);

namespace App\Domain\Auth\DTOs;

use App\Models\User;

readonly class AuthenticatedUserData
{
    public function __construct(
        public User $user,
        public string $token,
    ) {}

    public function toArray(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'created_at' => $this->user->created_at,
            ],
            'token' => $this->token,
        ];
    }
}
