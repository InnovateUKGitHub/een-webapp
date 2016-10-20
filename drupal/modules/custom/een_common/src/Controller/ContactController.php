<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\een_common\Form\ContactForm;

class ContactController extends ControllerBase
{
    /**
     * @return array
     */
    public function index()
    {
        $form = \Drupal::formBuilder()->getForm(ContactForm::class);

        return [
            '#theme' => 'contact_form',
            '#form'  => $form,
        ];
    }
}
