<?php

namespace IngestTest\V1\Rest\Event;

use Ingest\V1\Rest\Event\EventResource;
use Ingest\V1\Rest\Event\EventResourceFactory;

/**
 * @covers Ingest\V1\Rest\Event\EventResourceFactory
 */
class EventResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new EventResourceFactory();

        $controller = $factory->__invoke(null);
        self::assertInstanceOf(EventResource::class, $controller);
    }
}