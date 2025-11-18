<?php

declare(strict_types=1);

namespace App\Domain\Savings\Contracts;

use App\Domain\Savings\Models\SavingsPlan;
use App\Domain\Savings\Models\SavingsPlanExecution;

interface ExecuteSavingsPlanInterface
{
    public function execute(SavingsPlan $plan): ?SavingsPlanExecution;
}
