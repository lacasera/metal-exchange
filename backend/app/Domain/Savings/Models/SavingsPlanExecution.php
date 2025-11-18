<?php

declare(strict_types=1);

namespace App\Domain\Savings\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Savings\Enums\SavingsPlanExecutionStatus;
use Database\Factories\Domain\Savings\Models\SavingsPlanExecutionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SavingsPlanExecution extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function newFactory()
    {
        return SavingsPlanExecutionFactory::new();
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SavingsPlan::class, 'savings_plan_id');
    }

    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    protected function casts(): array
    {
        return [
            'executed_at' => 'datetime',
            'metal_price_eur' => 'decimal:4',
            'amount_eur' => 'decimal:2',
            'metal_quantity' => 'decimal:6',
            'status' => SavingsPlanExecutionStatus::class,
        ];
    }
}
