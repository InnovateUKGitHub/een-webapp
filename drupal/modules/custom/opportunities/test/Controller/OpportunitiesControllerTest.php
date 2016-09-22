<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\opportunities\Controller\OpportunitiesController;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
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
        $mockFormBuilder = self::getMock(FormBuilder::class, [], [], '', false);
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $container */
        $container = \Drupal::getContainer();

        $container->expects(self::at(0))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);

        $mockFormBuilder->expects(self::once())
            ->method('getForm')
            ->with(OpportunitiesForm::class)
            ->willReturn([]);

        $mockRequest->query = $mockQuery;

        $mockQuery->expects(self::at(0))
            ->method('get')
            ->with(OpportunitiesController::PAGE_NUMBER)
            ->willReturn(1);
        $mockQuery->expects(self::at(1))
            ->method('get')
            ->with(OpportunitiesController::RESULT_PER_PAGE)
            ->willReturn(10);
        $mockQuery->expects(self::at(2))
            ->method('get')
            ->with(OpportunitiesController::SEARCH)
            ->willReturn('H2020');
        $mockQuery->expects(self::at(3))
            ->method('get')
            ->with(OpportunitiesController::OPPORTUNITY_TYPE)
            ->willReturn(['BO']);
        $mockQuery->expects(self::at(4))
            ->method('get')
            ->with(OpportunitiesController::COUNTRY)
            ->willReturn(['FR']);

        $this->mockService->expects(self::once())
            ->method('search')
            ->with([], 'H2020', ['BO'], ['FR'], 1, 10)
            ->willReturn(['results' => [], 'total' => 10]);

        self::assertEquals(
            [
                '#attached'         => [
                    'library' => [
                        'een/opportunity-list',
                    ],
                ],
                '#theme'            => 'opportunities_search',
                '#form'             => [],
                '#search'           => 'H2020',
                '#opportunity_type' => ['BO'],
                '#results'          => [],
                '#total'            => 10,
                '#pageTotal'        => 1,
                '#page'             => 1,
                '#resultPerPage'    => 10,
                '#route'            => 'opportunities.search',
            ],
            $this->controller->index($mockRequest)
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