<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\opportunities\Controller\OpportunitiesController;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Drupal\opportunities\Controller\OpportunitiesController
 */
class OpportunitiesControllerTest extends UnitTestCase
{
    /** @var OpportunitiesService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var OpportunitiesController */
    private $controller;

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

    public function testSearch()
    {
        $mockFormBuilder = self::getMock(FormBuilder::class, [], [], '', false);
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        \Drupal::getContainer()->expects(self::at(0))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);

        $mockFormBuilder->expects(self::once())
            ->method('getForm')
            ->with(OpportunitiesForm::class)
            ->willReturn([]);

        $mockRequest->query = $mockQuery;

        self::assertEquals(
            [
                '#theme'            => 'opportunities_search',
                '#form'             => [],
                '#search'           => null,
                '#opportunity_type' => null,
                '#results'          => null,
                '#total'            => null,
                '#pageTotal'        => null,
                '#page'             => null,
                '#resultPerPage'    => null,
                '#route'            => 'opportunities.search',
            ],
            $this->controller->search($mockRequest)
        );
    }

    public function testSearchWithSearch()
    {
        $mockFormBuilder = self::getMock(FormBuilder::class, [], [], '', false);
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        \Drupal::getContainer()->expects(self::at(0))
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

        $this->mockService->expects(self::once())
            ->method('search')
            ->with([], 'H2020', ['BO'], 1, 10)
            ->willReturn(['results' => [], 'total' => 10]);

        self::assertEquals(
            [
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
            $this->controller->search($mockRequest)
        );
    }

    public function testDetails()
    {
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        $mockRequest->query = $mockQuery;

        $mockQuery->expects(self::at(0))
            ->method('get')
            ->with(OpportunitiesController::SEARCH)
            ->willReturn('H2020');
        $mockQuery->expects(self::at(1))
            ->method('get')
            ->with(OpportunitiesController::OPPORTUNITY_TYPE)
            ->willReturn(['BO']);

        $this->mockService->expects(self::once())
            ->method('get')
            ->with(1)
            ->willReturn([]);

        self::assertEquals(
            [
                '#theme'            => 'opportunities_details',
                '#opportunity'      => [],
                '#search'           => 'H2020',
                '#opportunity_type' => ['BO'],
            ],
            $this->controller->details(1, $mockRequest)
        );
    }

    public function testAjax()
    {
        $this->mockService->expects(self::once())
            ->method('get')
            ->with(1)
            ->willReturn([]);

        $result = $this->controller->ajax(1);

        self::assertInstanceOf(JsonResponse::class, $result);
    }
}