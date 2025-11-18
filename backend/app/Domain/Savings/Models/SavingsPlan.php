<?php

declare(strict_types=1);

namespace App\Domain\Savings\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Savings\Enums\SavingsPlanFrequency;
use App\Models\User;
use Database\Factories\Domain\Savings\Models\SavingsPlanFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class SavingsPlan extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return SavingsPlanFactory::new();
    }

    protected $guarded = ['id'];

    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    public function executions(): HasMany
    {
        return $this->hasMany(SavingsPlanExecution::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function shouldExecute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (is_null($this->last_executed_at)) {
            return true;
        }

        return match ($this->frequency) {
            SavingsPlanFrequency::Daily => $this->last_executed_at->isBefore(today()),
            SavingsPlanFrequency::Weekly => $this->last_executed_at->isBefore(now()->subWeek()),
            SavingsPlanFrequency::Monthly => $this->last_executed_at->isBefore(now()->subMonth()),
            default => false,
        };
    }

    protected function casts(): array
    {
        return [
            'amount_eur' => 'decimal:2',
            'last_executed_at' => 'datetime',
            'next_execution_date' => 'datetime',
            'frequency' => SavingsPlanFrequency::class,
        ];
    }
}
