<?php

declare(strict_types=1);

namespace App\Domain\Prices\Models;

use App\Domain\Savings\Models\SavingsPlan;
use Database\Factories\Domain\Prices\Models\MetalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Metal extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return MetalFactory::new();
    }

    protected $guarded = [];

    public function prices(): HasMany
    {
        return $this->hasMany(MetalPrice::class);
    }

    public function savingsPlans(): HasMany
    {
        return $this->hasMany(SavingsPlan::class);
    }
}
