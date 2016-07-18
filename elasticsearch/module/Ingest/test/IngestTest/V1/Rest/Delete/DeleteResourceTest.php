<?php

namespace IngestTest\V1\Rest\Delete;

use Elasticsearch\Namespaces\IndicesNamespace;
use Ingest\V1\Rest\Delete\DeleteResource;
use Elasticsearch\Client;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\ResourceEvent;

/**
 * @covers Ingest\V1\Rest\Delete\DeleteResource
 */
class DeleteResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;
    /** @var \PHPUnit_Framework_MockObject_MockObject|IndicesNamespace */
    private $indices;
    /** @var DeleteResource */
    private $resource;

    public function Setup()
    {
        $this->client = self::getMock(Client::class, [], [], '', false);
        $this->indices = self::getMock(IndicesNamespace::class, [], [], '', false);
        $this->resource = new DeleteResource($this->client);
    }

    public function testDeleteEvent()
    {
        $event = new ResourceEvent();
        $event->setName('delete');
        $event->setParam('id', 'event');

        $this->client
            ->expects(self::once())
            ->method('indices')
            ->willReturn($this->indices);

        $this->indices
            ->expects(self::once())
            ->method('delete')
            ->with(['index' => 'event'])
            ->willReturn(['success' => true]);

        self::assertEquals(['success' => true], $this->resource->dispatch($event));
    }

    public function testDeleteAll()
    {
        $event = new ResourceEvent();
        $event->setName('delete');
        $event->setParam('id', 'all');

        $this->client
            ->expects(self::once())
            ->method('indices')
            ->willReturn($this->indices);

        $this->indices
            ->expects(self::once())
            ->method('delete')
            ->with(['index' => '*'])
            ->willReturn(['success' => true]);

        self::assertEquals(['success' => true], $this->resource->dispatch($event));
    }

    /**
     * @dataProvider dataProviderNotImplementedFunctionsThrowsError
     *
     * @param string $action
     * @param string $errorMessage
     */
    public function testNotImplementedFunctionsThrowsError($action, $errorMessage)
    {
        $event = new ResourceEvent();
        $event->setName($action);

        $result = $this->resource->dispatch($event);
        self::assertInstanceOf(ApiProblem::class, $result);
        $result = $result->toArray();
        self::assertEquals('Method Not Allowed', $result['title']);
        self::assertEquals(405, $result['status']);
        self::assertEquals($errorMessage, $result['detail']);
    }

    public function dataProviderNotImplementedFunctionsThrowsError()
    {
        return [
            ['create', 'The POST method has not been defined for individual resources'],
            ['deleteList', 'The DELETE method has not been defined for collections'],
            ['fetch', 'The GET method has not been defined for individual resources'],
            ['fetchAll', 'The GET method has not been defined for collections'],
            ['patch', 'The PATCH method has not been defined for individual resources'],
            ['replaceList', 'The PUT method has not been defined for collections'],
            ['update', 'The PUT method has not been defined for individual resources'],
        ];
    }
}