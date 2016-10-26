<?php
namespace Drupal\elastic_search\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;

class ElasticSearchService
{
    const SERVICE_ERROR_MSG = 'Cannot connect to elastic search.';
    /** @var string */
    private $server;
    /** @var Client */
    private $client;
    /** @var int */
    private $searchFrom = 0;
    /** @var int */
    private $searchSize = 10;

    /**
     * ElasticSearchService constructor.
     */
    public function __construct()
    {
        $config = \Drupal::config('elastic_search.settings');
        $this->server = $config->get('server');
        $this->client = new Client(null, ['timeout' => 30]);
        $this->client->setHeaders([
            'Content-type' => 'application/json',
            'accept'       => 'application/json',
        ]);
        $this->client->setMethod(Request::METHOD_POST);
    }

    /**
     * @param string $server
     *
     * @return $this
     */
    public function setServer($server)
    {
        $this->server = $server;

        return $this;
    }

    /**
     * @param Client $client
     *
     * @return $this
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        $this->client->setMethod($method);

        return $this;
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function setQueryParams($params)
    {
        $this->client->setParameterGet($params);

        return $this;
    }

    /**
     * @param string $endPoint
     *
     * @return $this
     */
    public function setUrl($endPoint)
    {
        $this->client->setUri($this->server . $endPoint);

        return $this;
    }

    /**
     * @param array $query
     *
     * @return $this
     */
    public function setBody(array $query)
    {
        $this->client->setRawBody(json_encode($query));

        return $this;
    }

    /**
     * @param int $searchFrom
     *
     * @return $this
     */
    public function setSearchFrom($searchFrom)
    {
        $this->searchFrom = $searchFrom;

        return $this;
    }

    /**
     * @param int $searchSize
     *
     * @return $this
     */
    public function setSearchSize($searchSize)
    {
        $this->searchSize = $searchSize;

        return $this;
    }

    public function setBasicAuth($auth)
    {
        $this->client->setAuth($auth, null);
    }

    /**
     * @return array
     */
    public function sendRequest()
    {
        $response = $this->client->send();
        if (!$response->isSuccess()) {
            // Throw correct error if applicable
            return $this->manageErrorType($response);
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * @param Response $response
     *
     * @return array
     */
    private function manageErrorType(Response $response)
    {
        $error = json_decode($response->getBody(), true);

        switch ($response->getStatusCode()) {
            case Response::STATUS_CODE_404:
                throw new NotFoundHttpException($error['detail']);
            case Response::STATUS_CODE_422:
                if (isset($error['validation_messages'])) {
                    return ['error' => $error['validation_messages']];
                }
        }

        // Return standard error message if no error type known
        return ['error' => $error['detail']];
    }
}