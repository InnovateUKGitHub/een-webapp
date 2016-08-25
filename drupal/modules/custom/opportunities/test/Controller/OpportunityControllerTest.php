<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Routing\UrlGenerator;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @covers Drupal\opportunities\Controller\OpportunityController
 */
class OpportunityControllerTest extends UnitTestCase
{
    /** @var OpportunitiesService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var OpportunityController */
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

        $this->controller = OpportunityController::create($this->mockContainer);
    }

    public function testIndex()
    {
        $mockFormBuilder = self::getMock(FormBuilder::class, [], [], '', false);
        $mockUrlGenerator = self::getMock(UrlGenerator::class, [], [], '', false);
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        \Drupal::getContainer()->expects(self::at(0))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);
        \Drupal::getContainer()->expects(self::at(1))
            ->method('get')
            ->with('url_generator')
            ->willReturn($mockUrlGenerator);

        $mockFormBuilder->expects(self::once())
            ->method('getForm')
            ->with(ExpressionOfInterestForm::class)
            ->willReturn([]);
        $mockUrlGenerator->expects(self::once())
            ->method('generateFromRoute')
            ->with(
                'opportunities.details',
                ['profileId' => 1],
                ['query' => ['search' => 'H2020', 'opportunity_type' => ['BO']]]
            )
            ->willReturn('my-action');

        $mockRequest->query = $mockQuery;

        $mockQuery->expects(self::at(0))
            ->method('get')
            ->with(OpportunityController::SEARCH)
            ->willReturn('H2020');
        $mockQuery->expects(self::at(1))
            ->method('get')
            ->with(OpportunityController::OPPORTUNITY_TYPE)
            ->willReturn(['BO']);

        $this->mockService->expects(self::once())
            ->method('get')
            ->with(1)
            ->willReturn([]);

        self::assertEquals(
            [
                '#attached'         => [
                    'library' => [
                        'core/drupal.ajax',
                        'core/drupal.dialog',
                        'core/drupal.dialog.ajax',
                        'een/opportunity',
                    ],
                ],
                '#theme'            => 'opportunities_details',
                '#form'             => [
                    '#action' => 'my-action',
                ],
                '#opportunity'      => [],
                '#search'           => 'H2020',
                '#opportunity_type' => ['BO'],
            ],
            $this->controller->index(1, $mockRequest)
        );
    }
}