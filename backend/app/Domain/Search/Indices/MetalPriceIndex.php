<?php

declare(strict_types=1);

namespace App\Domain\Search\Indices;

use App\Domain\Search\Contracts\IndexDefinition;

final class MetalPriceIndex implements IndexDefinition
{
    public static function name(): string
    {
        return 'metal_prices';
    }

    public static function mappings(): array
    {
        return [
            'properties' => [
                'symbol' => ['type' => 'keyword'],
                'name' => ['type' => 'text'],
                'price_eur' => ['type' => 'float'],
                'change_percent' => ['type' => 'float'],
                'updated_at' => ['type' => 'date'],
            ],
        ];
    }

    public static function settings(): array
    {
        return [
            'number_of_shards' => 1,
            'number_of_replicas' => 0,
        ];
    }
}
