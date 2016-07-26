<?php

namespace SearchTest\V1\Rpc\Event;

use Search\V1\ElasticSearch\Factory\QueryServiceFactory;
use Search\V1\ElasticSearch\Service\QueryService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Search\V1\ElasticSearch\Factory\QueryServiceFactory
 */
class QueryServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceLocatorMock = self::getMock(ServiceLocatorInterface::class, [], [], '', false);
        $factory = new QueryServiceFactory();
        $service = $factory->createService($serviceLocatorMock);

        self::assertInstanceOf(QueryService::class, $service);
    }
}