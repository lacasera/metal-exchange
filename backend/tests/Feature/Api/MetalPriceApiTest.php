<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MetalPriceApiTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function chart_data_endpoint_returns_valid_structure(): void
    {
        // Mock the Elasticsearch service interface
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

        // Make request
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => '15m',
        ]));

        // Assert response structure
        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'symbol',
                'start_date',
                'interval',
                'data_points',
                'chart_data' => [
                    '*' => [
                        'timestamp',
                        'date',
                        'open',
                        'high',
                        'low',
                        'close',
                        'average',
                        'volume',
                    ],
                ],
                'summary' => [
                    'first_price',
                    'last_price',
                    'change',
                    'change_percent',
                    'high',
                    'low',
                    'average',
                ],
            ])
            ->assertJson([
                'success' => true,
                'symbol' => 'XAU',
                'data_points' => 2,
            ]);
    }

    #[Test]
    public function chart_data_endpoint_validates_required_parameters(): void
    {
        // Test missing symbol
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symbol']);

        // Test missing start_date
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);

        // Test missing interval
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17T00:00:00Z',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interval']);
    }

    #[Test]
    public function chart_data_endpoint_validates_symbol_format(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'invalid123',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['symbol']);
    }

    #[Test]
    public function chart_data_endpoint_validates_date_format(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17 00:00:00', // Wrong format
            'interval' => '15m',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['start_date']);
    }

    #[Test]
    public function chart_data_endpoint_validates_interval_values(): void
    {
        $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
            'symbol' => 'XAU',
            'start_date' => '2025-11-17T00:00:00Z',
            'interval' => 'invalid',
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['interval']);
    }

    #[Test]
    public function chart_data_endpoint_accepts_all_valid_symbols(): void
    {
        $validSymbols = ['XAU', 'XAG', 'XPT', 'XPD'];

        // Mock the Elasticsearch service interface
        $mockElasticsearch = $this->mock(ElasticsearchServiceInterface::class);
        $mockElasticsearch->shouldReceive('search')
            ->times(count($validSymbols))
            ->andReturn([
                'aggregations' => [
                    'price_over_time' => [
                        'buckets' => [],
                    ],
                ],
            ]);

        foreach ($validSymbols as $symbol) {
            $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
                'symbol' => $symbol,
                'start_date' => '2025-11-17T00:00:00Z',
                'interval' => '15m',
            ]));

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'symbol' => $symbol,
                ]);
        }
    }

    #[Test]
    public function chart_data_endpoint_accepts_all_valid_intervals(): void
    {
        $validIntervals = ['1m', '5m', '15m', '30m', '1h', '4h', '1d', '1w'];

        // Mock the Elasticsearch service interface
        $mockElasticsearch = $this->mock(ElasticsearchServiceInterface::class);
        $mockElasticsearch->shouldReceive('search')
            ->times(count($validIntervals))
            ->andReturn([
                'aggregations' => [
                    'price_over_time' => [
                        'buckets' => [],
                    ],
                ],
            ]);

        foreach ($validIntervals as $interval) {
            $response = $this->getJson('/api/metal-prices/chart?'.http_build_query([
                'symbol' => 'XAU',
                'start_date' => '2025-11-17T00:00:00Z',
                'interval' => $interval,
            ]));

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'interval' => $interval,
                ]);
        }
    }
}
