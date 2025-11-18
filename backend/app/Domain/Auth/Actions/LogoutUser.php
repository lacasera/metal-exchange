<?php

declare(strict_types=1);

namespace App\Domain\Auth\Actions;

use Illuminate\Http\Request;

final class LogoutUser
{
    public function execute(Request $request): void
    {
        $request->user()->currentAccessToken()->delete();
    }
}
