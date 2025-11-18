<?php

declare(strict_types=1);

namespace Tests\Unit\Http\Controllers\Api;

use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MetalPriceChartTest extends TestCase
{
    #[Test]
    public function chart_endpoint_returns_expected_structure_with_mocked_elasticsearch(): void
    {
        $mockElasticsearch = $this->mock(ElasticsearchServiceInterface::class);
        $mockElasticsearch->shouldReceive('search')
            ->once()
            ->andReturn([
                'aggregations' => [
                    'price_over_time' => [
                        'buckets' => [
                            [
                                'key' => 1700000000000,
                                'key_as_string' => '2025-11-17T00:00:00.000Z',
                                'doc_count' => 150,
                                'avg_price' => ['value' => 1950.38],
                                'max_price' => ['value' => 1955.25],
                                'min_price' => ['value' => 1945.00],
                                'first_price' => ['value' => 1948.50],
                                'last_price' => ['value' => 1952.75],
                            ],
                            [
                                'key' => 1700000900000,
                                'key_as_string' => '2025-11-17T00:15:00.000Z',
                                'doc_count' => 125,
                                'avg_price' => ['value' => 1954.25],
                                'max_price' => ['value' => 1958.00],
                                'min_price' => ['value' => 1950.00],
                                'first_price' => ['value' => 1952.75],
                                'last_price' => ['value' => 1956.25],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => '15m',
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'symbol',
                'start_date',
                'interval',
                'data_points',
                'chart_data',
                'summary',
            ])
            ->assertJson([
                'success' => true,
                'symbol' => 'XAU',
                'interval' => '15m',
            ]);
    }

    #[Test]
    public function chart_endpoint_validates_required_fields(): void
    {
        $response = $this->getJson('/api/metal-prices/chart');
        $response->assertStatus(422);

        $response = $this->getJson('/api/metal-prices/chart?symbol=XAU');
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date', 'interval']);
    }

    #[Test]
    public function chart_endpoint_validates_symbol_enum(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'INVALID',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symbol']);
    }

    #[Test]
    public function chart_endpoint_validates_date_format(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17 00:00:00',
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    #[Test]
    public function chart_endpoint_validates_interval_enum(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interval']);
    }
}
