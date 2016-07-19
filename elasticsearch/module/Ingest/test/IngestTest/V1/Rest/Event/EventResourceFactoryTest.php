<?php

namespace IngestTest\V1\Rest\Event;

use Ingest\V1\Rest\Event\EventResource;
use Ingest\V1\Rest\Event\EventResourceFactory;
use Ingest\V1\Service\IndexService;
use Zend\Di\ServiceLocatorInterface;

/**
 * @covers Ingest\V1\Rest\Event\EventResourceFactory
 */
class EventResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new EventResourceFactory();

        $mockIndexService = self::getMock(IndexService::class, [], [], '', false);
        $serviceLocatorMock = self::getMock(ServiceLocatorInterface::class, [], [], '', false);
        $serviceLocatorMock->expects(self::once())
            ->method('get')
            ->with(IndexService::class)
            ->willReturn($mockIndexService);

        $controller = $factory->__invoke($serviceLocatorMock);
        self::assertInstanceOf(EventResource::class, $controller);
    }
}