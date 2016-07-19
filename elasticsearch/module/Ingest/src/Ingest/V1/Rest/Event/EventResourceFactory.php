<?php

namespace Ingest\V1\Rest\Event;

use Ingest\V1\Service\IndexService;
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
        $indexService = $services->get(IndexService::class);

        return new EventResource($indexService);
    }
}
