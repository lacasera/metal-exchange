<?php

declare(strict_types=1);

namespace App\Domain\Trades\Contracts;

use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Models\Trade;
use App\Models\User;

interface SellMetalInterface
{
    public function execute(User $user, Metal $metal, float $amount): Trade;
}
