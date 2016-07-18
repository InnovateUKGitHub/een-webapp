<?php

namespace Search\V1\Rpc\Event;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Zend\Mvc\Controller\ControllerManager;

/**
 * Class EventControllerFactory
 *
 * @package Search\V1\Rpc\Event
 */
class EventControllerFactory
{
    /**
     * @param ControllerManager $controllers
     *
     * @return EventController
     */
    public function __invoke(ControllerManager $controllers)
    {
        $serviceLocator = $controllers->getServiceLocator();
        $service = $serviceLocator->get(ElasticSearchService::class);

        return new EventController($service);
    }
}
