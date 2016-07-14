<?php

namespace Data\Factory\Service;

use Data\Service\HttpService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Http\Client;
use Zend\Http\Client\Adapter\Curl;
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
            throw new InvalidArgumentException('The config file is incorrect. Please specify the server information');
        }

        $adapter = new Curl();
        $adapter->setCurlOption(CURLOPT_ENCODING, 'deflate');
        $adapter->setOptions([
            CURLOPT_MAXCONNECTS   => 3,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $adapter->setOptions(['timeout' => 30]);

        $client = new Client(null, ['timeout' => 30]);
        $client->setAdapter($adapter);

        return new HttpService($client, $config[self::CONFIG_ELASTIC_SEARCH]);
    }
}
