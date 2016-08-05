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
    private $baseUrl;
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
        $this->baseUrl = $config->get('server');
        $this->client = new Client(null, ['timeout' => 30]);
        $this->client->setHeaders([
            'Content-type' => 'application/json',
            'accept'       => 'application/json',
        ]);
        $this->client->setMethod(Request::METHOD_POST);
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
        $this->client->setUri($this->baseUrl . $endPoint);

        return $this;
    }

    /**
     * @param array $query
     *
     * @return $this
     */
    public function setSearchParams(array $query)
    {
        $params = [
            'from' => $this->searchFrom,
            'size' => $this->searchSize,
        ];
        $body = array_merge($params, $query);
        $this->client->setRawBody(json_encode($body));

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

    /**
     * @return array
     */
    public function sendRequest()
    {
        $response = $this->client->send();
        if (!$response->isSuccess()) {
            // Throw correct error if applicable
            $this->manageErrorType($response);

            // Return standard error message if no error type known
            return ['error' => t('Connection to the search engine failed')];
        }

        return json_decode($response->getBody(), true);
    }

    /**
     * @param Response $response
     */
    private function manageErrorType(Response $response)
    {
        switch ($response->getStatusCode()) {
            case Response::STATUS_CODE_404:
                $error = json_decode($response->getBody(), true);
                throw new NotFoundHttpException($error['detail']);
        }
    }
}