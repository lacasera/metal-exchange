<?php

declare(strict_types=1);

namespace App\Domain\Savings\Jobs;

use App\Domain\Savings\Actions\ExecuteSavingsPlan;
use App\Domain\Savings\Models\SavingsPlan;
use App\Domain\Savings\Models\SavingsPlanExecution;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class ProcessSavingsPlanBatch implements ShouldQueue
{
    use Queueable;

    public function handle(ExecuteSavingsPlan $executor): void
    {
        SavingsPlan::query()
            ->active()
            ->each(fn (SavingsPlan $plan): ?SavingsPlanExecution => $executor->execute($plan));
    }
}
