<?php
namespace Drupal\opportunities\Test\Form;

use Drupal\Core\Form\FormState;
use Drupal\Core\Url;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \Drupal\opportunities\Form\OpportunitiesForm
 * @covers \Drupal\opportunities\Form\AbstractForm
 */
class OpportunitiesFormTest extends UnitTestCase
{
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var OpportunitiesService|\PHPUnit_Framework_MockObject_MockObject */
    private $mockService;

    public function testGetFormId()
    {
        $form = new OpportunitiesForm($this->mockService);

        self::assertEquals('opportunity_search_form', $form->getFormId());
    }

    public function testBuildForm()
    {
        $this->mockService->expects(self::any())
            ->method('getCountryList')
            ->willReturn([]);

        $form = new OpportunitiesForm($this->mockService);

        $formArray = [];
        $formState = new FormState();

        $result = $form->buildForm($formArray, $formState);

        self::assertArrayHasKey('search', $result);
        self::assertArrayHasKey('opportunity_type', $result);
        self::assertArrayHasKey('actions', $result);
        self::assertArrayHasKey('#method', $result);
    }

    public function testValidForm()
    {
        $form = new OpportunitiesForm($this->mockService);

        $formArray = [];
        $formState = new FormState();

        $formState->setValue('search', 'Valid Search');

        $form->validateForm($formArray, $formState);

        self::assertEquals(
            [],
            $formState->getErrors()
        );
    }

    public function testSubmitForm()
    {
        $form = new OpportunitiesForm($this->mockService);

        $formArray = [];
        $formState = new FormState();

        $formState->setValue('search', '');
        $formState->setValue('opportunity_type', '');
        $formState->setValue('country', '');

        $form->submitForm($formArray, $formState);

        self::assertInstanceOf(Url::class, $formState->getRedirect());
    }

    protected function setup()
    {
        parent::setUp();

        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        $this->mockService = self::getMock(OpportunitiesService::class, [], [], '', false);

        \Drupal::setContainer($this->mockContainer);
    }
}