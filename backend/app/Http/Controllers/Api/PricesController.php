<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Domain\Prices\Actions\GetLatestMetalPrices;
use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;

final class PricesController extends Controller
{
    public function __invoke(GetLatestMetalPrices $action): JsonResponse
    {
        $prices = $action->execute();

        return ApiResponse::success([
            'updated_at' => $prices['updated_at'],
            'prices' => $prices['data'],
        ], 'Latest metal prices retrieved successfully');
    }
}
