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
 * @covers \Drupal\opportunities\Controller\OpportunityController
 */
class OpportunityControllerTest extends UnitTestCase
{
    /** @var OpportunitiesService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var OpportunityController */
    private $controller;

    public function testIndex()
    {
        $mockFormBuilder = self::getMock(FormBuilder::class, [], [], '', false);
        $mockUrlGenerator = self::getMock(UrlGenerator::class, [], [], '', false);
        $mockRequest = self::getMock(Request::class, [], [], '', false);
        $mockQuery = self::getMock(ParameterBag::class, [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $container */
        $container = \Drupal::getContainer();

        $container->expects(self::at(0))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);
        $container->expects(self::at(1))
            ->method('get')
            ->with('url_generator')
            ->willReturn($mockUrlGenerator);
        $container->expects(self::at(2))
            ->method('get')
            ->with('url_generator')
            ->willReturn($mockUrlGenerator);

        $mockFormBuilder->expects(self::once())
            ->method('getForm')
            ->with(ExpressionOfInterestForm::class)
            ->willReturn([]);
        $mockUrlGenerator->expects(self::at(0))
            ->method('generateFromRoute')
            ->with(
                'opportunities.details',
                ['profileId' => 1],
                ['query' => ['search' => 'H2020', 'opportunity_type' => ['BO']]]
            )
            ->willReturn('my-action');
        $mockUrlGenerator->expects(self::at(1))
            ->method('generateFromRoute')
            ->with(
                'opportunities.details',
                ['profileId' => 1]
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
            ->willReturn(['_source' => ['title' => 'Opportunity Title']]);

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
                '#opportunity'      => [
                    '_source' => ['title' => 'Opportunity Title'],
                ],
                '#search'           => 'H2020',
                '#opportunity_type' => ['BO'],
                '#mail'             => [
                    'subject' => 'Opportunity Title',
                    'body'    => 'Hello,

Here\'s a partnering opportunity I thought might be of interest:
my-action

It\'s on Enterprise Europe Network\'s website, the world\'s largest business support network, led by the European Commission.
',
                ],
            ],
            $this->controller->index(1, $mockRequest)
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

        $this->controller = OpportunityController::create($this->mockContainer);
    }
}