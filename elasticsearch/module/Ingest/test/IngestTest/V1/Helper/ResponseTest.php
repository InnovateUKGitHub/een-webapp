<?php

namespace IngestTest\Helper;
use Ingest\Helper\Response;

/**
 * @covers Ingest\Helper\Response
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testCreate()
    {
        $response = Response::create(['success' => true]);

        self::assertInstanceOf(\Zend\Http\Response::class, $response);
        self::assertEquals('Content-Type: application/json; charset=utf-8', $response->getHeaders()->get('Content-Type')->toString());
        self::assertEquals('Content-Length: 16', $response->getHeaders()->get('Content-Length')->toString());
        self::assertEquals('{"success":true}', $response->getContent());
    }
}
