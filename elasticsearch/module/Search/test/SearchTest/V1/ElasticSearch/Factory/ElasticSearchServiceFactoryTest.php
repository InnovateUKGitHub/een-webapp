<?php

namespace SearchTest\V1\Rpc\Event;

use Search\V1\ElasticSearch\Factory\ElasticSearchServiceFactory;
use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\ElasticSearch\Service\QueryService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Search\V1\ElasticSearch\Factory\ElasticSearchServiceFactory
 */
class ElasticSearchServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $queryServiceMock = self::getMock(QueryService::class, [], [], '', false);

        $serviceLocatorMock = self::getMock(ServiceLocatorInterface::class, [], [], '', false);
        $serviceLocatorMock->expects(self::once())
            ->method('get')
            ->with(QueryService::class)
            ->willReturn($queryServiceMock);

        $factory = new ElasticSearchServiceFactory();

        $service = $factory->createService($serviceLocatorMock);
        self::assertInstanceOf(ElasticSearchService::class, $service);
    }
}