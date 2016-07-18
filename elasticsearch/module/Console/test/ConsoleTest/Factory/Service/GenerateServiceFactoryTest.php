<?php

namespace ConsoleTest\Factory\Service;

use Console\Service\ConnectionService;
use Console\Service\GenerateService;
use Console\Factory\Service\GenerateServiceFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Console\Factory\Service\GenerateServiceFactory
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
