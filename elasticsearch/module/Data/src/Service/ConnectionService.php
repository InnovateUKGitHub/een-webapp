<?php

namespace Data\Service;

class ConnectionService
{
    /** @var HttpService */
    private $client;

    public function __construct(HttpService $client)
    {
        $this->client = $client;
    }

    /**
     * Executes HTTP Service request
     *
     * @param string $method
     * @param string $path
     * @param array  $body
     *
     * @return array
     */
    public function execute($method, $path, $body = null)
    {
        $this->client->setHttpMethod($method);
        $this->client->setPathToService($path);
        if ($body !== null) {
            $this->client->setRequestBody(json_encode($body));
        }
        $result = $this->client->execute();

        return $result;
    }
}