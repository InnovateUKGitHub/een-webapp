<?php

namespace Drupal\opportunity_search\Service;

use Zend\Http\Client;
use Zend\Http\Request;

class ElasticSearchService
{
    const SERVICE_ERROR_MSG = 'Cannot connect to elastic search.';

    /** @var string */
    private $baseUrl;
    /** @var Client */
    private $client;
    /** @var int */
    private $searchFrom = 0;
    /** @var int */
    private $searchSize = 10;

    public function __construct()
    {
        // TODO Add to config
        $this->baseUrl = 'http://een-elasticsearch/v1/een/';

        $this->client = new Client(null, ['timeout' => 30]);

        $this->client->setHeaders([
            'Content-type' => 'application/json',
            'accept' => 'application/json'
        ]);
        $this->client->setMethod(Request::METHOD_POST);
    }

    public function setUrl($endPoint)
    {
        $this->client->setUri($this->baseUrl . $endPoint);

        return $this;
    }

    public function setParams(array $query)
    {
        $params = [
            'from' => $this->searchFrom,
            'size' => $this->searchSize,
        ];
        $body = array_merge($params, $query);
        $this->client->setRawBody(json_encode($body));

        return $this;
    }

    public function setSearchFrom($searchFrom)
    {
        $this->searchFrom = $searchFrom;

        return $this;
    }

    public function setSearchSize($searchSize)
    {
        $this->searchSize = $searchSize;

        return $this;
    }

    public function sendRequest()
    {
        $result = $this->client->send();

        if (!$result->isSuccess()) {
            throw new \UnexpectedValueException(
                self::SERVICE_ERROR_MSG,
                $result->getStatusCode(),
                new \UnexpectedValueException(
                    $result->getBody(),
                    $result->getStatusCode()
                )
            );
        }

        return json_decode($result->getBody(), true);
    }
}