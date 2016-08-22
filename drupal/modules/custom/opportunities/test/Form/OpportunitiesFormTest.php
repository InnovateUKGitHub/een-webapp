<?php
namespace Drupal\opportunities\Test\Service;

use Drupal\Core\Form\FormState;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

include __DIR__ . '/../../../../../core/includes/bootstrap.inc';

/**
 * @covers Drupal\opportunities\Form\OpportunitiesForm
 */
class OpportunitiesFormTest extends UnitTestCase
{
    /** @var ContainerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $mockContainer;

    protected function setup()
    {
        parent::setUp();

        $this->mockContainer = self::getMock(ContainerInterface::class, [], [], '', false);
        \Drupal::setContainer($this->mockContainer);
    }

    public function testGetFormId()
    {
        $form = new OpportunitiesForm();

        self::assertEquals('opportunity_search_form', $form->getFormId());
    }

    public function testBuildForm()
    {
        $form = new OpportunitiesForm();

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
        $form = new OpportunitiesForm();

        $formArray = [];
        $formState = new FormState();

        $formState->setValue('search', 'Valid Search');

        $form->validateForm($formArray, $formState);

        self::assertEquals(
            [],
            $formState->getErrors()
        );
    }

    public function testValidFormInvalid()
    {
        $form = new OpportunitiesForm();

        $formArray = [];
        $formState = new FormState();

        $formState->setValue('search', '');

        $form->validateForm($formArray, $formState);

        self::assertArrayHasKey('search', $formState->getErrors());
        self::assertArrayHasKey('key', $formState->getErrors()['search']);
        self::assertEquals('edit-search', $formState->getErrors()['search']['key']);
    }

    public function testSubmitForm()
    {
        $form = new OpportunitiesForm();

        $formArray = [];
        $formState = new FormState();

        $formState->setValue('search', '');

        $form->submitForm($formArray, $formState);

        self::assertFalse($formState->getRedirect());
    }
}