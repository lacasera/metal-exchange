<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Prices\Search\MetalPrice;
use App\Http\Requests\Api\ChartDataRequest;
use App\Http\Requests\Api\LatestPricesRequest;
use App\Http\Requests\Api\SearchBySymbolRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

final class MetalPriceController extends Controller
{
    public function __construct(
        private readonly MetalPrice $metalPrice
    ) {}

    public function getChartData(ChartDataRequest $request): JsonResponse
    {
        $results = $this->metalPrice->searchPriceHistory(
            $request->getSymbol(),
            $request->getStartDate(),
            $request->getInterval()
        );

        return response()->json([
            'success' => true,
            'symbol' => $request->getSymbol(),
            ...$results,
        ]);
    }

    public function getLatestPrices(LatestPricesRequest $request): JsonResponse
    {
        $results = $this->metalPrice->getLatestPrices($request->getSymbols());

        return response()->json([
            'success' => true,
            'data' => $results,
        ]);
    }

    public function searchBySymbol(SearchBySymbolRequest $request): JsonResponse
    {
        $results = $this->metalPrice->searchBySymbol(
            $request->getSymbol(),
            $request->getSize()
        );

        return response()->json([
            'success' => true,
            'symbol' => $request->getSymbol(),
            'data' => $results,
        ]);
    }
}
