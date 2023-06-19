<?php

namespace Spatie\Analytics;

use Google\Analytics\Data\V1beta\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\FilterExpression;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\RunReportResponse;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;

class AnalyticsClient
{
    protected $cacheLifeTimeInMinutes = 0;
    protected $service;
    protected $cache;

    public function __construct(
        BetaAnalyticsDataClient $service,
        Repository $cache
    ) {
        $this->cache = $cache;
        $this->service = $service;
    }

    public function setCacheLifeTimeInMinutes(int $cacheLifeTimeInMinutes): self
    {
        $this->cacheLifeTimeInMinutes = $cacheLifeTimeInMinutes * 60;

        return $this;
    }

    public function get(
        string $propertyId,
        Period $period,
        array $metrics,
        array $dimensions = [],
        int $maxResults = 10,
        array $orderBy = [],
        int $offset = 0,
        FilterExpression $dimensionFilter = null
    ): Collection {
        $typeCaster = resolve(TypeCaster::class);

        $response = $this->runReport([
            'property' => "properties/{$propertyId}",
            'dateRanges' => [
                $period->toDateRange(),
            ],
            'metrics' => $this->getFormattedMetrics($metrics),
            'dimensions' => $this->getFormattedDimensions($dimensions),
            'limit' => $maxResults,
            'offset' => $offset,
            'orderBys' => $orderBy,
            'dimensionFilter' => $dimensionFilter,
        ]);

        $result = collect();

        foreach ($response->getRows() as $row) {
            $rowResult = [];

            foreach ($row->getDimensionValues() as $i => $dimensionValue) {
                $rowResult[$dimensions[$i]] =
                    $typeCaster->castValue($dimensions[$i], $dimensionValue->getValue());
            }

            foreach ($row->getMetricValues() as $i => $metricValue) {
                $rowResult[$metrics[$i]] =
                    $typeCaster->castValue($metrics[$i], $metricValue->getValue());
            }

            $result->push($rowResult);
        }

        return $result;
    }

    public function runReport(array $request): RunReportResponse
    {
        $cacheName = $this->determineCacheName(func_get_args());

        if ($this->cacheLifeTimeInMinutes === 0) {
            $this->cache->forget($cacheName);
        }

        return $this->cache->remember(
            $cacheName,
            $this->cacheLifeTimeInMinutes,
            function () use ($request) {
                return $this->service->runReport($request);
            },
        );
    }

    public function getAnalyticsService(): BetaAnalyticsDataClient
    {
        return $this->service;
    }

    protected function determineCacheName(array $properties): string
    {
        $hash = md5(serialize($properties));

        return "spatie.laravel-analytics.{$hash}";
    }

    protected function getFormattedMetrics(array $metrics): array
    {
        return collect($metrics)
            ->map(function ($metric) {
                return new Metric(['name' => $metric]);
            })
            ->toArray();
    }

    protected function getFormattedDimensions(array $dimensions): array
    {
        return collect($dimensions)
            ->map(function ($dimension) {
                return new Dimension(['name' => $dimension]);
            })
            ->toArray();
    }
}
