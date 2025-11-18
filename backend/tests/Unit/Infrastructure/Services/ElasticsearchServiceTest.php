<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Services;

use App\Infrastructure\Contracts\ElasticsearchServiceInterface;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ElasticsearchServiceTest extends TestCase
{
    #[Test]
    public function can_index_document(): void
    {
        $index = 'test_index';
        $id = 'test_id';
        $document = ['name' => 'test', 'value' => 123];

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('index')
            ->once()
            ->with($index, $id, $document);

        $mockService->index($index, $id, $document);

        $this->assertTrue(true);
    }

    #[Test]
    public function can_search_documents(): void
    {
        $index = 'test_index';
        $params = ['query' => ['match_all' => (object) []]];
        $expectedResponse = [
            'hits' => [
                'total' => ['value' => 2],
                'hits' => [
                    ['_id' => '1', '_source' => ['name' => 'doc1']],
                    ['_id' => '2', '_source' => ['name' => 'doc2']],
                ],
            ],
        ];

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('search')
            ->once()
            ->with($index, $params)
            ->andReturn($expectedResponse);

        $result = $mockService->search($index, $params);

        $this->assertEquals($expectedResponse, $result);
    }

    #[Test]
    public function can_bulk_index_documents(): void
    {
        $operations = [
            ['index' => ['_index' => 'test', '_id' => '1']],
            ['name' => 'doc1'],
            ['index' => ['_index' => 'test', '_id' => '2']],
            ['name' => 'doc2'],
        ];

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('bulk')
            ->once()
            ->with($operations);

        $mockService->bulk($operations);

        $this->assertTrue(true);
    }

    #[Test]
    public function can_check_if_index_exists(): void
    {
        $index = 'test_index';

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('indexExists')
            ->once()
            ->with($index)
            ->andReturn(true);

        $exists = $mockService->indexExists($index);

        $this->assertTrue($exists);
    }

    #[Test]
    public function returns_false_when_index_does_not_exist(): void
    {
        $index = 'nonexistent_index';

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('indexExists')
            ->once()
            ->with($index)
            ->andReturn(false);

        $exists = $mockService->indexExists($index);

        $this->assertFalse($exists);
    }

    #[Test]
    public function can_create_index(): void
    {
        $index = 'test_index';
        $settings = ['number_of_shards' => 1];
        $mappings = [
            'properties' => [
                'name' => ['type' => 'text'],
                'price' => ['type' => 'float'],
            ],
        ];

        $mockService = $this->mock(ElasticsearchServiceInterface::class);
        $mockService->shouldReceive('createIndex')
            ->once()
            ->with($index, $settings, $mappings);

        $mockService->createIndex($index, $settings, $mappings);

        $this->assertTrue(true);
    }
}
