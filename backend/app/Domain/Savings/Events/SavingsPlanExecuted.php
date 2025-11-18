<?php

declare(strict_types=1);

namespace App\Domain\Savings\Events;

use App\Domain\Savings\Models\SavingsPlanExecution;
use Illuminate\Foundation\Events\Dispatchable;

final class SavingsPlanExecuted
{
    use Dispatchable;

    public function __construct(
        public SavingsPlanExecution $execution
    ) {}
}
