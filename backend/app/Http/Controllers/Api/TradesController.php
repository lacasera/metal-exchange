<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Prices\Models\Metal;
use App\Domain\Trades\Actions\BuyMetal;
use App\Domain\Trades\Actions\GetUserPortfolio;
use App\Domain\Trades\Actions\GetUserTradeHistory;
use App\Domain\Trades\Actions\SellMetal;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Trades\BuyMetalRequest;
use App\Http\Requests\Trades\SellMetalRequest;
use App\Http\Resources\PortfolioResource;
use App\Http\Resources\TradeResource;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;

final class TradesController extends Controller
{
    public function buy(
        #[CurrentUser] User $user,
        BuyMetalRequest $request,
        BuyMetal $action
    ): JsonResponse {
        $metal = Metal::query()->findOrFail($request->validated('metal_id'));

        $trade = $action->execute($user, $metal, $request->validated('amount'));

        return ApiResponse::created(
            new TradeResource($trade),
            'Metal purchased successfully'
        );
    }

    public function sell(
        #[CurrentUser] User $user,
        SellMetalRequest $request,
        SellMetal $action
    ): JsonResponse {
        $metal = Metal::query()->findOrFail($request->validated('metal_id'));

        $trade = $action->execute($user, $metal, $request->validated('amount'));

        return ApiResponse::created(
            new TradeResource($trade),
            'Metal sold successfully'
        );
    }

    public function portfolio(
        #[CurrentUser] User $user,
        GetUserPortfolio $action
    ): JsonResponse {
        $portfolio = $action->execute($user);

        return ApiResponse::success(
            PortfolioResource::collection($portfolio),
            'Portfolio retrieved successfully'
        );
    }

    public function history(
        #[CurrentUser] User $user,
        GetUserTradeHistory $action
    ): JsonResponse {
        $trades = $action->execute($user);

        return ApiResponse::paginated(
            TradeResource::collection($trades),
            'Trade history retrieved successfully'
        );
    }
}
