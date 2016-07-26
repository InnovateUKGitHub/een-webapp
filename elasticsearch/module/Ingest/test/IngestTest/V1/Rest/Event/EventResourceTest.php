<?php

namespace IngestTest\V1\Rest\Event;

use Ingest\V1\Rest\Event\EventResource;
use Ingest\V1\Service\IndexService;
use Zend\Http\Response;
use Zend\InputFilter\InputFilter;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\ResourceEvent;

/**
 * @covers Ingest\V1\Rest\Event\EventResource
 */
class EventResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|IndexService */
    private $service;
    /** @var EventResource */
    private $resource;

    public function Setup()
    {
        $this->service = self::getMock(IndexService::class, [], [], '', false);
        $this->resource = new EventResource($this->service);
    }

    public function testCreateEvent()
    {
        $input = self::getMock(InputFilter::class, [], [], '', false);

        $event = new ResourceEvent();
        $event->setName('create');
        $event->setInputFilter($input);

        $input->expects(self::once())
            ->method('getValues')
            ->willReturn(['id' => 1]);

        $this->service
            ->expects(self::once())
            ->method('createIndex')
            ->with(EventResource::ES_INDEX);

        $this->service
            ->expects(self::once())
            ->method('index')
            ->with(['id' => 1], 1, EventResource::ES_INDEX, EventResource::ES_INDEX)
            ->willReturn(['success' => true]);

        $response = $this->resource->dispatch($event);
        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('{"success":true}', $response->getContent());
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
            ['delete', 'The DELETE method has not been defined for individual resources'],
            ['deleteList', 'The DELETE method has not been defined for collections'],
            ['fetch', 'The GET method has not been defined for individual resources'],
            ['fetchAll', 'The GET method has not been defined for collections'],
            ['patch', 'The PATCH method has not been defined for individual resources'],
            ['replaceList', 'The PUT method has not been defined for collections'],
            ['update', 'The PUT method has not been defined for individual resources'],
        ];
    }
}