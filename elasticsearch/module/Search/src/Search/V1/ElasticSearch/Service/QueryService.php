<?php

namespace Search\V1\ElasticSearch\Service;

use Elasticsearch\Client;

class QueryService
{
    const ASC = 'asc';
    const DESC = 'desc';

    /** @var Client */
    private $elasticSearch;

    /**
     * ElasticSearchService constructor.
     */
    public function __construct(Client $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    public function search($params, $index, $type)
    {
        $query = [
            'index' => $index,
            'type'  => $type,
            'from'  => $params['from'],
            'size'  => $params['size'],
            'body'  => [
                'query'   => [
                    'bool' => [
                        'must' => [
                            'query_string' => [
                                'default_field' => 'name',
                                'query'         => '*' . $params['search'] . '*',
                            ],
                        ],
                    ],
                ],
                'sort'    => $params['sort'],
                '_source' => $params['source'],
            ],
        ];

        return $this->convertResult($this->elasticSearch->search($query));
    }

    public function convertResult($results)
    {
        return [
            'total' => $results['hits']['total'],
            'results' => $results['hits']['hits'],
        ];
    }
}
