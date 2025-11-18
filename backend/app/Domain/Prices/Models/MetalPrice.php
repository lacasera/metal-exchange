<?php

declare(strict_types=1);

namespace App\Domain\Prices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MetalPrice extends Model
{
    protected $guarded = [];

    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'price_eur' => 'decimal:4',
            'price_usd' => 'decimal:4',
        ];
    }
}
