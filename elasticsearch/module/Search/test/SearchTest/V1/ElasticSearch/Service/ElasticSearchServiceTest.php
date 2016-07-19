<?php

namespace ConsoleTest\Controller;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\ElasticSearch\Service\QueryService;

/**
 * @covers Search\V1\ElasticSearch\Service\ElasticSearchService
 */
class ElasticSearchServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSearchOpportunity()
    {
        $params = [
            'from' => 0,
            'size' => 10,
            'search' => 'Some Search'
        ];

        $queryServiceMock = self::getMock(QueryService::class, [], [], '', false);
        $queryServiceMock->expects(self::once())
            ->method('search')
            ->with($params, ElasticSearchService::OPPORTUNITY, ElasticSearchService::OPPORTUNITY)
            ->willReturn(['success' => true]);

        $service = new ElasticSearchService($queryServiceMock);

        self::assertEquals(['success' => true], $service->searchOpportunity($params));
    }

    public function testSearchEvent()
    {
        $params = [
            'from' => 0,
            'size' => 10,
            'search' => 'Some Search'
        ];

        $queryServiceMock = self::getMock(QueryService::class, [], [], '', false);
        $queryServiceMock->expects(self::once())
            ->method('search')
            ->with($params, ElasticSearchService::EVENT, ElasticSearchService::EVENT)
            ->willReturn(['success' => true]);

        $service = new ElasticSearchService($queryServiceMock);

        self::assertEquals(['success' => true], $service->searchEvent($params));
    }
}
