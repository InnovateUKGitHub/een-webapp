<?php

namespace ConsoleTest\Controller;

use Elasticsearch\Client;
use Search\V1\ElasticSearch\Service\QueryService;

/**
 * @covers Search\V1\ElasticSearch\Service\QueryService
 */
class QueryServiceTest extends \PHPUnit_Framework_TestCase
{
    const INDEX = 'index';
    const TYPE = 'type';

    public function testSearch()
    {
        $params = [
            'from' => 0,
            'size' => 10,
            'search' => 'Some Search',
            'sort' => [
                ['date.timestamp' => 'desc']
            ],
            'source' => ['name', 'description']
        ];

        $elasticSearchMock = self::getMock(Client::class, [], [], '', false);
        $elasticSearchMock->expects(self::once())
            ->method('search')
            ->with([
                'index' => self::INDEX,
                'type'  => self::TYPE,
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
            ])
            ->willReturn([
                'hits' => [
                    'total' => 100,
                    'hits' => [
                        [
                            'index' => self::INDEX,
                            'type' => self::TYPE,
                            '_source' => [
                                'name' => 'Name',
                                'description' => 'Description',
                            ]
                        ]
                    ]
                ],
            ]);

        $service = new QueryService($elasticSearchMock);

        self::assertEquals(
            [
                'total' => 100,
                'results' => [
                    [
                        'index' => self::INDEX,
                        'type' => self::TYPE,
                        '_source' => [
                            'name' => 'Name',
                            'description' => 'Description',
                        ]
                    ]
                ]
            ],
            $service->search($params, self::INDEX, self::TYPE)
        );
    }
}
