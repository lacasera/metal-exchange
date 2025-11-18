<?php

declare(strict_types=1);

namespace App\Domain\Savings\Contracts;

use App\Domain\Savings\Models\SavingsPlan;

interface CreateSavingsPlanInterface
{
    public function execute(array $data): SavingsPlan;
}
