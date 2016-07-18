<?php

namespace Search\V1\Rpc\Opportunity;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Zend\Mvc\Controller\ControllerManager;

/**
 * Class OpportunityControllerFactory
 *
 * @package Search\V1\Rpc\Opportunity
 */
class OpportunityControllerFactory
{
    /**
     * @param ControllerManager $controllers
     *
     * @return OpportunityController
     */
    public function __invoke(ControllerManager $controllers)
    {
        $serviceLocator = $controllers->getServiceLocator();
        $service = $serviceLocator->get(ElasticSearchService::class);

        return new OpportunityController($service);
    }
}
