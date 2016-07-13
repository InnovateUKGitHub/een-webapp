<?php

namespace Data\Factory\Service;

use Data\Service\ConnectionService;
use Data\Service\GenerateService;
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
