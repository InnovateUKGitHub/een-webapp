<?php

namespace Console\Factory\Service;

use Console\Service\ConnectionService;
use Console\Service\HttpService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class ConnectionServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return ConnectionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var HttpService $httpService */
        $httpService = $serviceLocator->get(HttpService::class);

        return new ConnectionService($httpService);
    }
}
