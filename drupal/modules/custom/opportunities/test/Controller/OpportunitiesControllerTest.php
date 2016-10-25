<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\opportunities\Controller\OpportunitiesController;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers \Drupal\opportunities\Controller\OpportunitiesController
 */
class OpportunitiesControllerTest extends UnitTestCase
{
    /** @var OpportunitiesService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var OpportunitiesController */
    private $controller;

    public function testIndex()
    {
        $mockRequest = self::getMock(Request::class, [], [], '', false);

        $this->mockService->expects(self::once())
            ->method('getOpportunities')
            ->with($mockRequest)
            ->willReturn([
                'form'             => 10,
                'search'           => 10,
                'opportunity_type' => 10,
                'country'          => 10,
                'results'          => [],
                'total'            => 10,
                'pageTotal'        => 10,
                'page'             => 10,
                'resultPerPage'    => 10,
            ]);

        self::assertEquals(
            [
                '#theme',
                '#form',
                '#search',
                '#opportunity_type',
                '#country',
                '#results',
                '#total',
                '#pageTotal',
                '#page',
                '#resultPerPage',
                '#route',
            ],
            array_keys($this->controller->index($mockRequest))
        );
    }

    protected function Setup()
    {
        parent::setUp();

        $this->mockService = self::getMock(OpportunitiesService::class, [], [], '', false);
        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        \Drupal::setContainer($this->mockContainer);

        $this->mockContainer->expects(self::at(0))
            ->method('get')
            ->with('opportunities.service')
            ->willReturn($this->mockService);

        $this->controller = OpportunitiesController::create($this->mockContainer);
    }
}