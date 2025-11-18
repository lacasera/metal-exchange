<?php

declare(strict_types=1);

namespace App\Domain\Savings\Actions;

use App\Domain\Savings\Contracts\CreateSavingsPlanInterface;
use App\Domain\Savings\Enums\SavingsPlanFrequency;
use App\Domain\Savings\Models\SavingsPlan;

final class CreateSavingsPlan implements CreateSavingsPlanInterface
{
    public function execute(array $data): SavingsPlan
    {

        return SavingsPlan::query()->create([
            'name' => $data['name'],
            'metal_id' => $data['metal_id'],
            'frequency' => $data['frequency'],
            'amount_eur' => $data['amount_eur'],
            'user_id' => $data['user_id'],
            'next_execution_date' => SavingsPlanFrequency::from($data['frequency'])->nextExecutionDate(),
            'status' => 'active',
        ]);
    }
}
