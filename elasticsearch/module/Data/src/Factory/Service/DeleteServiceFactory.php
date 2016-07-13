<?php

namespace Data\Factory\Service;

use Data\Service\ConnectionService;
use Data\Service\DeleteService;
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
