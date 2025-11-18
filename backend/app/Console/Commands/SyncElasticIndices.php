<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Search\IndexRegistry;
use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use Illuminate\Console\Command;

class SyncElasticIndices extends Command
{
    protected $signature = 'elastic:sync 
                            {--force : Delete existing indices before recreating them}';

    protected $description = 'Create or sync all Elasticsearch indices based on IndexDefinitions.';

    public function __construct(
        private readonly ElasticsearchServiceInterface $elasticsearch
    ) {
        parent::__construct();
    }

    public function handle(): void
    {
        $this->info("🔍 Syncing Elasticsearch indices...\n");

        foreach (IndexRegistry::all() as $indexClass) {
            $name = $indexClass::name();
            $this->line("• Processing index: <info>{$name}</info>");

            $exists = $this->elasticsearch->indexExists($name);

            if ($exists && $this->option('force')) {
                $this->warn('  - Deleting index...');
                $this->elasticsearch->deleteIndex($name);
                $exists = false;
            }

            if (! $exists) {
                $this->info('  - Creating index...');

                $settings = $indexClass::settings() ?? [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                ];

                $this->elasticsearch->createIndex(
                    $name,
                    $settings,
                    $indexClass::mappings()
                );

                $this->info('  - Index created');
            } else {
                $this->comment('  - Index exists');
            }

            $this->info('  - Updating mapping...');
            $this->elasticsearch->putMapping($name, $indexClass::mappings());
        }
    }
}
