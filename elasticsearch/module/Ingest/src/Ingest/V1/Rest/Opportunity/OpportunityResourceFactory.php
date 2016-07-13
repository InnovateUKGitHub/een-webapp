<?php

namespace Ingest\V1\Rest\Opportunity;

use Elasticsearch\ClientBuilder;
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
        $elasticSearch = ClientBuilder::create()->build();

        return new OpportunityResource($elasticSearch);
    }
}
