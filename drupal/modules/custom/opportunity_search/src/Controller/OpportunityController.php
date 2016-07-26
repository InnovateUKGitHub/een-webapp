<?php

namespace Drupal\opportunity_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunity_search\Form\OpportunityForm;

class OpportunityController extends ControllerBase
{
    public function content()
    {
        $form = \Drupal::formBuilder()->getForm(OpportunityForm::class);

        return [
            '#theme'     => 'opportunity_search',
            '#form'      => $form,
            '#results'   => $form['results']['results'],
            '#total'     => $form['results']['total'],
            '#page'      => 5,
            '#pageTotal' => ceil($form['results']['total'] / 10),
        ];
    }
}