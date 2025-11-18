<?php

declare(strict_types=1);

namespace App\Domain\Trades\Builders;

use Illuminate\Database\Eloquent\Builder;

final class TradeQueryBuilder extends Builder
{
    public function forUser(int $userId): self
    {
        return $this->where('user_id', $userId);
    }

    public function forMetal(int $metalId): self
    {
        return $this->where('metal_id', $metalId);
    }

    public function calculateHoldings(): float
    {
        return (float) $this->selectRaw("
            SUM(
                CASE 
                    WHEN action = 'buy'  THEN metal_quantity
                    WHEN action = 'sell' THEN -metal_quantity
                    ELSE 0
                END
            ) AS balance
        ")
            ->lockForUpdate()
            ->value('balance') ?? 0.0;
    }
}
