<?php

namespace Ingest\V1\Rest\Opportunity;

use Ingest\V1\Service\IndexService;
use Zend\Di\ServiceLocator;

class OpportunityResourceFactory
{
    /**
     * @param ServiceLocator $services
     *
     * @return OpportunityResource
     */
    public function __invoke($services)
    {
        $indexService = $services->get(IndexService::class);

        return new OpportunityResource($indexService);
    }
}
