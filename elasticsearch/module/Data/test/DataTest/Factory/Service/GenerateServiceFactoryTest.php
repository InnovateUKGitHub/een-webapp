<?php

namespace DataTest\Factory\Service;

use Data\Service\ConnectionService;
use Data\Service\GenerateService;
use Data\Factory\Service\GenerateServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Data\Factory\Service\GenerateServiceFactory
 */
class GenerateServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        /* @var $serviceLocator ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $serviceLocator = self::getMock(
            ServiceLocatorInterface::class,
            ['get', 'has'],
            [],
            '',
            false
        );

        $serviceLocator
            ->expects(self::once())
            ->method('get')
            ->willReturn($this->getMock(ConnectionService::class, [], [], '', false));

        self::assertInstanceOf(
            GenerateService::class,
            (new GenerateServiceFactory())->createService($serviceLocator)
        );
    }
}
