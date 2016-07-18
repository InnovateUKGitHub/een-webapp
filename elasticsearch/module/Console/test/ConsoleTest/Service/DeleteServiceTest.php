<?php

namespace ConsoleTest\Service;

use Console\Service\ConnectionService;
use Console\Service\DeleteService;
use Zend\Http\Request;

/**
 * @covers Console\Service\DeleteService
 */
class DeleteServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testDelete()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|ConnectionService $connectionServiceMock */
        $connectionServiceMock = self::getMock(ConnectionService::class, [], [], '', false);

        $service = new DeleteService($connectionServiceMock);

        $connectionServiceMock
            ->expects(self::once())
            ->method('execute')
            ->with(Request::METHOD_DELETE, 'delete/index')
            ->willReturn([]);

        $service->delete('index');
    }
}
