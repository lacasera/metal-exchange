<?php

declare(strict_types=1);

namespace App\Domain\Prices\Search;

use App\Domain\Search\Indices\MetalPriceIndex;
use App\Infrastructure\Contracts\ElasticsearchServiceInterface;

final readonly class MetalPrice
{
    private string $index;

    public function __construct(
        private ElasticsearchServiceInterface $elasticsearch
    ) {
        $this->index = MetalPriceIndex::name();
    }

    public function index(string $id, array $document): void
    {
        $this->elasticsearch->index($this->index, $id, $document);
    }

    public function bulkIndex(array $documents): void
    {
        $operations = [];

        foreach ($documents as $id => $document) {
            $operations[] = ['index' => ['_index' => $this->index, '_id' => $id]];
            $operations[] = $document;
        }

        $this->elasticsearch->bulk($operations);
    }

    public function searchPriceHistory(string $symbol, string $startDate, string $interval): array
    {
        $response = $this->elasticsearch->search($this->index, [
            'size' => 0,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['symbol' => $symbol]],
                            [
                                'range' => [
                                    'updated_at' => [
                                        'gte' => $startDate,
                                        'lte' => 'now',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'aggs' => [
                    'price_over_time' => [
                        'date_histogram' => [
                            'field' => 'updated_at',
                            'fixed_interval' => $interval,
                            'min_doc_count' => 1,
                        ],
                        'aggs' => [
                            'avg_price' => [
                                'avg' => ['field' => 'price_eur'],
                            ],
                            'min_price' => [
                                'min' => ['field' => 'price_eur'],
                            ],
                            'max_price' => [
                                'max' => ['field' => 'price_eur'],
                            ],
                            'open_price' => [
                                'top_hits' => [
                                    'sort' => [['updated_at' => ['order' => 'asc']]],
                                    '_source' => ['price_eur'],
                                    'size' => 1,
                                ],
                            ],
                            'close_price' => [
                                'top_hits' => [
                                    'sort' => [['updated_at' => ['order' => 'desc']]],
                                    '_source' => ['price_eur'],
                                    'size' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        return $this->formatChartData($response, $startDate, $interval);
    }

    private function formatChartData(array $response, string $startDate, string $interval): array
    {
        $buckets = $response['aggregations']['price_over_time']['buckets'] ?? [];
        $chartData = [];

        foreach ($buckets as $bucket) {
            $timestamp = $bucket['key'];
            $date = $bucket['key_as_string'];

            $open = $bucket['open_price']['hits']['hits'][0]['_source']['price_eur'] ?? null;
            $high = $bucket['max_price']['value'] ?? null;
            $low = $bucket['min_price']['value'] ?? null;
            $close = $bucket['close_price']['hits']['hits'][0]['_source']['price_eur'] ?? null;
            $average = $bucket['avg_price']['value'] ?? null;
            $volume = $bucket['doc_count'] ?? 0;

            $chartData[] = [
                'timestamp' => $timestamp,
                'date' => $date,
                'open' => $open ? round($open, 2) : null,
                'high' => $high ? round($high, 2) : null,
                'low' => $low ? round($low, 2) : null,
                'close' => $close ? round($close, 2) : null,
                'average' => $average ? round($average, 2) : null,
                'volume' => $volume,
            ];
        }

        usort($chartData, fn (array $a, array $b): int => $a['timestamp'] <=> $b['timestamp']);

        return [
            'start_date' => $startDate,
            'interval' => $interval,
            'data_points' => count($chartData),
            'chart_data' => $chartData,
            'summary' => $this->calculateSummary($chartData),
        ];
    }

    private function calculateSummary(array $chartData): array
    {
        if ($chartData === []) {
            return [
                'first_price' => null,
                'last_price' => null,
                'change' => null,
                'change_percent' => null,
                'high' => null,
                'low' => null,
                'average' => null,
            ];
        }

        $prices = array_filter(array_column($chartData, 'close'));
        $highs = array_filter(array_column($chartData, 'high'));
        $lows = array_filter(array_column($chartData, 'low'));

        $firstPrice = $prices[0] ?? null;
        $lastPrice = end($prices) ?: null;
        $change = ($firstPrice && $lastPrice) ? $lastPrice - $firstPrice : null;
        $changePercent = ($firstPrice && $change !== null) ? ($change / $firstPrice) * 100 : null;

        return [
            'first_price' => $firstPrice,
            'last_price' => $lastPrice,
            'change' => $change ? round($change, 2) : null,
            'change_percent' => $changePercent ? round($changePercent, 2) : null,
            'high' => $highs !== [] ? round(max($highs), 2) : null,
            'low' => $lows !== [] ? round(min($lows), 2) : null,
            'average' => $prices !== [] ? round(array_sum($prices) / count($prices), 2) : null,
        ];
    }

    public function searchBySymbol(string $symbol, int $size = 20): array
    {
        return $this->elasticsearch->search($this->index, [
            'size' => $size,
            'body' => [
                'query' => [
                    'term' => ['symbol' => $symbol],
                ],
                'sort' => [
                    ['timestamp' => ['order' => 'desc']],
                ],
            ],
        ]);
    }

    public function getLatestPrices(array $symbols = []): array
    {
        $query = [
            'size' => 0,
            'body' => [
                'aggs' => [
                    'symbols' => [
                        'terms' => ['field' => 'symbol'],
                        'aggs' => [
                            'latest' => [
                                'top_hits' => [
                                    'sort' => [['timestamp' => ['order' => 'desc']]],
                                    'size' => 1,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if ($symbols !== []) {
            $query['body']['query'] = [
                'terms' => ['symbol' => $symbols],
            ];
        }

        return $this->elasticsearch->search($this->index, $query);
    }
}
