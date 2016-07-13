<?php

namespace Data\Factory\Controller;

use Data\Controller\GenerateController;
use Data\Service\GenerateService;
use Data\Service\DeleteService;
use Zend\Di\ServiceLocator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class GenerateControllerFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return GenerateController
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ServiceLocator $sl */
        $sl = $serviceLocator->getServiceLocator();

        /** @var GenerateService $generateService */
        $generateService = $sl->get(GenerateService::class);
        /** @var DeleteService $deleteService */
        $deleteService = $sl->get(DeleteService::class);

        return new GenerateController($generateService, $deleteService);
    }
}
