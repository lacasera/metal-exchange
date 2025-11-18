<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Savings\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Savings\Enums\SavingsPlanFrequency;
use App\Domain\Savings\Models\SavingsPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavingsPlan>
 */
final class SavingsPlanFactory extends Factory
{
    protected $model = SavingsPlan::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'metal_id' => Metal::factory(),
            'name' => fake()->words(3, true).' Savings Plan',
            'amount_eur' => fake()->randomFloat(2, 50, 1000),
            'frequency' => fake()->randomElement(SavingsPlanFrequency::values()),
            'status' => fake()->randomElement(['active', 'paused']),
            'last_executed_at' => null,
            'next_execution_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'active',
        ]);
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'paused',
        ]);
    }

    public function daily(): static
    {
        return $this->state([
            'frequency' => SavingsPlanFrequency::Daily->value,
        ]);
    }

    public function weekly(): static
    {
        return $this->state([
            'frequency' => SavingsPlanFrequency::Weekly->value,
        ]);
    }

    public function monthly(): static
    {
        return $this->state([
            'frequency' => SavingsPlanFrequency::Monthly->value,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes): array => [
            'user_id' => $user->id,
        ]);
    }

    public function forMetal(Metal $metal): static
    {
        return $this->state(fn (array $attributes): array => [
            'metal_id' => $metal->id,
        ]);
    }

    public function withAmount(float $amount): static
    {
        return $this->state(fn (array $attributes): array => [
            'amount_eur' => $amount,
        ]);
    }

    public function executed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_executed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function neverExecuted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'last_executed_at' => null,
        ]);
    }
}
