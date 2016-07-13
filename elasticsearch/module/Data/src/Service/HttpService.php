<?php

namespace Data\Service;

use Zend\Http\Client;
use Zend\Http\Client\Adapter\AdapterInterface;
use Zend\Http\Client\Adapter\Curl;
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
    /** @var integer Request::METHOD_GET|PUT|POST|DELETE etc. */
    private $httpMethod = Request::METHOD_GET;
    /** @var Client */
    private $httpClient;
    /** @var AdapterInterface */
    private $httpAdapter;
    /** @var Response */
    private $httpResponse;

    /**
     * HttpService constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->server = $config[self::SERVER];
        $this->port = $config[self::PORT];
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
     * @return string json
     */
    public function execute()
    {
        $adapter = new Curl();
        $adapter->setOptions([
            CURLOPT_MAXCONNECTS   => 3,
            CURLOPT_FRESH_CONNECT => true,
        ]);
        $uri = $this->buildUri();

        $httpClient = $this->getHttpClient();

        $httpClient->setHeaders([
            'Accept'       => 'application/json',
            'Content-type' => 'application/json'
        ]);

        $httpClient->setAdapter($this->getHttpAdapter());
        $httpClient->setMethod($this->httpMethod);
        $httpClient->setUri($uri);

        if ($this->requestBody !== null && $this->httpMethod === Request::METHOD_POST) {
            $httpClient->setRawBody($this->requestBody);
        }

        $this->httpResponse = $httpClient->send();

        $rawContent = $this->httpResponse->getBody();
        $content = json_decode($rawContent, true);

        if ($content === null) {
            throw new HttpException('Malformed JSON response' . (string)$this->httpResponse);
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

    /**
     * @return Client
     */
    private function getHttpClient()
    {
        if ($this->httpClient === null) {
            $client = new Client(null, ['timeout' => 30]);
            $client->setAdapter($this->getHttpAdapter());
            $this->httpClient = $client;
        }

        return $this->httpClient;
    }

    /**
     * @return AdapterInterface
     */
    private function getHttpAdapter()
    {
        if ($this->httpAdapter === null) {
            $adapter = new Curl();
            $adapter->setCurlOption(CURLOPT_ENCODING, 'deflate');
            // Sensible default - simply prevents infinite waiting time
            $adapter->setOptions(['timeout' => 30]);
            $this->httpAdapter = $adapter;
        }

        return $this->httpAdapter;
    }
}
