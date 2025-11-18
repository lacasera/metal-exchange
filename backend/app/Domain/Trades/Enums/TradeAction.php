<?php

declare(strict_types=1);

namespace App\Domain\Trades\Enums;

enum TradeAction: string
{
    case BUY = 'buy';
    case SELL = 'sell';
}
