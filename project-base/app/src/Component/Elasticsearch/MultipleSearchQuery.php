<?php

declare(strict_types=1);

namespace App\Component\Elasticsearch;

/**
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/search-multi-search.html
 */
class MultipleSearchQuery
{
    /**
     * @var array<int, mixed>
     */
    private array $body;

    /**
     * @param string $indexName
     * @param \App\Model\Product\Search\FilterQuery[] $filterQueries
     */
    public function __construct(private string $indexName, array $filterQueries)
    {
        $this->body = $this->getBody($filterQueries);
    }

    /**
     * @return array
     */
    public function getQuery(): array
    {
        return [
            'index' => $this->indexName,
            'body' => $this->body,
        ];
    }

    /**
     * @param \App\Model\Product\Search\FilterQuery[] $filterQueries
     * @return array
     */
    private function getBody(array $filterQueries): array
    {
        $body = [];

        foreach ($filterQueries as $filterQuery) {
            $body[] = [];
            $body[] = $filterQuery->getQuery()['body'];
        }

        return $body;
    }
}
