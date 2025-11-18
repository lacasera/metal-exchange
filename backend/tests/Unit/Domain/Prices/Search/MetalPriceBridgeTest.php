<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Prices\Search;

use Tests\TestCase;

final class MetalPriceBridgeTest extends TestCase
{
    public function test_metal_price_bridge_class_exists(): void
    {
        $this->assertTrue(class_exists('App\Domain\Prices\Search\MetalPrice'));
    }

    public function test_bridge_has_required_methods(): void
    {
        $bridge = new \App\Domain\Prices\Search\MetalPrice(
            $this->createMock(\App\Infrastructure\Contracts\ElasticsearchServiceInterface::class)
        );

        $this->assertTrue(method_exists($bridge, 'index'));
        $this->assertTrue(method_exists($bridge, 'bulkIndex'));
        $this->assertTrue(method_exists($bridge, 'searchPriceHistory'));
        $this->assertTrue(method_exists($bridge, 'searchBySymbol'));
        $this->assertTrue(method_exists($bridge, 'getLatestPrices'));
    }

    public function test_bridge_constructor_accepts_elasticsearch_service(): void
    {
        $mockService = $this->createMock(\App\Infrastructure\Contracts\ElasticsearchServiceInterface::class);

        $bridge = new \App\Domain\Prices\Search\MetalPrice($mockService);

        $this->assertInstanceOf(\App\Domain\Prices\Search\MetalPrice::class, $bridge);
    }

    public function test_search_price_history_returns_array(): void
    {
        $mockService = $this->createMock(\App\Infrastructure\Contracts\ElasticsearchServiceInterface::class);
        $mockService->method('search')->willReturn([
            'aggregations' => [
                'price_over_time' => [
                    'buckets' => [],
                ],
            ],
        ]);

        $bridge = new \App\Domain\Prices\Search\MetalPrice($mockService);
        $result = $bridge->searchPriceHistory('XAU', '2025-11-17T00:00:00Z', '15m');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('start_date', $result);
        $this->assertArrayHasKey('interval', $result);
        $this->assertArrayHasKey('data_points', $result);
        $this->assertArrayHasKey('chart_data', $result);
        $this->assertArrayHasKey('summary', $result);
    }
}
