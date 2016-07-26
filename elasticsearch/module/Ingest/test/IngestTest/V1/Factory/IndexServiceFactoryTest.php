<?php

namespace IngestTest\V1\Rest\Delete;

use Ingest\V1\Factory\IndexServiceFactory;
use Ingest\V1\Service\IndexService;

/**
 * @covers Ingest\V1\Factory\IndexServiceFactory
 */
class IndexServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new IndexServiceFactory();

        $serviceLocatorMock = self::getMock(\Zend\ServiceManager\ServiceLocatorInterface::class, [], [], '', false);

        $service = $factory->createService($serviceLocatorMock);
        self::assertInstanceOf(IndexService::class, $service);
    }
}