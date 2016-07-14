<?php

namespace DataTest\Service;

use Data\Service\HttpService;
use Zend\Http\Client;
use Zend\Http\Exception\InvalidArgumentException;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Json\Server\Exception\HttpException;

/**
 * @covers Data\Service\HttpService
 */
class HttpServiceTest extends \PHPUnit_Framework_TestCase
{
    const HTTP_SCHEME = 'http';
    const CONFIG = [
        'server' => 'test',
        'port' => '80'
    ];
    const PATH_TO_SERVICE = 'path-to-service';
    const REQUEST_BODY = 'request-body';

    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $clientMock;

    public function Setup()
    {
        $this->clientMock = self::getMock(Client::class, [], [], '', false);
    }
    
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config file is incorrect. Please specify the server
     */
    public function testNoConfigThrowError()
    {
        new HttpService($this->clientMock, []);
    }
    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The config file is incorrect. Please specify the port
     */
    public function testNoPortConfigThrowError()
    {
        new HttpService($this->clientMock, ['server' => 'test']);
    }

    public function testGetterSetter()
    {
        $service = new HttpService($this->clientMock, self::CONFIG);

        $service->setHttpMethod(Request::METHOD_GET);
        self::assertEquals(Request::METHOD_GET, $service->getHttpMethod());

        $service->setPathToService(self::PATH_TO_SERVICE);
        self::assertEquals(self::PATH_TO_SERVICE, $service->getPathToService());

        $service->setRequestBody(self::REQUEST_BODY);
        self::assertEquals(self::REQUEST_BODY, $service->getRequestBody());
    }

    /**
     * @dataProvider testSetHttpMethodProvider
     *
     * @param      $method
     * @param bool $exception
     */
    public function testSetHttpMethod($method, $exception = false)
    {
        $service = new HttpService($this->clientMock, self::CONFIG);

        if ($exception === true) {
            self::setExpectedException(InvalidArgumentException::class, 'Unsupported HTTP method ' . $method);
        }

        $service->setHttpMethod($method);
    }

    public function testSetHttpMethodProvider()
    {
        return [
            [Request::METHOD_GET],
            [Request::METHOD_PUT],
            [Request::METHOD_POST],
            [Request::METHOD_DELETE],
            [Request::METHOD_CONNECT, true],
            [Request::METHOD_HEAD, true],
            [Request::METHOD_OPTIONS, true],
            [Request::METHOD_PATCH, true],
            [Request::METHOD_PROPFIND, true],
            [Request::METHOD_TRACE, true],
        ];
    }

    public function testExecute()
    {
        $service = new HttpService($this->clientMock, self::CONFIG);

        $this->clientMock
            ->expects(self::once())
            ->method('setHeaders')
            ->with([
                'Accept'       => 'application/json',
                'Content-type' => 'application/json'
            ]);
        $this->clientMock
            ->expects(self::once())
            ->method('setMethod')
            ->with(Request::METHOD_POST);
        $this->clientMock
            ->expects(self::once())
            ->method('setUri')
            ->with(self::HTTP_SCHEME . '://' . self::CONFIG['server'] . ':' . self::CONFIG['port'] . '/' . self::PATH_TO_SERVICE);
        $this->clientMock
            ->expects(self::once())
            ->method('setRawBody')
            ->with(self::REQUEST_BODY);

        $responseMock = self::getMock(Response::class, [], [], '', false);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"success": 1}');

        $this->clientMock
            ->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);

        $service->setHttpMethod(Request::METHOD_POST);
        $service->setPathToService(self::PATH_TO_SERVICE);
        $service->setRequestBody(self::REQUEST_BODY);

        self::assertEquals(['success' => true], $service->execute());
    }

    public function testExecuteThrowException()
    {
        $service = new HttpService($this->clientMock, self::CONFIG);

        $this->clientMock
            ->expects(self::once())
            ->method('setHeaders')
            ->with([
                'Accept'       => 'application/json',
                'Content-type' => 'application/json'
            ]);
        $this->clientMock
            ->expects(self::once())
            ->method('setMethod')
            ->with(Request::METHOD_POST);
        $this->clientMock
            ->expects(self::once())
            ->method('setUri')
            ->with(self::HTTP_SCHEME . '://' . self::CONFIG['server'] . ':' . self::CONFIG['port'] . '/' . self::PATH_TO_SERVICE);
        $this->clientMock
            ->expects(self::once())
            ->method('setRawBody')
            ->with(self::REQUEST_BODY);

        $responseMock = self::getMock(Response::class, [], [], '', false);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"success" => 1}');

        $this->clientMock
            ->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);

        $service->setHttpMethod(Request::METHOD_POST);
        $service->setPathToService(self::PATH_TO_SERVICE);
        $service->setRequestBody(self::REQUEST_BODY);

        self::setExpectedException(HttpException::class, 'Malformed JSON response: {"success" => 1}');

        $service->execute();
    }
}
