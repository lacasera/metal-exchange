<?php

declare(strict_types=1);

namespace App\Infrastructure\Services;

use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Exception;
use Illuminate\Support\Facades\Log;

final class ElasticsearchService implements ElasticsearchServiceInterface
{
    private static ?Client $client = null;

    public function __construct()
    {
        if (! self::$client instanceof Client) {
            self::$client = $this->createClient();
        }
    }

    public function client(): Client
    {
        return self::$client;
    }

    public function indexExists(string $index): bool
    {
        try {
            return $this->client()->indices()->exists(['index' => $index])->asBool();
        } catch (Exception $e) {
            Log::error('Failed to check if index exists', [
                'index' => $index,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function createIndex(string $index, array $settings = [], array $mappings = []): void
    {
        $body = [];

        if ($settings !== []) {
            $body['settings'] = $settings;
        }

        if ($mappings !== []) {
            $body['mappings'] = $mappings;
        }

        $this->client()->indices()->create([
            'index' => $index,
            'body' => $body,
        ]);
    }

    public function deleteIndex(string $index): void
    {
        $this->client()->indices()->delete(['index' => $index]);
    }

    public function putMapping(string $index, array $mappings): void
    {
        $this->client()->indices()->putMapping([
            'index' => $index,
            'body' => $mappings,
        ]);
    }

    public function search(string $index, array $query): array
    {
        try {
            $response = $this->client()->search([
                'index' => $index,
                ...$query,
            ]);

            return $response->asArray();
        } catch (Exception $e) {
            Log::error('Elasticsearch search failed', [
                'index' => $index,
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function index(string $index, string $id, array $document): void
    {
        $this->client()->index([
            'index' => $index,
            'id' => $id,
            'body' => $document,
        ]);
    }

    public function bulk(array $operations): void
    {
        $this->client()->bulk(['body' => $operations]);
    }

    private function createClient(): Client
    {
        $hosts = config('services.elasticsearch.hosts', ['http://elastic@elastic:9200']);

        return ClientBuilder::create()
            ->setHosts($hosts)
            ->setElasticMetaHeader(false)
            ->build();
    }
}
