<?php

namespace Ingest\V1\Rest\Event;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Zend\Di\ServiceLocator;

/**
 * Class EventResourceFactory
 *
 * @package Ingest\V1\Rest\Event
 */
class EventResourceFactory
{
    /**
     * @param ServiceLocator $services
     *
     * @return EventResource
     */
    public function __invoke($services)
    {
        $elasticSearch = ClientBuilder::create()->build();
        
        return new EventResource($elasticSearch);
    }
}
