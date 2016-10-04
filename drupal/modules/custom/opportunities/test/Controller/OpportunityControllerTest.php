<?php
namespace Drupal\opportunities\Test\Controller;

use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Routing\UrlGenerator;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\opportunities\Form\EmailVerificationForm;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Drupal\user\PrivateTempStore;
use Drupal\user\PrivateTempStoreFactory;
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
    /** @var PrivateTempStoreFactory|\PHPUnit_Framework_MockObject_MockObject */
    private $mockSession;
    /** @var SessionManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockSessionManager;
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
        $container = $this->mockContainer;

        $container->expects(self::at(0))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);
        $container->expects(self::at(1))
            ->method('get')
            ->with('form_builder')
            ->willReturn($mockFormBuilder);
        $container->expects(self::at(2))
            ->method('get')
            ->with('url_generator')
            ->willReturn($mockUrlGenerator);

        $mockFormBuilder->expects(self::at(0))
            ->method('getForm')
            ->with(ExpressionOfInterestForm::class)
            ->willReturn([]);
        $mockFormBuilder->expects(self::at(1))
            ->method('getForm')
            ->with(EmailVerificationForm::class)
            ->willReturn([]);

        $mockQuery->expects(self::at(0))
            ->method('get')
            ->with(OpportunityController::SEARCH)
            ->willReturn('H2020');
        $mockQuery->expects(self::at(1))
            ->method('get')
            ->with(OpportunityController::OPPORTUNITY_TYPE)
            ->willReturn(['BO']);
        $mockQuery->expects(self::at(2))
            ->method('get')
            ->with(OpportunityController::COUNTRY)
            ->willReturn(['FR']);
        $mockRequest->query = $mockQuery;

        $this->mockService->expects(self::once())
            ->method('get')
            ->with(1)
            ->willReturn(['_source' => ['title' => 'Opportunity Title']]);

        self::assertEquals(
            [
                '#theme',
                '#form_email',
                '#form',
                '#opportunity',
                '#search',
                '#opportunity_type',
                '#country',
                '#token',
                '#email',
                '#mail',
            ],
            array_keys($this->controller->index(1, '', $mockRequest))
        );
    }

    protected function Setup()
    {
        parent::setUp();

        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);

        $this->mockService = self::getMock(OpportunitiesService::class, [], [], '', false);
        $this->mockSession = self::getMock(PrivateTempStore::class, ['get'], [], '', false);
        $this->mockSessionManager = self::getMock(SessionManagerInterface::class, [], [], '', false);

        \Drupal::setContainer($this->mockContainer);

        $this->controller = new OpportunityController(
            $this->mockService,
            $this->mockSession,
            $this->mockSessionManager
        );
    }
}