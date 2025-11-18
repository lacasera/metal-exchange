<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Trades\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Enums\TradeAction;
use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trade>
 */
final class TradeFactory extends Factory
{
    protected $model = Trade::class;

    public function definition(): array
    {
        $metalPrice = fake()->randomFloat(2, 20, 2000);
        $amount = fake()->randomFloat(2, 100, 5000);
        $quantity = round($amount / $metalPrice, 8);

        return [
            'user_id' => User::factory(),
            'metal_id' => Metal::factory(),
            'action' => fake()->randomElement(TradeAction::cases()),
            'metal_price_eur' => $metalPrice,
            'amount_eur' => $amount,
            'metal_quantity' => $quantity,
            'executed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function buy(): static
    {
        return $this->state(fn (array $attributes): array => [
            'action' => TradeAction::BUY,
        ]);
    }

    public function sell(): static
    {
        return $this->state(fn (array $attributes): array => [
            'action' => TradeAction::SELL,
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

    public function withPrice(float $price): static
    {
        return $this->state(function (array $attributes) use ($price): array {
            $quantity = round($attributes['amount_eur'] / $price, 8);

            return [
                'metal_price_eur' => $price,
                'metal_quantity' => $quantity,
            ];
        });
    }

    public function withAmount(float $amount): static
    {
        return $this->state(function (array $attributes) use ($amount): array {
            $quantity = round($amount / $attributes['metal_price_eur'], 8);

            return [
                'amount_eur' => $amount,
                'metal_quantity' => $quantity,
            ];
        });
    }
}
