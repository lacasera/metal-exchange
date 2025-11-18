<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Prices\Actions\SimulateMetalPrices;
use Illuminate\Console\Command;
use Illuminate\Support\Sleep;

final class SimulateMetalPriceFeed extends Command
{
    protected $signature = 'prices:simulate {--interval=5}';

    protected $description = 'Simulate real-time metal price updates';

    public function handle(SimulateMetalPrices $priceSimulator): void
    {
        $interval = (int) $this->option('interval');

        $this->info("Starting metal price simulation. Updating every {$interval}s...");

        while (true) {
            $prices = $priceSimulator->execute();
            $this->info('Updated: '.json_encode($prices));

            Sleep::sleep($interval);
        }
    }
}
