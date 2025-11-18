<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Trades;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Enums\TradeAction;
use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class TradesControllerTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function buy_executes_purchase_and_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);

        $this->mock(GetLatestMetalPricesInterface::class)
            ->shouldReceive(methodNames: 'execute')
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => 1950.00],
                ],
            ]);

        $response = $this->actingAs($user)->postJson('/api/trades/buy', [
            'metal_id' => $metal->id,
            'amount' => 100.0,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Metal purchased successfully',
            ]);

        $this->assertDatabaseHas('trades', [
            'user_id' => $user->id,
            'metal_id' => $metal->id,
            'action' => TradeAction::BUY->value,
            'amount_eur' => 100.0,
        ]);
    }

    #[Test]
    public function sell_executes_sale_and_returns_successful_response(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAG']);

        Trade::factory()->create([
            'user_id' => $user->id,
            'metal_id' => $metal->id,
            'action' => TradeAction::BUY,
            'metal_quantity' => 10.0,
            'amount_eur' => 250.0,
        ]);

        $this->mock(GetLatestMetalPricesInterface::class)
            ->shouldReceive('execute')
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAG', 'price_eur' => 25.00],
                ],
            ]);

        $response = $this->actingAs($user)->postJson('/api/trades/sell', [
            'metal_id' => $metal->id,
            'amount' => 50.0,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Metal sold successfully',
            ]);

        $this->assertDatabaseHas('trades', [
            'user_id' => $user->id,
            'metal_id' => $metal->id,
            'action' => TradeAction::SELL->value,
            'amount_eur' => 50.0,
        ]);
    }

    #[Test]
    public function portfolio_returns_user_portfolio_data(): void
    {
        $user = User::factory()->create();
        $goldMetal = Metal::factory()->create(['symbol' => 'XAU', 'name' => 'Gold']);
        $silverMetal = Metal::factory()->create(['symbol' => 'XAG', 'name' => 'Silver']);

        Trade::factory()->create([
            'user_id' => $user->id,
            'metal_id' => $goldMetal->id,
            'action' => TradeAction::BUY,
            'metal_quantity' => 5.0,
        ]);

        Trade::factory()->create([
            'user_id' => $user->id,
            'metal_id' => $silverMetal->id,
            'action' => TradeAction::BUY,
            'metal_quantity' => 100.0,
        ]);

        $response = $this->actingAs($user)->getJson('/api/trades/portfolio');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Portfolio retrieved successfully',
            ])
            ->assertJsonStructure([
                'data' => [
                    '*' => ['metal', 'metal_name', 'holdings'],
                ],
            ]);
    }

    #[Test]
    public function history_returns_paginated_trade_history(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create();

        Trade::factory()->count(3)->create([
            'user_id' => $user->id,
            'metal_id' => $metal->id,
        ]);

        $otherUser = User::factory()->create();
        Trade::factory()->create([
            'user_id' => $otherUser->id,
            'metal_id' => $metal->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/trades');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Trade history retrieved successfully',
            ])
            ->assertJsonStructure([
                'data',
                'pagination' => [
                    'current_page',
                    'total',
                    'per_page',
                ],
            ]);

        $responseData = $response->json();
        $this->assertCount(3, $responseData['data']);
    }

    #[Test]
    public function buy_handles_invalid_metal_gracefully(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/trades/buy', [
            'metal_id' => 999,
            'amount' => 100.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metal_id']);
    }

    #[Test]
    public function sell_handles_invalid_metal_gracefully(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/trades/sell', [
            'metal_id' => 999,
            'amount' => 50.0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metal_id']);
    }

    #[Test]
    public function buy_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/trades/buy', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metal_id', 'amount']);
    }

    #[Test]
    public function sell_validates_required_fields(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/trades/sell', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['metal_id', 'amount']);
    }

    #[Test]
    public function unauthenticated_user_cannot_access_endpoints(): void
    {
        $metal = Metal::factory()->create();

        $this->postJson('/api/trades/buy', ['metal_id' => $metal->id, 'amount' => 100])
            ->assertStatus(401);

        $this->postJson('/api/trades/sell', ['metal_id' => $metal->id, 'amount' => 50])
            ->assertStatus(401);

        $this->getJson('/api/trades/portfolio')
            ->assertStatus(401);

        $this->getJson('/api/trades')
            ->assertStatus(401);
    }
}
