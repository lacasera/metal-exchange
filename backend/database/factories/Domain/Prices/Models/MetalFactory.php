<?php

declare(strict_types=1);

namespace Database\Factories\Domain\Prices\Models;

use App\Domain\Prices\Models\Metal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Metal>
 */
final class MetalFactory extends Factory
{
    protected $model = Metal::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' Metal',
            'symbol' => fake()->unique()->regexify('[A-Z]{3}'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function gold(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Gold',
            'symbol' => 'XAU',
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Silver',
            'symbol' => 'XAG',
        ]);
    }

    public function platinum(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Platinum',
            'symbol' => 'XPT',
        ]);
    }

    public function palladium(): static
    {
        return $this->state(fn (array $attributes): array => [
            'name' => 'Palladium',
            'symbol' => 'XPD',
        ]);
    }
}
