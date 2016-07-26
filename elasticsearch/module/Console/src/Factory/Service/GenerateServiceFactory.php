<?php

namespace Console\Factory\Service;

use Console\Service\ConnectionService;
use Console\Service\GenerateService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class GenerateServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return GenerateService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ConnectionService $connectionService */
        $connectionService = $serviceLocator->get(ConnectionService::class);
        $faker = \Faker\Factory::create();

        return new GenerateService($connectionService, $faker);
    }
}
