<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Domain\Prices\Models\Metal;
use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class MetalPriceControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function get_chart_data_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);

        $this->mock(ElasticsearchServiceInterface::class)
            ->shouldReceive('search')
            ->andReturn([
                'aggregations' => [
                    'price_history' => [
                        'buckets' => [
                            [
                                'key_as_string' => '2025-11-17T00:00:00.000Z',
                                'key' => 1700000000000,
                                'doc_count' => 15,
                                'open' => ['value' => 1948.50],
                                'high' => ['value' => 1955.25],
                                'low' => ['value' => 1945.00],
                                'close' => ['value' => 1952.75],
                                'average' => ['value' => 1950.38],
                            ],
                        ],
                    ],
                ],
            ]);

        $response = $this->actingAs($user)
            ->getJson('/api/metal-prices/chart?symbol=XAU&start_date=2025-11-17T00:00:00Z&interval=15m');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'symbol' => 'XAU',
            ])
            ->assertJsonStructure([
                'chart_data',
                'start_date',
                'interval',
            ]);
    }

    #[Test]
    public function get_chart_data_handles_empty_results(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XPD']);

        $this->mock(ElasticsearchServiceInterface::class)
            ->shouldReceive('search')
            ->andReturn([
                'aggregations' => [
                    'price_history' => [
                        'buckets' => [],
                    ],
                ],
            ]);

        $response = $this->actingAs($user)
            ->getJson('/api/metal-prices/chart?symbol=XPD&start_date=2025-11-17T00:00:00Z&interval=1h');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'symbol' => 'XPD',
            ])
            ->assertJsonStructure([
                'chart_data',
                'start_date',
                'interval',
            ]);
    }
}
