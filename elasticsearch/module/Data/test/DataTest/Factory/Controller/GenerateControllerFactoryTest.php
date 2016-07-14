<?php

namespace DataTest\Factory\Controller;

use Data\Controller\GenerateController;
use Data\Factory\Controller\GenerateControllerFactory;
use Data\Service\DeleteService;
use Data\Service\GenerateService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Data\Factory\Controller\GenerateControllerFactory
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
