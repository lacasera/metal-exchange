<?php

declare(strict_types=1);

namespace App\Domain\Trades\Models;

use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Builders\TradeQueryBuilder;
use App\Domain\Trades\Enums\TradeAction;
use App\Models\User;
use Database\Factories\Domain\Trades\Models\TradeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Trade extends Model
{
    use HasFactory;

    protected static function newFactory()
    {
        return TradeFactory::new();
    }

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'metal_price_eur' => 'float',
            'amount_eur' => 'float',
            'metal_quantity' => 'float',
            'executed_at' => 'datetime',
            'action' => TradeAction::class,
        ];
    }

    public function newEloquentBuilder($query): TradeQueryBuilder
    {
        return new TradeQueryBuilder($query);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function metal(): BelongsTo
    {
        return $this->belongsTo(Metal::class);
    }

    public function isBuy(): bool
    {
        return $this->action === TradeAction::BUY;
    }

    public function isSell(): bool
    {
        return $this->action === TradeAction::SELL;
    }
}
