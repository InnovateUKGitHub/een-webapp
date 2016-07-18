<?php

namespace Console\Factory\Controller;

use Console\Controller\GenerateController;
use Console\Service\GenerateService;
use Console\Service\DeleteService;
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
