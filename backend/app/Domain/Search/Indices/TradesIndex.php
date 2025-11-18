<?php

declare(strict_types=1);

namespace App\Domain\Search\Indices;

use App\Domain\Search\Contracts\IndexDefinition;

final class TradesIndex implements IndexDefinition
{
    public static function name(): string
    {
        return 'trades';
    }

    public static function mappings(): array
    {
        return [
            'properties' => [
                'trade_id' => ['type' => 'keyword'],
                'user_id' => ['type' => 'keyword'],
                'asset' => ['type' => 'keyword'],
                'quantity' => ['type' => 'float'],
                'price' => ['type' => 'float'],
                'trade_date' => ['type' => 'date'],
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
