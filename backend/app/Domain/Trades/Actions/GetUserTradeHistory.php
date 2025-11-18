<?php

declare(strict_types=1);

namespace App\Domain\Trades\Actions;

use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class GetUserTradeHistory
{
    public function execute(User $user): LengthAwarePaginator
    {
        return Trade::query()
            ->where('user_id', $user->id)->latest()
            ->paginate(15);
    }
}
