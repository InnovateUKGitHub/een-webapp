<?php

namespace Data\Service;

use Zend\Http\Client;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Http\Exception\InvalidArgumentException;
use Zend\Json\Server\Exception\HttpException;

class HttpService
{
    const SERVER = 'server';
    const PORT = 'port';

    /** @var string */
    private $server;
    /** @var string */
    private $userName;
    /** @var string */
    private $password;
    /** @var integer */
    private $port;
    /** @var string */
    private $pathToService;
    /** @var string */
    private $requestBody;
    /** @var string */
    private $version;
    /** @var string http or https */
    private $httpScheme = 'http';
    /** @var string Request::METHOD_GET|PUT|POST|DELETE etc. */
    private $httpMethod = Request::METHOD_GET;
    /** @var Client */
    private $client;

    /**
     * HttpService constructor.
     *
     * @param Client $client
     * @param array $config
     */
    public function __construct(Client $client, $config)
    {
        if (array_key_exists(self::SERVER, $config) === false) {
            throw new InvalidArgumentException('The config file is incorrect. Please specify the server');
        }
        if (array_key_exists(self::PORT, $config) === false) {
            throw new InvalidArgumentException('The config file is incorrect. Please specify the port');
        }

        $this->server = $config[self::SERVER];
        $this->port = $config[self::PORT];

        $this->client = $client;
    }

    /**
     * @param string $httpMethod
     *
     * @return $this
     */
    public function setHttpMethod($httpMethod)
    {
        switch ($httpMethod) {
            case 'GET':
                $method = Request::METHOD_GET;
                break;
            case 'PUT':
                $method = Request::METHOD_PUT;
                break;
            case 'POST':
                $method = Request::METHOD_POST;
                break;
            case 'DELETE':
                $method = Request::METHOD_DELETE;
                break;
            default:
                throw new InvalidArgumentException('Unsupported HTTP method ' . $httpMethod);
        }
        $this->httpMethod = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    /**
     * The part of the URL after the hostname
     *
     * @param string $pathToService
     *
     * @return self
     */
    public function setPathToService($pathToService)
    {
        $this->pathToService = $pathToService;

        return $this;
    }

    /**
     * @return string
     */
    public function getPathToService()
    {
        return $this->pathToService;
    }

    /**
     * @param string $requestBody
     *
     * @return $this
     */
    public function setRequestBody($requestBody)
    {
        $this->requestBody = $requestBody;

        return $this;
    }

    /**
     * @return string
     */
    public function getRequestBody()
    {
        return $this->requestBody;
    }

    /**
     * @return string json
     */
    public function execute()
    {
        $uri = $this->buildUri();

        $this->client->setHeaders([
            'Accept'       => 'application/json',
            'Content-type' => 'application/json'
        ]);

        $this->client->setMethod($this->httpMethod);
        $this->client->setUri($uri);

        if ($this->requestBody !== null && $this->httpMethod === Request::METHOD_POST) {
            $this->client->setRawBody($this->requestBody);
        }

        $httpResponse = $this->client->send();

        $rawContent = $httpResponse->getBody();
        $content = json_decode($rawContent, true);

        if ($content === null) {
            throw new HttpException('Malformed JSON response: ' . (string)$rawContent);
        }

        return $content;
    }

    /**
     * @return string
     */
    private function buildUri()
    {
        $uri = $this->httpScheme . '://';

        if (!empty($this->userName) || !empty($this->password)) {
            $uri .= $this->userName . '.' . $this->password . '@';
        }
        $uri .= $this->server;
        if (!empty($this->port)) {
            $uri .= ':' . $this->port;
        }
        if (!empty($this->version)) {
            $uri .= '/' . $this->version;
        }
        if (!empty($this->pathToService)) {
            $uri .= '/' . $this->pathToService;
        }

        return $uri;
    }
}
