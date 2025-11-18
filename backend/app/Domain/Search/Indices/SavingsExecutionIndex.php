<?php

declare(strict_types=1);

namespace App\Domain\Search\Indices;

use App\Domain\Search\Contracts\IndexDefinition;

final class SavingsExecutionIndex implements IndexDefinition
{
    public static function name(): string
    {
        return 'savings_executions';
    }

    public static function mappings(): array
    {
        return [
            'properties' => [
                'user_id' => ['type' => 'keyword'],
                'amount' => ['type' => 'float'],
                'currency' => ['type' => 'keyword'],
                'executed_at' => ['type' => 'date'],
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
