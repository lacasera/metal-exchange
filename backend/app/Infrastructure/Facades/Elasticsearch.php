<?php

declare(strict_types=1);

namespace App\Infrastructure\Facades;

use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Client client()
 * @method static bool indexExists(string $index)
 * @method static void createIndex(string $index, array $settings = [], array $mappings = [])
 * @method static void deleteIndex(string $index)
 * @method static void putMapping(string $index, array $mappings)
 * @method static array search(string $index, array $query)
 * @method static void index(string $index, string $id, array $document)
 * @method static void bulk(array $operations)
 *
 * @see \App\Infrastructure\Services\ElasticsearchService
 */
final class Elasticsearch extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ElasticsearchServiceInterface::class;
    }
}
