<?php

declare(strict_types=1);

namespace App\Domain\Prices\Actions;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Prices\Services\MetalPriceCache;

final readonly class GetLatestMetalPrices implements GetLatestMetalPricesInterface
{
    public function __construct(
        private MetalPriceCache $cache
    ) {}

    public function execute(): array
    {
        return $this->cache->getLatest();
    }
}
