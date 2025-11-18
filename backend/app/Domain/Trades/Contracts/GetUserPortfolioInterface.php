<?php

declare(strict_types=1);

namespace App\Domain\Trades\Contracts;

use App\Models\User;
use Illuminate\Support\Collection;

interface GetUserPortfolioInterface
{
    public function execute(User $user): Collection;
}
