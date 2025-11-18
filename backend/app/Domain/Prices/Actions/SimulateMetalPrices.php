<?php

declare(strict_types=1);

namespace App\Domain\Prices\Actions;

use App\Domain\Prices\Models\Metal;
use App\Domain\Prices\Services\MetalPriceCache;

final readonly class SimulateMetalPrices
{
    public function __construct(
        private MetalPriceCache $cache
    ) {}

    public function execute(): array
    {
        $prices = Metal::all()->map(fn (Metal $metal): array => [
            'symbol' => $metal->symbol,
            'name' => $metal->name,
            'price_eur' => $this->randomPrice($metal->symbol),
            'updated_at' => now()->toISOString(),
        ])->toArray();

        $this->cache->storeLatest($prices);

        app(StoreMetalPrices::class)->execute($prices);

        return $prices;
    }

    private function randomPrice(string $metal): float
    {
        $base = match ($metal) {
            'XAU' => 2040.00,
            'XAG' => 24.50,
            'XPT' => 920.00,
            default => 100.00,
        };

        // Random movement: ±5%
        $variation = $base * (random_int(-50, 50) / 1000);

        return round($base + $variation, 2);
    }
}
