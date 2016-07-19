<?php

namespace Ingest\V1\Service;

use Elasticsearch\Client;

class IndexService
{
    /** @var Client */
    private $elasticSearch;

    /**
     * @param Client       $elasticSearch
     */
    public function __construct(Client $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    public function createIndex($index)
    {
        if ($this->elasticSearch->indices()->exists(['index' => $index]) === true) {
            return;
        }
    }

    public function index($values, $id, $index, $type)
    {
        $params = [
            'body' => $values,
            'index' => $index,
            'type' => $type,
            'id' => $id
        ];

        return $this->elasticSearch->index($params);
    }
}