<?php

namespace Console\Factory\Service;

use Console\Service\ConnectionService;
use Console\Service\DeleteService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

final class DeleteServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return DeleteService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var ConnectionService $connectionService */
        $connectionService = $serviceLocator->get(ConnectionService::class);

        return new DeleteService($connectionService);
    }
}
