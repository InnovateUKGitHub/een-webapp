<?php
namespace Search\V1\ElasticSearch\Service;

use Elasticsearch\Client;

/**
 * Class ElasticSearchService
 *
 * @package Search\V1\ElasticSearch\Service
 */
class ElasticSearchService
{
    const OPPORTUNITY = 'opportunity';
    const EVENT = 'event';

    /** @var Client */
    private $elasticSearch;

    /**
     * ElasticSearchService constructor.
     */
    public function __construct(Client $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    public function searchOpportunity($params)
    {
        $base = [
            'index' => self::OPPORTUNITY,
            'type'  => self::OPPORTUNITY,
            'from'  => $params['start'],
            'size'  => $params['length'],
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'query_string' => [
                                'query'                  => '*' . $params['search'] . '*',
                                'allow_leading_wildcard' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->convertResult($this->elasticSearch->search($base));
    }

    public function searchEvent($params)
    {

        $base = [
            'index' => self::EVENT,
            'type'  => self::EVENT,
            'from'  => $params['start'],
            'size'  => $params['length'],
            'body'  => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'query_string' => [
                                'query'                  => '*' . $params['search'] . '*',
                                'allow_leading_wildcard' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->convertResult($this->elasticSearch->search($base));
    }

    private function convertResult($results)
    {
        return [
            'total' => $results['hits']['total'],
            'result' => $results['hits']['hits'],
        ];
    }
}
