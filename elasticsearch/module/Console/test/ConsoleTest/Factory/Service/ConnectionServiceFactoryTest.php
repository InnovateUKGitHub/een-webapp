<?php

namespace ConsoleTest\Factory\Service;

use Console\Factory\Service\ConnectionServiceFactory;
use Console\Service\ConnectionService;
use Console\Service\HttpService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Console\Factory\Service\ConnectionServiceFactory
 */
class ConnectionServiceFactoryTest extends \PHPUnit_Framework_TestCase
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
            ->willReturn($this->getMock(HttpService::class, [], [], '', false));

        self::assertInstanceOf(
            ConnectionService::class,
            (new ConnectionServiceFactory())->createService($serviceLocator)
        );
    }
}
