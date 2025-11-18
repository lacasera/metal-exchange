<?php

declare(strict_types=1);

namespace App\Infrastructure\Contracts;

use Elastic\Elasticsearch\Client;

interface ElasticsearchServiceInterface
{
    public function client(): Client;

    public function indexExists(string $index): bool;

    public function createIndex(string $index, array $settings = [], array $mappings = []): void;

    public function deleteIndex(string $index): void;

    public function putMapping(string $index, array $mappings): void;

    public function search(string $index, array $query): array;

    public function index(string $index, string $id, array $document): void;

    public function bulk(array $operations): void;
}
