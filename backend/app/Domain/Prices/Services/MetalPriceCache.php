<?php

declare(strict_types=1);

namespace App\Domain\Prices\Services;

use Illuminate\Support\Facades\Redis;

final class MetalPriceCache
{
    protected string $latestKey = 'metal_prices:latest';

    protected string $previousKey = 'metal_prices:previous';

    public function storeLatest(array $prices): void
    {
        if (Redis::exists($this->latestKey)) {
            Redis::set($this->previousKey, Redis::get($this->latestKey));
        }

        Redis::set($this->latestKey, json_encode([
            'updated_at' => now()->toISOString(),
            'data' => $prices,
        ]));
    }

    public function getLatest(): array
    {
        $raw = Redis::get($this->latestKey);

        if (! $raw) {
            return [
                'updated_at' => null,
                'data' => [],
            ];
        }

        $decoded = json_decode($raw, true);

        return [
            'updated_at' => $decoded['updated_at'] ?? null,
            'data' => $decoded['data'] ?? [],
        ];
    }

    public function getPrevious(): array
    {
        $raw = Redis::get($this->previousKey);

        if (! $raw) {
            return [
                'updated_at' => null,
                'data' => [],
            ];
        }

        $decoded = json_decode($raw, true);

        return [
            'updated_at' => $decoded['updated_at'] ?? null,
            'data' => $decoded['data'] ?? [],
        ];
    }

    public function exists(): bool
    {
        return Redis::exists($this->latestKey) === 1;
    }

    public function clear(): void
    {
        Redis::del($this->latestKey);
        Redis::del($this->previousKey);
    }
}
