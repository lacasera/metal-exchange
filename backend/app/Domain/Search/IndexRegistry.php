<?php

declare(strict_types=1);

namespace App\Domain\Search;

use App\Domain\Search\Indices\MetalPriceIndex;
use App\Domain\Search\Indices\SavingsExecutionIndex;
use App\Domain\Search\Indices\TradesIndex;

class IndexRegistry
{
    /**
     * Return all IndexDefinition classes
     */
    public static function all(): array
    {
        return [
            MetalPriceIndex::class,
            TradesIndex::class,
            SavingsExecutionIndex::class,
        ];
    }
}
