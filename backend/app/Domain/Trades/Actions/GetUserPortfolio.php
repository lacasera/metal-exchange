<?php

declare(strict_types=1);

namespace App\Domain\Trades\Actions;

use App\Domain\Trades\Models\Trade;
use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUserPortfolio
{
    public function execute(User $user): Collection
    {
        $trades = Trade::query()
            ->where('user_id', $user->id)
            ->with('metal')
            ->get();

        return $trades->groupBy('metal_id')->map(function ($rows): array {
            $net = 0;

            foreach ($rows as $trade) {
                $net += $trade->isBuy()
                    ? $trade->metal_quantity
                    : -$trade->metal_quantity;
            }

            return [
                'metal' => $rows->first()->metal->symbol,
                'metal_name' => $rows->first()->metal->name,
                'holdings' => $net,
            ];
        })->values();
    }
}
