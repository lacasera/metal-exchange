<?php

declare(strict_types=1);

namespace App\Domain\Trades\Events;

use App\Domain\Trades\Models\Trade;
use Illuminate\Foundation\Events\Dispatchable;

class TradeExecuted
{
    use Dispatchable;

    public function __construct(public Trade $trade) {}
}
