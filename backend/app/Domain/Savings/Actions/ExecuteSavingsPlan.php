<?php

declare(strict_types=1);

namespace App\Domain\Savings\Actions;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Savings\Contracts\ExecuteSavingsPlanInterface;
use App\Domain\Savings\Enums\SavingsPlanExecutionStatus;
use App\Domain\Savings\Events\SavingsPlanExecuted;
use App\Domain\Savings\Models\SavingsPlan;
use App\Domain\Savings\Models\SavingsPlanExecution;
use Exception;

final readonly class ExecuteSavingsPlan implements ExecuteSavingsPlanInterface
{
    public function __construct(
        private GetLatestMetalPricesInterface $prices
    ) {}

    public function execute(SavingsPlan $plan): ?SavingsPlanExecution
    {

        if (! $plan->shouldExecute()) {
            return null;
        }

        $latest = $this->prices->execute();

        $metal = $plan->metal;

        $priceEntry = collect($latest['data'])
            ->firstWhere('symbol', $metal->symbol);

        if (! $priceEntry) {
            throw new Exception("Price for {$metal->symbol} not available.");
        }

        $priceEur = $priceEntry['price_eur'];

        $quantity = round($plan->amount_eur / $priceEur, 6);

        $execution = SavingsPlanExecution::query()->create([
            'savings_plan_id' => $plan->id,
            'metal_id' => $metal->id,
            'metal_price_eur' => $priceEur,
            'amount_eur' => $plan->amount_eur,
            'metal_quantity' => $quantity,
            'status' => SavingsPlanExecutionStatus::COMPLETED,
            'executed_at' => now(),
        ]);

        $plan->update([
            'last_executed_at' => now(),
            'next_execution_date' => $plan->frequency->nextExecutionDate(),
        ]);

        event(new SavingsPlanExecuted($execution));

        return $execution;
    }
}
