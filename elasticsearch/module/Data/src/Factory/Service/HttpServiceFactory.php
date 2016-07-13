<?php

namespace Data\Factory\Service;

use Data\Service\HttpService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Exception\InvalidArgumentException;

final class HttpServiceFactory implements FactoryInterface
{
    const CONFIG_SERVICE = 'config';
    const CONFIG_ELASTIC_SEARCH = 'elastic-search';
    /**
     * {@inheritDoc}
     *
     * @return HttpService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get(self::CONFIG_SERVICE);
        if (array_key_exists(self::CONFIG_ELASTIC_SEARCH, $config) === false) {
            throw new InvalidArgumentException('The config file is incorrect. Please specify the server informations');
        }

        return new HttpService($config[self::CONFIG_ELASTIC_SEARCH]);
    }
}
