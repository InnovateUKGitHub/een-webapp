<?php

namespace Ingest\V1\Rest\Event;

use Elasticsearch\ClientBuilder;
use Zend\Di\ServiceLocator;

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
