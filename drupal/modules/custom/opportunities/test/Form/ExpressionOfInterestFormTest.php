<?php
namespace Drupal\opportunities\Test\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Renderer;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\Tests\UnitTestCase;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \Drupal\opportunities\Form\ExpressionOfInterestForm
 * @covers \Drupal\opportunities\Form\AbstractForm
 */
class ExpressionOfInterestFormTest extends UnitTestCase
{
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;
    /** @var PrivateTempStore|\PHPUnit_Framework_MockObject_MockObject */
    private $mockSession;
    /** @var ExpressionOfInterestForm|\PHPUnit_Framework_MockObject_MockObject */
    private $form;

    public function testGetFormId()
    {
        self::assertEquals('expression_of_interest_form', $this->form->getFormId());
    }

    public function testBuildForm()
    {
        $formArray = [];
        $formState = new FormState();

        $formArray = $this->form->buildForm($formArray, $formState);

        self::assertArrayHasKey('description', $formArray);
        self::assertEquals('textarea', $formArray['description']['#type']);

        self::assertArrayHasKey('interest', $formArray);
        self::assertEquals('textarea', $formArray['interest']['#type']);

        self::assertArrayHasKey('more', $formArray);
        self::assertEquals('textarea', $formArray['more']['#type']);

        self::assertArrayHasKey('email', $formArray);
        self::assertEquals('textfield', $formArray['email']['#type']);

        self::assertArrayHasKey('actions', $formArray);
        self::assertArrayHasKey('#method', $formArray);
    }

    public function testValidateForm()
    {
        $formArray = [];
        $formState = new FormState();

        $formArray = $this->form->buildForm($formArray, $formState);

        $this->form->validateForm($formArray, $formState);

        self::assertCount(2, $formState->getErrors());
    }

    public function testSubmitForm()
    {
        $formArray = [];
        $formState = new FormState();

        $formArray = $this->form->buildForm($formArray, $formState);

        self::assertFalse($this->form->submitForm($formArray, $formState));
    }

    protected function setup()
    {
        parent::setUp();

        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        \Drupal::setContainer($this->mockContainer);

        $this->mockSession = self::getMock(PrivateTempStore::class, ['get', 'set'], [], '', false);
        $this->form = new ExpressionOfInterestForm($this->mockSession);
    }
}