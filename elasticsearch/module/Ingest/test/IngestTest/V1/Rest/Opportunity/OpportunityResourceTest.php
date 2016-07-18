<?php

namespace IngestTest\V1\Rest\Opportunity;

use Elasticsearch\Client;
use Ingest\V1\Rest\Opportunity\OpportunityResource;
use Zend\InputFilter\InputFilter;
use ZF\ApiProblem\ApiProblem;
use ZF\Rest\ResourceEvent;

/**
 * @covers Ingest\V1\Rest\Opportunity\OpportunityResource
 */
class OpportunityResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $client;
    /** @var OpportunityResource */
    private $resource;

    public function Setup()
    {
        $this->client = self::getMock(Client::class, [], [], '', false);
        $this->resource = new OpportunityResource($this->client);
    }

    public function testCreateOpportunity()
    {
        $input = self::getMock(InputFilter::class, [], [], '', false);

        $event = new ResourceEvent();
        $event->setName('create');
        $event->setInputFilter($input);

        $input->expects(self::once())
            ->method('getValues')
            ->willReturn(['id' => 1]);

        $this->client
            ->expects(self::once())
            ->method('index')
            ->with([
                'body' => ['id' => 1],
                'index' => OpportunityResource::ES_INDEX,
                'type' => OpportunityResource::ES_TYPE,
                'id' => 1,
            ])
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