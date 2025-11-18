<?php

declare(strict_types=1);

namespace App\Domain\Prices\Contracts;

interface GetLatestMetalPricesInterface
{
    public function execute(): array;
}
