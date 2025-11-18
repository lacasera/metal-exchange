<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Trades;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Actions\BuyMetal;
use App\Domain\Trades\Enums\TradeAction;
use App\Domain\Trades\Events\TradeExecuted;
use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class BuyMetalActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function execute_creates_buy_trade_with_correct_calculations(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $amount = 1000.0;
        $metalPrice = 1950.50;

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => $metalPrice],
                ],
            ]);

        $action = new BuyMetal($mockPricesAction);

        $trade = $action->execute($user, $metal, $amount);

        $this->assertInstanceOf(Trade::class, $trade);
        $this->assertEquals($user->id, $trade->user_id);
        $this->assertEquals($metal->id, $trade->metal_id);
        $this->assertEquals(TradeAction::BUY, $trade->action);
        $this->assertEquals($metalPrice, $trade->metal_price_eur);
        $this->assertEquals($amount, $trade->amount_eur);
        $this->assertEquals(round($amount / $metalPrice, 8), $trade->metal_quantity);
        $this->assertNotNull($trade->executed_at);

        Event::assertDispatched(TradeExecuted::class, function ($event) use ($trade) {
            return $event->trade->id === $trade->id;
        });
    }

    #[Test]
    public function execute_throws_exception_when_price_not_available(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $amount = 1000.0;

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAG', 'price_eur' => 25.50],
                ],
            ]);

        $action = new BuyMetal($mockPricesAction);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Price for XAU not available.');

        $action->execute($user, $metal, $amount);
    }

    #[Test]
    public function execute_uses_database_transaction(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $amount = 1000.0;

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => 1950.50],
                ],
            ]);

        DB::shouldReceive('transaction')
            ->once()
            ->andReturnUsing(function ($callback) {
                return $callback();
            });

        $action = new BuyMetal($mockPricesAction);

        $trade = $action->execute($user, $metal, $amount);

        $this->assertInstanceOf(Trade::class, $trade);
    }

    #[Test]
    public function execute_calculates_quantity_correctly_with_high_precision(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $amount = 999.99;
        $metalPrice = 1923.75;
        $expectedQuantity = round($amount / $metalPrice, 8);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => $metalPrice],
                ],
            ]);

        $action = new BuyMetal($mockPricesAction);

        $trade = $action->execute($user, $metal, $amount);

        $this->assertEquals($expectedQuantity, $trade->metal_quantity);
        $this->assertIsFloat($trade->metal_quantity);

        $quantityString = (string) $trade->metal_quantity;
        $decimalPart = substr(strrchr($quantityString, '.'), 1);
        $this->assertLessThanOrEqual(8, strlen($decimalPart));
    }

    #[Test]
    public function execute_handles_multiple_metals_in_price_data(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAG']);
        $amount = 500.0;

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => 1950.50],
                    ['symbol' => 'XAG', 'price_eur' => 25.75], // Target metal
                    ['symbol' => 'XPT', 'price_eur' => 950.25],
                ],
            ]);

        $action = new BuyMetal($mockPricesAction);

        $trade = $action->execute($user, $metal, $amount);

        $this->assertEquals(25.75, $trade->metal_price_eur);
        $this->assertEquals(round(500.0 / 25.75, 8), $trade->metal_quantity);
    }
}
