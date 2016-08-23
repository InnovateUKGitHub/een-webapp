<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpressionOfInterestForm extends FormBase
{
    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'opportunity_search_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form = [
            'description' => [
                '#type'                => 'textarea',
                '#title'               => t('Short description of your organisation, activities, products and services'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'interest'    => [
                '#type'                => 'textarea',
                '#title'               => t('What interests you about this opportunity and what do you expect of that organisation?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'more'        => [
                '#type'                => 'textarea',
                '#title'               => t('Is there anything further you would like to know about this opportunity?'),
                '#field_prefix'        => t('Why do EEN need this?'),
                '#description'         => t('Lorem ipsum'),
                '#description_display' => 'before',
                '#label_display'       => 'before',
                '#attributes'          => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Express your interest in this opportunity'),
                    '#button_type' => 'primary',
                ],
            ],
//            '#action'     => Url::fromRoute('opportunities.eoi')->toString(),
            '#method'     => Request::METHOD_POST,
        ];

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (strlen($form_state->getValue('description')) < 1) {
            $form_state->setErrorByName(
                'description',
                [
                    'key'  => 'edit-description',
                    'text' => t('The description is required.'),
                ]
            );
        }
        if (strlen($form_state->getValue('interest')) < 1) {
            $form_state->setErrorByName(
                'interest',
                [
                    'key'  => 'edit-interest',
                    'text' => t('The interest is required.'),
                ]
            );
        }
        if (strlen($form_state->getValue('description')) < 1) {
            $form_state->setErrorByName(
                'more',
                [
                    'key'  => 'edit-more',
                    'text' => t('The more is required.'),
                ]
            );
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        drupal_set_message($this->t('Your EOI has been submitted.'));
        $form_state->disableRedirect();
    }
}