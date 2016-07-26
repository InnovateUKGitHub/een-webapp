<?php

namespace Search\V1\ElasticSearch\Factory;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Search\V1\ElasticSearch\Service\QueryService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ElasticSearchServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $sm
     *
     * @return ElasticSearchService
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        /** @var QueryService $query */
        $query = $sm->get(QueryService::class);

        return new ElasticSearchService($query);
    }
}