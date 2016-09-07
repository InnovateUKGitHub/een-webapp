<?php
namespace Drupal\opportunities\Test\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormState;
use Drupal\Core\Render\Renderer;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @covers \Drupal\opportunities\Form\ExpressionOfInterestForm
 * @covers \Drupal\opportunities\Form\AbstractForm
 */
class ExpressionOfInterestFormTest extends UnitTestCase
{
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;

    public function testGetFormId()
    {
        $form = new ExpressionOfInterestForm();

        self::assertEquals('expression_of_interest_form', $form->getFormId());
    }

    public function testBuildForm()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();

        $formArray = $form->buildForm($formArray, $formState);

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

    public function testSubmitHandler()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();

        $formArray = $form->buildForm($formArray, $formState);

        self::assertInstanceOf(AjaxResponse::class, $form->submitHandler($formArray, $formState));
    }

    public function testSubmitHandlerWithErrors()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();
        $formState->setValue('email', 'invalid');

        $formArray = $form->buildForm($formArray, $formState);
        $form->validateForm($formArray, $formState);

        $formArray['description']['#parents'] = ['description'];
        $formArray['interest']['#parents'] = ['interest'];
        $formArray['more']['#parents'] = ['more'];
        $formArray['email']['#parents'] = ['email'];
        $formArray['phone']['#parents'] = ['phone'];
        $formArray['phoneStatus']['#parents'] = ['phoneStatus'];

        $rendererMock = self::getMock(Renderer::class, [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $container */
        $container = \Drupal::getContainer();

        $container->expects(self::once())
            ->method('get')
            ->with('renderer')
            ->willReturn($rendererMock);
        $rendererMock->expects(self::once())
            ->method('renderRoot')
            ->with(['#type' => 'status_messages'])
            ->willReturn('Some status messages');

        $response = $form->submitHandler($formArray, $formState);
        self::assertInstanceOf(AjaxResponse::class, $response);

        $commands = $response->getCommands();
        self::assertCount(13, $commands);
        self::assertEquals('insert', $commands[0]['command']);
        self::assertEquals('html', $commands[0]['method']);
        self::assertEquals('invoke', $commands[1]['command']);
        self::assertEquals('removeClass', $commands[1]['method']);

        self::assertEquals('insert', $commands[2]['command']);
        self::assertEquals('html', $commands[2]['method']);
        self::assertEquals('invoke', $commands[3]['command']);
        self::assertEquals('addClass', $commands[3]['method']);
        self::assertEquals('insert', $commands[4]['command']);
        self::assertEquals('html', $commands[4]['method']);
        self::assertEquals('invoke', $commands[5]['command']);
        self::assertEquals('addClass', $commands[5]['method']);
        self::assertEquals('insert', $commands[6]['command']);
        self::assertEquals('html', $commands[6]['method']);
        self::assertEquals('invoke', $commands[7]['command']);
        self::assertEquals('addClass', $commands[7]['method']);
        self::assertEquals('insert', $commands[8]['command']);
        self::assertEquals('html', $commands[8]['method']);
        self::assertEquals('invoke', $commands[9]['command']);
        self::assertEquals('addClass', $commands[9]['method']);
        self::assertEquals('insert', $commands[10]['command']);
        self::assertEquals('html', $commands[10]['method']);
        self::assertEquals('invoke', $commands[11]['command']);
        self::assertEquals('addClass', $commands[11]['method']);
        self::assertEquals('insert', $commands[12]['command']);
        self::assertEquals('html', $commands[12]['method']);
    }

    public function testSubmitHandlerWithoutErrorsAndEmail()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();
        $formState->setValue('description', 'description');
        $formState->setValue('interest', 'interest');
        $formState->setValue('more', 'more');
        $formState->setValue('email', 'email@email.email');

        $formArray = $form->buildForm($formArray, $formState);
        $form->validateForm($formArray, $formState);

        $formArray['description']['#parents'] = ['description'];
        $formArray['interest']['#parents'] = ['interest'];
        $formArray['more']['#parents'] = ['more'];
        $formArray['email']['#parents'] = ['email'];
        $formArray['phone']['#parents'] = ['phone'];
        $formArray['phoneStatus']['#parents'] = ['phoneStatus'];

        $response = $form->submitHandler($formArray, $formState);
        self::assertInstanceOf(AjaxResponse::class, $response);

        $commands = $response->getCommands();
        self::assertCount(4, $commands);
        self::assertEquals('insert', $commands[0]['command']);
        self::assertEquals('html', $commands[0]['method']);
        self::assertEquals('invoke', $commands[1]['command']);
        self::assertEquals('removeClass', $commands[1]['method']);
        self::assertEquals('insert', $commands[2]['command']);
        self::assertEquals('html', $commands[2]['method']);
        self::assertEquals('openDialog', $commands[3]['command']);
    }

    public function testSubmitHandlerWithoutErrorsAndPhoneNumber()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();
        $formState->setValue('description', 'description');
        $formState->setValue('interest', 'interest');
        $formState->setValue('more', 'more');
        $formState->setValue('phone', '01234567891');
        $formState->setValue('phoneStatus', '1');

        $formArray = $form->buildForm($formArray, $formState);
        $form->validateForm($formArray, $formState);

        $formArray['description']['#parents'] = ['description'];
        $formArray['interest']['#parents'] = ['interest'];
        $formArray['more']['#parents'] = ['more'];
        $formArray['email']['#parents'] = ['email'];
        $formArray['phone']['#parents'] = ['phone'];
        $formArray['phoneStatus']['#parents'] = ['phoneStatus'];

        $response = $form->submitHandler($formArray, $formState);
        self::assertInstanceOf(AjaxResponse::class, $response);

        $commands = $response->getCommands();
        self::assertCount(4, $commands);
        self::assertEquals('insert', $commands[0]['command']);
        self::assertEquals('html', $commands[0]['method']);
        self::assertEquals('invoke', $commands[1]['command']);
        self::assertEquals('removeClass', $commands[1]['method']);
        self::assertEquals('insert', $commands[2]['command']);
        self::assertEquals('html', $commands[2]['method']);
        self::assertEquals('openDialog', $commands[3]['command']);
    }

    public function testValidateForm()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();

        $formArray = $form->buildForm($formArray, $formState);

        $form->validateForm($formArray, $formState);

        self::assertCount(5, $formState->getErrors());
    }

    public function testSubmitForm()
    {
        $form = new ExpressionOfInterestForm();

        $formArray = [];
        $formState = new FormState();

        $formArray = $form->buildForm($formArray, $formState);

        self::assertFalse($form->submitForm($formArray, $formState));
    }

    protected function setup()
    {
        parent::setUp();

        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        \Drupal::setContainer($this->mockContainer);
    }
}