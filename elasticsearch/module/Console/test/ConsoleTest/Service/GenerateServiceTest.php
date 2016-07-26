<?php

namespace ConsoleTest\Service;

use Console\Service\ConnectionService;
use Console\Service\GenerateService;
use Faker\Generator;
use Zend\Http\Request;

/**
 * @covers Console\Service\GenerateService
 */
class GenerateServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerateAll()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $faker = \Faker\Factory::create();

        $service = new GenerateService($connectionServiceMock, $faker);

        $connectionServiceMock
            ->expects(self::at(0))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::OPPORTUNITY)
            ->willReturn([]);
        $connectionServiceMock
            ->expects(self::at(1))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::EVENT)
            ->willReturn([]);

        $service->generate(GenerateService::ALL, 1);
    }

    public function testGenerateOpportunity()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $faker = \Faker\Factory::create();

        $service = new GenerateService($connectionServiceMock, $faker);

        $connectionServiceMock
            ->expects(self::exactly(1))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::OPPORTUNITY)
            ->willReturn([]);

        $service->generate(GenerateService::OPPORTUNITY, 1);
    }

    public function testGenerateEvent()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $faker = \Faker\Factory::create();

        $service = new GenerateService($connectionServiceMock, $faker);

        $connectionServiceMock
            ->expects(self::exactly(1))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::EVENT)
            ->willReturn([]);

        $service->generate(GenerateService::EVENT, 1);
    }

    public function testGenerateNoIndexSpecified()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $faker = \Faker\Factory::create();

        $service = new GenerateService($connectionServiceMock, $faker);

        $connectionServiceMock
            ->expects(self::at(0))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::OPPORTUNITY)
            ->willReturn([]);
        $connectionServiceMock
            ->expects(self::at(1))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::EVENT)
            ->willReturn([]);

        $service->generate(null, 1);
    }

    public function testGenerateInvalidIndexSpecified()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $faker = \Faker\Factory::create();

        $service = new GenerateService($connectionServiceMock, $faker);

        $connectionServiceMock
            ->expects(self::at(0))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::OPPORTUNITY)
            ->willReturn([]);
        $connectionServiceMock
            ->expects(self::at(1))
            ->method('execute')
            ->with(Request::METHOD_POST, GenerateService::EVENT)
            ->willReturn([]);

        $service->generate('InvalidIndex', 1);
    }
}
