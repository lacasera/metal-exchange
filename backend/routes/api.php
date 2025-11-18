<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\MetalPriceController;
use App\Http\Controllers\Api\PricesController;
use App\Http\Controllers\Api\SavingsPlanController;
use App\Http\Controllers\Api\TradesController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::get('/prices/latest', PricesController::class);

// Metal prices endpoints
Route::prefix('metal-prices')->name('metal-prices.')->group(function (): void {
    // Chart data endpoint
    Route::get('chart', [MetalPriceController::class, 'getChartData'])
        ->name('chart');

    // Latest prices for all or specific symbols
    Route::get('latest', [MetalPriceController::class, 'getLatestPrices'])
        ->name('latest');

    // Search prices by symbol
    Route::get('{symbol}', [MetalPriceController::class, 'searchBySymbol'])
        ->name('search')
        ->where('symbol', '[A-Z]{2,10}');
});

// Protected routes
Route::group(['middleware' => ['auth:sanctum']], function (): void {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Savings plans
    Route::prefix('/savings-plans')->group(function (): void {
        Route::get('/', [SavingsPlanController::class, 'index']);
        Route::post('/', [SavingsPlanController::class, 'store']);
        Route::get('/{plan}', [SavingsPlanController::class, 'show']);
        Route::delete('/{plan}', [SavingsPlanController::class, 'destroy']);
        Route::put('/{plan}/pause', [SavingsPlanController::class, 'pause']);
        Route::put('/{plan}/resume', [SavingsPlanController::class, 'resume']);
        Route::post('/{plan}/execute', [SavingsPlanController::class, 'execute']);
    });

    Route::prefix('/trades')->group(function (): void {
        Route::get('/', [TradesController::class, 'history']);
        Route::post('/buy', [TradesController::class, 'buy']);
        Route::post('/sell', [TradesController::class, 'sell']);
        Route::get('/portfolio', [TradesController::class, 'portfolio']);
    });
});
