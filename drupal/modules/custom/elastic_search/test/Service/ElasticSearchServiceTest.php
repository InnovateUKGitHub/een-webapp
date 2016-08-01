<?php
namespace Drupal\elastic_search\Test\Service;

use Drupal\Core\Config\ImmutableConfig;
use Drupal\elastic_search\Service\ElasticSearchService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Http\Request;

/**
 * @covers Drupal\elastic_search\Service\ElasticSearchService
 */
class ElasticSearchServiceTest extends UnitTestCase
{
    protected function Setup()
    {
        parent::setUp();

        $configMock = self::getMock(ImmutableConfig::class, [], [], '', false);
        $containerMock = self::getMock(ContainerInterface::class, [], [], '', false);
        $containerMock->expects(self::once())
            ->method('get')
            ->with('config.factory')
            ->willReturn($configMock);
        $configMock->expects(self::at(0))
            ->method('get')
            ->with('elastic_search.settings')
            ->willReturn($configMock);
        $configMock->expects(self::at(1))
            ->method('get')
            ->with('server')
            ->willReturn('my_server');

        \Drupal::setContainer($containerMock);
    }

    public function testConstructor()
    {
        $service = new ElasticSearchService();

        self::assertInstanceOf(ElasticSearchService::class, $service->setMethod(Request::METHOD_GET));
    }
}