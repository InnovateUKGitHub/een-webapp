<?php

namespace IngestTest\V1\Rest\Opportunity;

use Ingest\V1\Rest\Opportunity\OpportunityResource;
use Ingest\V1\Rest\Opportunity\OpportunityResourceFactory;
use Ingest\V1\Service\IndexService;
use Zend\Di\ServiceLocatorInterface;

/**
 * @covers Ingest\V1\Rest\Opportunity\OpportunityResourceFactory
 */
class OpportunityResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new OpportunityResourceFactory();

        $mockIndexService = self::getMock(IndexService::class, [], [], '', false);
        $serviceLocatorMock = self::getMock(ServiceLocatorInterface::class, [], [], '', false);
        $serviceLocatorMock->expects(self::once())
            ->method('get')
            ->with(IndexService::class)
            ->willReturn($mockIndexService);

        $controller = $factory->__invoke($serviceLocatorMock);
        self::assertInstanceOf(OpportunityResource::class, $controller);
    }
}