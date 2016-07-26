<?php

namespace IngestTest\V1\Rest\Delete;

use Ingest\V1\Rest\Delete\DeleteResource;
use Ingest\V1\Rest\Delete\DeleteResourceFactory;

/**
 * @covers Ingest\V1\Rest\Delete\DeleteResourceFactory
 */
class DeleteResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new DeleteResourceFactory();

        $controller = $factory->__invoke(null);
        self::assertInstanceOf(DeleteResource::class, $controller);
    }
}