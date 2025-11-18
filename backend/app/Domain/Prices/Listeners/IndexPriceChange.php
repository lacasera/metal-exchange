<?php

declare(strict_types=1);

namespace App\Domain\Prices\Listeners;

use App\Domain\Prices\Events\MetalPricesUpdated;
use App\Domain\Prices\Search\MetalPrice;
use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final readonly class IndexPriceChange implements ShouldQueue
{
    public function __construct(
        private ElasticsearchServiceInterface $elasticsearch
    ) {}

    public function handle(MetalPricesUpdated $event): void
    {
        $prices = $event->prices;
        $metalPriceService = new MetalPrice($this->elasticsearch);

        try {
            $documents = [];

            foreach ($prices as $price) {
                $id = sprintf('%s_%s', $price['symbol'], $price['updated_at']);
                $documents[$id] = [
                    'symbol' => $price['symbol'],
                    'name' => $price['name'],
                    'change_percent' => $price['change_percent'],
                    'price_eur' => $price['price_eur'],
                    'price_usd' => $price['price_usd'] ?? null,
                    'timestamp' => $price['updated_at'],
                    'updated_at' => now()->toISOString(),
                ];
            }

            $metalPriceService->bulkIndex($documents);

            Log::info('Successfully indexed metal prices to Elasticsearch', [
                'count' => count($documents),
                'symbols' => array_unique(array_column($prices, 'symbol')),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to index metal prices to Elasticsearch', [
                'error' => $e->getMessage(),
                'prices_count' => count($prices),
            ]);
        }
    }
}
