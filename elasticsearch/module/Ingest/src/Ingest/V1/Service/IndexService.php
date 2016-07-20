<?php

namespace Ingest\V1\Service;

use Elasticsearch\Client;
use Ingest\V1\Rest\Event\EventResource;
use Ingest\V1\Rest\Opportunity\OpportunityResource;

class IndexService
{
    /** @var Client */
    private $elasticSearch;

    /**
     * @param Client $elasticSearch
     */
    public function __construct(Client $elasticSearch)
    {
        $this->elasticSearch = $elasticSearch;
    }

    public function createIndex($index)
    {
        if ($this->elasticSearch->indices()->exists(['index' => $index]) === true) {
            return true;
        }
        switch ($index) {
            case OpportunityResource::ES_INDEX:
                $this->createOpportunityIndex();
                break;
            case EventResource::ES_INDEX:
                $this->createEventIndex();
                break;
        }
        return false;
    }

    private function createOpportunityIndex()
    {
        $params = [
            'index' => OpportunityResource::ES_INDEX,
            'body' => [
                'mappings' => [
                    OpportunityResource::ES_TYPE => [
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
        ];
        $this->elasticSearch->indices()->create($params);
    }

    private function createEventIndex()
    {
        $params = [
            'index' => EventResource::ES_INDEX,
            'body' => [
                'mappings' => [
                    EventResource::ES_TYPE => [
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
        ];
        $this->elasticSearch->indices()->create($params);
    }

    public function index($values, $id, $index, $type)
    {
        $params = [
            'body' => $values,
            'index' => $index,
            'type' => $type,
            'id' => $id
        ];

        return $this->elasticSearch->index($params);
    }
}