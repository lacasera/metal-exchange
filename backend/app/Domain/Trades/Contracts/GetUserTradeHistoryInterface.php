<?php

declare(strict_types=1);

namespace App\Domain\Trades\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface GetUserTradeHistoryInterface
{
    public function execute(User $user): LengthAwarePaginator;
}
