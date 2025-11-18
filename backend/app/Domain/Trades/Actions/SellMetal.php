<?php

declare(strict_types=1);

namespace App\Domain\Trades\Actions;

use App\Domain\Prices\Contracts\GetLatestMetalPricesInterface;
use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Contracts\SellMetalInterface;
use App\Domain\Trades\Enums\TradeAction;
use App\Domain\Trades\Events\TradeExecuted;
use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class SellMetal implements SellMetalInterface
{
    public function __construct(
        private GetLatestMetalPricesInterface $prices
    ) {}

    public function execute(User $user, Metal $metal, float $amount): Trade
    {
        $trade = DB::transaction(function () use ($user, $metal, $amount) {

            $holdings = Trade::query()
                ->forUser($user->id)
                ->forMetal($metal->id)
                ->calculateHoldings();

            if ($holdings <= 0) {
                throw new Exception("You have no {$metal->name} to sell.");
            }

            $latest = $this->prices->execute();

            $priceEntry = collect($latest['data'])
                ->firstWhere('symbol', $metal->symbol);

            if (! $priceEntry) {
                throw new Exception("Price for {$metal->symbol} not available.");
            }

            $metalPrice = $priceEntry['price_eur'];

            $quantity = round($amount / $metalPrice, 8);

            if ($quantity > $holdings) {
                throw new Exception(
                    "Insufficient {$metal->name} holdings. 
                You own {$holdings}, attempted to sell {$quantity}."
                );
            }

            return Trade::query()->create([
                'user_id' => $user->id,
                'metal_id' => $metal->id,
                'action' => TradeAction::SELL,
                'metal_price_eur' => $metalPrice,
                'amount_eur' => $amount,
                'metal_quantity' => $quantity,
                'executed_at' => now(),
            ]);
        });

        event(new TradeExecuted($trade));

        return $trade;
    }
}
