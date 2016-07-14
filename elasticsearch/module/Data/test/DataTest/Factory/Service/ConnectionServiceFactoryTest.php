<?php

namespace DataTest\Factory\Service;

use Data\Factory\Service\ConnectionServiceFactory;
use Data\Service\ConnectionService;
use Data\Service\HttpService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Data\Factory\Service\ConnectionServiceFactory
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
