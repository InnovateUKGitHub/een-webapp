<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\opportunities\Controller\OpportunitiesController;
use Drupal\Tests\UnitTestCase;

/**
 * @covers Drupal\opportunities\Controller\OpportunitiesController
 */
class OpportunitiesControllerTest extends UnitTestCase
{
    protected function Setup()
    {
        parent::setUp();
    }

    public function testFactory()
    {
        $mockElasticSearchService = self::getMock(ElasticSearchService::class, [], [], '', false);
        $mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        \Drupal::setContainer($mockContainer);

        $mockContainer->expects(self::once())
            ->method('get')
            ->with('elastic_search.connection')
            ->willReturn($mockElasticSearchService);

        self::assertInstanceOf(
            OpportunitiesController::class,
            OpportunitiesController::create($mockContainer)
        );
    }
}