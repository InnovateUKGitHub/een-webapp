<?php
namespace Drupal\elastic_search\Test\Service;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\elastic_search\Service\ElasticSearchService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

/**
 * @covers Drupal\elastic_search\Service\ElasticSearchService
 */
class ElasticSearchServiceTest extends UnitTestCase
{
    protected function Setup()
    {
        parent::setUp();

        $configMock = self::getMock(ImmutableConfig::class, [], [], '', false);
        $containerMock = self::getMock(ContainerInterface::class, [], [], '', false);
        $containerMock->expects(self::once())
            ->method('get')
            ->with('config.factory')
            ->willReturn($configMock);
        $configMock->expects(self::at(0))
            ->method('get')
            ->with('elastic_search.settings')
            ->willReturn($configMock);
        $configMock->expects(self::at(1))
            ->method('get')
            ->with('server')
            ->willReturn('my_server');

        \Drupal::setContainer($containerMock);
    }

    public function testSuccess()
    {
        $service = new ElasticSearchService();

        self::assertInstanceOf(ElasticSearchService::class, $service->setMethod(Request::METHOD_POST));
        self::assertInstanceOf(ElasticSearchService::class, $service->setQueryParams(['type' => 'A Type']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setUrl('/search'));
        self::assertInstanceOf(ElasticSearchService::class, $service->setBody(['search' => 'A Search']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchFrom(0));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchSize(10));

        $clientMock = self::getMock(Client::class, [], [], '', false);
        $responseMock = self::getMock(Response::class, [], [], '', false);
        self::assertInstanceOf(ElasticSearchService::class, $service->setClient($clientMock));

        $clientMock->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);
        $responseMock->expects(self::once())
            ->method('isSuccess')
            ->willReturn(true);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"success": true}');

        self::assertEquals(['success' => true], $service->sendRequest());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @expectedExceptionMessage Not Found
     */
    public function testError404()
    {
        $service = new ElasticSearchService();

        self::assertInstanceOf(ElasticSearchService::class, $service->setMethod(Request::METHOD_POST));
        self::assertInstanceOf(ElasticSearchService::class, $service->setQueryParams(['type' => 'A Type']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setUrl('/search'));
        self::assertInstanceOf(ElasticSearchService::class, $service->setBody(['search' => 'A Search']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchFrom(0));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchSize(10));

        $clientMock = self::getMock(Client::class, [], [], '', false);
        $responseMock = self::getMock(Response::class, [], [], '', false);
        self::assertInstanceOf(ElasticSearchService::class, $service->setClient($clientMock));

        $clientMock->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);
        $responseMock->expects(self::once())
            ->method('isSuccess')
            ->willReturn(false);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::STATUS_CODE_404);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"detail": "Not Found"}');

        $service->sendRequest();
    }

    public function testError422()
    {
        $service = new ElasticSearchService();

        self::assertInstanceOf(ElasticSearchService::class, $service->setMethod(Request::METHOD_POST));
        self::assertInstanceOf(ElasticSearchService::class, $service->setQueryParams(['type' => 'A Type']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setUrl('/search'));
        self::assertInstanceOf(ElasticSearchService::class, $service->setBody(['search' => 'A Search']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchFrom(0));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchSize(10));

        $clientMock = self::getMock(Client::class, [], [], '', false);
        $responseMock = self::getMock(Response::class, [], [], '', false);
        self::assertInstanceOf(ElasticSearchService::class, $service->setClient($clientMock));

        $clientMock->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);
        $responseMock->expects(self::once())
            ->method('isSuccess')
            ->willReturn(false);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::STATUS_CODE_422);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"validation_messages": "An error as occurred"}');

        self::assertEquals(['error' => 'An error as occurred'], $service->sendRequest());
    }

    public function testError500()
    {
        $service = new ElasticSearchService();

        self::assertInstanceOf(ElasticSearchService::class, $service->setMethod(Request::METHOD_POST));
        self::assertInstanceOf(ElasticSearchService::class, $service->setQueryParams(['type' => 'A Type']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setUrl('/search'));
        self::assertInstanceOf(ElasticSearchService::class, $service->setBody(['search' => 'A Search']));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchFrom(0));
        self::assertInstanceOf(ElasticSearchService::class, $service->setSearchSize(10));

        $clientMock = self::getMock(Client::class, [], [], '', false);
        $responseMock = self::getMock(Response::class, [], [], '', false);
        self::assertInstanceOf(ElasticSearchService::class, $service->setClient($clientMock));

        $clientMock->expects(self::once())
            ->method('send')
            ->willReturn($responseMock);
        $responseMock->expects(self::once())
            ->method('isSuccess')
            ->willReturn(false);
        $responseMock->expects(self::once())
            ->method('getStatusCode')
            ->willReturn(Response::STATUS_CODE_500);
        $responseMock->expects(self::once())
            ->method('getBody')
            ->willReturn('{"detail": "An error as occurred"}');

        self::assertEquals(['error' => 'An error as occurred'], $service->sendRequest());
    }
}