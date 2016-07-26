<?php
namespace Ingest\V1\Rest\Delete;

use Elasticsearch\ClientBuilder;

class DeleteResourceFactory
{
    /**
     * @param $services
     *
     * @return DeleteResource
     */
    public function __invoke($services)
    {
        $elasticSearch = ClientBuilder::create()->build();

        return new DeleteResource($elasticSearch);
    }
}
