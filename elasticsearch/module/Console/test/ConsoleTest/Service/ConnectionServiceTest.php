<?php

namespace ConsoleTest\Service;

use Console\Service\ConnectionService;
use Console\Service\HttpService;
use Zend\Http\Request;

/**
 * @covers Console\Service\ConnectionService
 */
class ConnectionServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpService $httpServiceMock */
        $httpServiceMock = self::getMock(HttpService::class, [], [], '', false);

        $service = new ConnectionService($httpServiceMock);

        $httpServiceMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn([]);

        self::assertEquals([], $service->execute(Request::METHOD_GET, 'path'));
    }

    public function testExecuteWithBody()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|HttpService $httpServiceMock */
        $httpServiceMock = self::getMock(HttpService::class, ['execute'], [], '', false);

        $service = new ConnectionService($httpServiceMock);

        $httpServiceMock
            ->expects(self::once())
            ->method('execute')
            ->willReturn([]);

        self::assertEquals([], $service->execute(Request::METHOD_GET, 'path', ['body' => 'content']));
    }
}
