<?php
namespace Drupal\opportunities\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Controller\OpportunityController;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ExpressionOfInterestForm extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * ExpressionOfInterestForm constructor.
     *
     * @param PrivateTempStore $session
     */
    public function __construct(PrivateTempStore $session)
    {
        $this->session = $session;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return ExpressionOfInterestForm
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('user.private_tempstore')->get(OpportunityController::SESSION));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'expression_of_interest_form';
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
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'This is your pitch: remember to include your unique selling points (USP) and why someone would want to do business with you',
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
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'Tell us why you are a good fit for this opportunity, and why you think you\'re the right people for this partnership',
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
                    'class'       => [
                        'form-control',
                    ],
                    'placeholder' => [
                        'If there\'s anything additional, or commercially sensitive you\'d like to know about this opportunity, please let us know',
                    ],
                ],
            ],
            'email'       => [
                '#type'          => 'textfield',
                '#title'         => t('Your Email'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'other_email' => [
                '#type'          => 'textfield',
                '#title'         => t('Other email addresses (optional)'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phone'       => [
                '#type'          => 'textfield',
                '#title'         => t('Phone number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'phoneStatus' => [
                '#type'       => 'hidden',
                '#attributes' => [
                    'class' => [
                        'phoneStatus',
                    ],
                ],
            ],
            'actions'     => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Submit your application'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method'     => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        parent::checkRequireField($form_state, 'description', t('A short description of your organisation is required to complete your application.'));
        parent::checkRequireField($form_state, 'interest', t('Details of your interest in this opportunity are required to complete your application.'));
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'opportunities.eoi.step1',
            [
                'profileId' => $this->session->get('profileId'),
            ]
        );

        $this->session->set('other_email', $form_state->getValue('other_email'));
        $this->session->set('description', $form_state->getValue('description'));
        $this->session->set('interest', $form_state->getValue('interest'));
        $this->session->set('more', $form_state->getValue('more'));
        $this->session->set('phone', $form_state->getValue('phone'));
    }
}