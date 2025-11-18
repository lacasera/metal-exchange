<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Savings\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Savings\Enums\SavingsPlanExecutionStatus;
use App\Domain\Savings\Models\SavingsPlan;
use App\Domain\Savings\Models\SavingsPlanExecution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsPlanExecution>
 */
final class SavingsPlanExecutionFactory extends Factory
{
    protected $model = SavingsPlanExecution::class;

    public function definition(): array
    {
        $metalPrice = fake()->randomFloat(4, 20, 2000);
        $amount = fake()->randomFloat(2, 50, 500);
        $quantity = round($amount / $metalPrice, 6);

        return [
            'savings_plan_id' => SavingsPlan::factory(),
            'metal_id' => Metal::factory(),
            'metal_price_eur' => $metalPrice,
            'amount_eur' => $amount,
            'metal_quantity' => $quantity,
            'status' => fake()->randomElement(SavingsPlanExecutionStatus::cases()),
            'executed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SavingsPlanExecutionStatus::PENDING,
            'executed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SavingsPlanExecutionStatus::COMPLETED,
            'executed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SavingsPlanExecutionStatus::FAILED,
            'executed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function forPlan(SavingsPlan $plan): static
    {
        return $this->state(fn (array $attributes): array => [
            'savings_plan_id' => $plan->id,
            'metal_id' => $plan->metal_id,
        ]);
    }

    public function forMetal(Metal $metal): static
    {
        return $this->state(fn (array $attributes): array => [
            'metal_id' => $metal->id,
        ]);
    }

    public function withPrice(float $price): static
    {
        return $this->state(function (array $attributes) use ($price): array {
            $quantity = round($attributes['amount_eur'] / $price, 6);

            return [
                'metal_price_eur' => $price,
                'metal_quantity' => $quantity,
            ];
        });
    }

    public function withAmount(float $amount): static
    {
        return $this->state(function (array $attributes) use ($amount): array {
            $quantity = round($amount / $attributes['metal_price_eur'], 6);

            return [
                'amount_eur' => $amount,
                'metal_quantity' => $quantity,
            ];
        });
    }
}
