<?php

namespace ConsoleTest\Factory\Controller;

use Console\Controller\GenerateController;
use Console\Factory\Controller\GenerateControllerFactory;
use Console\Service\DeleteService;
use Console\Service\GenerateService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Console\Factory\Controller\GenerateControllerFactory
 */
class GenerateControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        /* @var $serviceLocator ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject */
        $serviceLocator = self::getMock(
            ServiceLocatorInterface::class,
            ['getServiceLocator', 'get', 'has'],
            [],
            '',
            false
        );

        $serviceLocator
            ->expects(self::at(0))
            ->method('getServiceLocator')
            ->willReturn($serviceLocator);

        $serviceLocator
            ->expects(self::at(1))
            ->method('get')
            ->willReturn($this->getMock(GenerateService::class, [], [], '', false));
        $serviceLocator
            ->expects(self::at(2))
            ->method('get')
            ->willReturn($this->getMock(DeleteService::class, [], [], '', false));

        self::assertInstanceOf(
            GenerateController::class,
            (new GenerateControllerFactory())->createService($serviceLocator)
        );
    }
}
