<?php

declare(strict_types=1);

namespace App\Domain\Search\Contracts;

interface IndexDefinition
{
    public static function name(): string;

    public static function mappings(): array;

    public static function settings(): ?array;
}
