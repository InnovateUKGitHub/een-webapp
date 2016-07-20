<?php

namespace IngestTest\V1\Rest\Delete;

use Elasticsearch\Client;
use Elasticsearch\Namespaces\IndicesNamespace;
use Ingest\V1\Service\IndexService;

/**
 * @covers Ingest\V1\Service\IndexService
 */
class IndexServiceTest extends \PHPUnit_Framework_TestCase
{
    const OPPORTUNITY = 'opportunity';
    const EVENT = 'event';

    /** @var \PHPUnit_Framework_MockObject_MockObject|Client */
    private $clientMock;
    /** @var IndexService */
    private $service;

    public function Setup()
    {
        $this->clientMock = self::getMock(Client::class, [], [], '', false);

        $this->service = new IndexService($this->clientMock);

    }

    public function testCreateIndexAlreadyCreated()
    {
        $indicesMock = self::getMock(IndicesNamespace::class, [], [], '', false);
        $this->clientMock
            ->expects(self::once())
            ->method('indices')
            ->willReturn($indicesMock);
        $indicesMock->expects(self::once())
            ->method('exists')
            ->with(['index' => self::OPPORTUNITY])
            ->willReturn(true);

        $this->service->createIndex(self::OPPORTUNITY);
    }

    public function testCreateIndexOpportunity()
    {
        $indicesMock = self::getMock(IndicesNamespace::class, [], [], '', false);
        $this->clientMock
            ->expects(self::exactly(2))
            ->method('indices')
            ->willReturn($indicesMock);
        $indicesMock->expects(self::once())
            ->method('exists')
            ->with(['index' => self::OPPORTUNITY])
            ->willReturn(false);
        $indicesMock->expects(self::once())
            ->method('create')
            ->with([
                'index' => self::OPPORTUNITY,
                'body' => [
                    'mappings' => [
                        self::OPPORTUNITY => [
                            'properties' => [
                                'id' => [
                                    'type' => 'string',
                                ],
                                'name' => [
                                    'type' => 'string',
                                ],
                                'type' => [
                                    'type' => 'string',
                                ],
                                'opportunity_type' => [
                                    'type' => 'string',
                                ],
                                'country' => [
                                    'type' => 'string',
                                ],
                                'date' => [
                                    'type' => 'date',
                                    'format' => 'strict_date_optional_time||epoch_millis',
                                ],
                                'types' => [
                                    'type' => 'string',
                                ],
                                'description' => [
                                    'type' => 'string',
                                ],
                                'expertise' => [
                                    'type' => 'string',
                                ],
                                'advantage' => [
                                    'type' => 'string',
                                ],
                                'stage' => [
                                    'type' => 'string',
                                ],
                                'stage_reference' => [
                                    'type' => 'string',
                                ],
                                'ipr' => [
                                    'type' => 'string',
                                ],
                                'ipr_reference' => [
                                    'type' => 'string',
                                ],
                            ]
                        ]
                    ]
                ]
            ]);

        $this->service->createIndex(self::OPPORTUNITY);
    }

    public function testCreateIndexEvent()
    {
        $indicesMock = self::getMock(IndicesNamespace::class, [], [], '', false);
        $this->clientMock
            ->expects(self::exactly(2))
            ->method('indices')
            ->willReturn($indicesMock);
        $indicesMock->expects(self::once())
            ->method('exists')
            ->with(['index' => self::EVENT])
            ->willReturn(false);
        $indicesMock->expects(self::once())
            ->method('create')
            ->with([
                'index' => self::EVENT,
                'body' => [
                    'mappings' => [
                        self::EVENT => [
                            'properties' => [
                                'id' => [
                                    'type' => 'long',
                                ],
                                'name' => [
                                    'type' => 'string',
                                ],
                                'type' => [
                                    'type' => 'string',
                                ],
                                'place' => [
                                    'type' => 'string',
                                ],
                                'address' => [
                                    'type' => 'string',
                                ],
                                'date_from' => [
                                    'type' => 'date',
                                    'format' => 'strict_date_optional_time||epoch_millis',
                                ],
                                'date_to' => [
                                    'type' => 'date',
                                    'format' => 'strict_date_optional_time||epoch_millis',
                                ],
                                'description' => [
                                    'type' => 'string',
                                ],
                                'attendee' => [
                                    'type' => 'string',
                                ],
                                'agenda' => [
                                    'type' => 'string',
                                ],
                                'cost' => [
                                    'type' => 'string',
                                ],
                                'topics' => [
                                    'type' => 'string',
                                ],
                                'latitude' => [
                                    'type' => 'float',
                                ],
                                'longitude' => [
                                    'type' => 'float',
                                ],
                            ]
                        ]
                    ]
                ]
            ]);

        $this->service->createIndex(self::EVENT);
    }

    public function testIndex()
    {
        $values = [
            'id' => 1
        ];

        $this->clientMock
            ->expects(self::once())
            ->method('index')
            ->with([
                'body' => $values,
                'index' => self::EVENT,
                'type' => self::EVENT,
                'id' => 1,
            ])
            ->willReturn(['success' => true]);

        self::assertEquals(
            ['success' => true],
            $this->service->index($values, 1, self::EVENT, self::EVENT)
        );
    }
}
