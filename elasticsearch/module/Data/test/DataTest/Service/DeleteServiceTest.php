<?php

namespace DataTest\Service;

use Data\Service\ConnectionService;
use Data\Service\DeleteService;
use Zend\Http\Request;

/**
 * @covers Data\Service\DeleteService
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
            ->with(Request::METHOD_POST, 'delete', ['index' => 'index'])
            ->willReturn([]);

        $service->delete('index');
    }
}
