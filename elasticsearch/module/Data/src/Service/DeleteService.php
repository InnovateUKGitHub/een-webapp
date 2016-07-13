<?php

namespace Data\Service;

use Zend\Http\Request;

class DeleteService
{
    /** @var ConnectionService */
    private $connection;

    public function __construct(ConnectionService $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string $index
     */
    public function delete($index)
    {
        $this->connection->execute(Request::METHOD_POST, 'delete', ['index' => $index]);
    }
}
