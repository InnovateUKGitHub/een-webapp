<?php

namespace Search\V1\ElasticSearch\Factory;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ElasticSearchServiceFactory
 *
 * @package Search\V1\ElasticSearch\Factory
 */
class ElasticSearchServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $sm
     *
     * @return ElasticSearchService
     */
    public function createService(ServiceLocatorInterface $sm)
    {
        return new ElasticSearchService();
    }
}