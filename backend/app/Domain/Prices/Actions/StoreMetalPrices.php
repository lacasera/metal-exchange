<?php

declare(strict_types=1);

namespace App\Domain\Prices\Actions;

use App\Domain\Prices\Events\MetalPricesUpdated;
use App\Domain\Prices\Services\MetalPriceCache;
use Illuminate\Support\Collection;

final readonly class StoreMetalPrices
{
    public function __construct(
        private MetalPriceCache $cache
    ) {}

    public function execute(array|Collection $prices): void
    {
        $pricesArray = $prices instanceof Collection ? $prices->toArray() : $prices;

        $previousSnapshot = $this->cache->getPrevious();
        $previous = $previousSnapshot['data'] ?? [];

        foreach ($pricesArray as &$price) {
            $symbol = $price['symbol'];

            $prevRow = collect($previous)->firstWhere('symbol', $symbol);
            $prevValue = $prevRow['price_eur'] ?? null;

            if ($prevValue && $prevValue > 0) {
                $price['change_percent'] = round(
                    (($price['price_eur'] - $prevValue) / $prevValue) * 100,
                    3
                );
            } else {
                $price['change_percent'] = 0.0;
            }

            $price['timestamp'] = now()->toISOString();
        }

        unset($price);

        $this->cache->storeLatest($pricesArray);

        broadcast(new MetalPricesUpdated($pricesArray))->toOthers();

        event(new MetalPricesUpdated($pricesArray));
    }
}
