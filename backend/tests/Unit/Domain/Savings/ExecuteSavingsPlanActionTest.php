<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Savings;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Prices\Models\Metal;
use App\Domain\Savings\Actions\ExecuteSavingsPlan;
use App\Domain\Savings\Enums\SavingsPlanExecutionStatus;
use App\Domain\Savings\Events\SavingsPlanExecuted;
use App\Domain\Savings\Models\SavingsPlan;
use App\Domain\Savings\Models\SavingsPlanExecution;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ExecuteSavingsPlanActionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function execute_creates_execution_when_plan_should_execute(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->active()
            ->monthly()
            ->create([
                'amount_eur' => 500.0,
                'last_executed_at' => null,
            ]);

        $metalPrice = 1950.75;
        $expectedQuantity = round(500.0 / $metalPrice, 6);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => $metalPrice],
                ],
            ]);

        $action = new ExecuteSavingsPlan($mockPricesAction);

        $execution = $action->execute($plan);
        $this->assertInstanceOf(SavingsPlanExecution::class, $execution);
        $this->assertEquals($plan->id, $execution->savings_plan_id);
        $this->assertEquals($metal->id, $execution->metal_id);
        $this->assertEquals($metalPrice, $execution->metal_price_eur);
        $this->assertEquals(500.0, $execution->amount_eur);
        $this->assertEquals($expectedQuantity, $execution->metal_quantity);
        $this->assertEquals(SavingsPlanExecutionStatus::COMPLETED, $execution->status);
        $this->assertNotNull($execution->executed_at);

        $plan->refresh();
        $this->assertNotNull($plan->last_executed_at);
        $this->assertNotNull($plan->next_execution_date);

        Event::assertDispatched(SavingsPlanExecuted::class, function ($event) use ($execution) {
            return $event->execution->id === $execution->id;
        });
    }

    #[Test]
    public function execute_returns_null_when_plan_should_not_execute(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create();
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->create([
                'status' => 'paused',
            ]);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $action = new ExecuteSavingsPlan($mockPricesAction);

        $result = $action->execute($plan);

        $this->assertNull($result);
    }

    #[Test]
    public function execute_throws_exception_when_price_not_available(): void
    {
        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAU']);
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->active()
            ->create([
                'last_executed_at' => null,
            ]);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAG', 'price_eur' => 25.50],
                ],
            ]);

        $action = new ExecuteSavingsPlan($mockPricesAction);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Price for XAU not available.');

        $action->execute($plan);
    }

    #[Test]
    public function execute_calculates_quantity_with_correct_precision(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XAG']);
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->active()
            ->weekly()
            ->create([
                'amount_eur' => 237.89,
                'last_executed_at' => null,
            ]);

        $metalPrice = 24.875;
        $expectedQuantity = round(237.89 / $metalPrice, 6);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAG', 'price_eur' => $metalPrice],
                ],
            ]);

        $action = new ExecuteSavingsPlan($mockPricesAction);

        $execution = $action->execute($plan);

        $this->assertEquals($expectedQuantity, (float) $execution->metal_quantity);
        $this->assertIsString($execution->metal_quantity);
    }

    #[Test]
    public function execute_updates_plan_with_execution_dates(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XPT']);
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->active()
            ->monthly()
            ->create([
                'amount_eur' => 1000.0,
                'last_executed_at' => null,
            ]);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XPT', 'price_eur' => 920.50],
                ],
            ]);

        $action = new ExecuteSavingsPlan($mockPricesAction);

        $execution = $action->execute($plan);

        $this->assertInstanceOf(SavingsPlanExecution::class, $execution);

        $plan->refresh();
        $this->assertNotNull($plan->last_executed_at);
        $this->assertNotNull($plan->next_execution_date);
        $this->assertTrue($plan->next_execution_date->isAfter(now()));
    }

    #[Test]
    public function execute_handles_multiple_metals_in_price_data(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $metal = Metal::factory()->create(['symbol' => 'XPD']);
        $plan = SavingsPlan::factory()
            ->forUser($user)
            ->forMetal($metal)
            ->active()
            ->weekly()
            ->create([
                'amount_eur' => 750.0,
                'last_executed_at' => null,
            ]);

        $mockPricesAction = $this->mock(GetLatestMetalPricesInterface::class);
        $mockPricesAction->shouldReceive('execute')
            ->once()
            ->andReturn([
                'data' => [
                    ['symbol' => 'XAU', 'price_eur' => 1950.50],
                    ['symbol' => 'XAG', 'price_eur' => 25.75],
                    ['symbol' => 'XPD', 'price_eur' => 1245.80],
                    ['symbol' => 'XPT', 'price_eur' => 920.25],
                ],
            ]);

        $action = new ExecuteSavingsPlan($mockPricesAction);

        $execution = $action->execute($plan);

        $this->assertEquals(1245.80, $execution->metal_price_eur);
        $this->assertEquals(round(750.0 / 1245.80, 6), $execution->metal_quantity);
    }
}
