<?php

namespace IngestTest\V1\Rest\Opportunity;

use Ingest\V1\Rest\Opportunity\OpportunityResource;
use Ingest\V1\Rest\Opportunity\OpportunityResourceFactory;

/**
 * @covers Ingest\V1\Rest\Opportunity\OpportunityResourceFactory
 */
class OpportunityResourceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $factory = new OpportunityResourceFactory();

        $controller = $factory->__invoke(null);
        self::assertInstanceOf(OpportunityResource::class, $controller);
    }
}