<?php

namespace App\Providers;

use App\Domain\Prices\Events\MetalPricesUpdated;
use App\Domain\Prices\Listeners\IndexPriceChange;
use App\Domain\Prices\Search\MetalPrice;
use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use App\Infrastructure\Services\ElasticsearchService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            ElasticsearchServiceInterface::class,
            ElasticsearchService::class
        );

        $this->app->bind(
            MetalPrice::class,
            fn ($app): MetalPrice => new MetalPrice(
                $app->make(ElasticsearchServiceInterface::class)
            )
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            MetalPricesUpdated::class,
            IndexPriceChange::class
        );
    }
}
