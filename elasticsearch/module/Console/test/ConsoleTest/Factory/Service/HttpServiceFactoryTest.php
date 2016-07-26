<?php

namespace ConsoleTest\Factory\Service;

use Console\Factory\Service\HttpServiceFactory;
use Console\Service\HttpService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Exception\InvalidArgumentException;

/**
 * @covers Console\Factory\Service\HttpServiceFactory
 */
class HttpServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryWithCorrectConfigFile()
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
            ->willReturn([
                HttpServiceFactory::CONFIG_ELASTIC_SEARCH => [
                    HttpService::SERVER => '',
                    HttpService::PORT => '',
                ]
            ]);

        self::assertInstanceOf(
            HttpService::class,
            (new HttpServiceFactory())->createService($serviceLocator)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config file is incorrect. Please specify the server information
     */
    public function testFactoryWithNoElasticSearchConfig()
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
            ->willReturn([]);

        self::assertInstanceOf(
            HttpService::class,
            (new HttpServiceFactory())->createService($serviceLocator)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config file is incorrect. Please specify the server
     */
    public function testFactoryWithNoServerInConfig()
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
            ->willReturn([
                HttpServiceFactory::CONFIG_ELASTIC_SEARCH => [
                    HttpService::PORT => '',
                ]
            ]);

        self::assertInstanceOf(
            HttpService::class,
            (new HttpServiceFactory())->createService($serviceLocator)
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config file is incorrect. Please specify the port
     */
    public function testFactoryWithNoPortInConfig()
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
            ->willReturn([
                HttpServiceFactory::CONFIG_ELASTIC_SEARCH => [
                    HttpService::SERVER => '',
                ]
            ]);

        self::assertInstanceOf(
            HttpService::class,
            (new HttpServiceFactory())->createService($serviceLocator)
        );
    }
}
