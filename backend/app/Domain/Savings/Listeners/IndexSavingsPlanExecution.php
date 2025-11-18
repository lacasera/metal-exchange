<?php

declare(strict_types=1);

namespace App\Domain\Savings\Listeners;

use App\Domain\Savings\Services\ExecutionIndexer;

final readonly class IndexSavingsPlanExecution
{
    public function __construct(
        private ExecutionIndexer $indexer
    ) {}

    public function handle(): void
    {
        $this->indexer->index();
    }
}
