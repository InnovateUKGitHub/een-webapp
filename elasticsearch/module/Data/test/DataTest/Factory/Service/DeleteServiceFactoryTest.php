<?php

namespace DataTest\Factory\Service;

use Data\Factory\Service\DeleteServiceFactory;
use Data\Service\ConnectionService;
use Data\Service\DeleteService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Data\Factory\Service\DeleteServiceFactory
 */
class DeleteServiceFactoryTest extends \PHPUnit_Framework_TestCase
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
            DeleteService::class,
            (new DeleteServiceFactory())->createService($serviceLocator)
        );
    }
}
