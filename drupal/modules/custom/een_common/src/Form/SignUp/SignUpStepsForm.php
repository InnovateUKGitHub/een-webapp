<?php
namespace Drupal\een_common\Form\SignUp;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class SignUpStepsForm extends AbstractForm
{
    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep1Form constructor.
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
     * @return SignUpStep1Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'));
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'sign_up_steps_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $types = [
            'BP' => t('Blog Posts'),
            'C'  => t('Consultations on EU law'),
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
            'L'  => t('London'),
            'M'  => t('Midlands'),
            'NE' => t('North England'),
            'NI' => t('Northern Ireland'),
            'SE' => t('South East England'),
            'SW' => t('South West England'),
            'W'  => t('Wales'),
        ];
        $radio = [
            'UK' => t('UK Newsletter'),
            'EE' => t('East of England'),
        ];

        $crchoice = [
            'yes' => t('Yes'),
            'no' => t('No')
        ];

        $accountchoice = [
            'no' => t('No'),
            'yes' => t('Yes')
        ];

        $form = [
            'firstname'     => [
                '#type'           => 'textfield',
                '#title'          => t('First name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-firstname',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The first name is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'given-name',
                    ],
                ],

            ],
            'lastname'      => [
                '#type'           => 'textfield',
                '#title'          => t('Last name'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-lastname',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The last name is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'family-name',
                    ],
                ],
            ],
            'contact_email' => [
                '#type'           => 'email',
                '#title'          => t('Your email address'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-email',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control disabled-input',
                    ],
                    'autocomplete' => [
                        'email',
                    ],
                    'readonly' => true,
                ],
            ],
            'contact_phone' => [
                '#type'           => 'textfield',
                '#title'          => t('Contact telephone number'),
                '#label_display'  => 'before',
                '#required'       => true,
                '#required_error' => [
                    'key'          => 'edit-contact-phone',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The telephone number is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'tel',
                    ],
                ],
            ],
            'newsletter'    => [
                '#type'    => 'checkboxes',
                '#title'   => t('<span tabindex="0">Sign up for the latest:</span>'),
                '#options' => $types,
            ],


            'create_account'    => [
                '#type'    => 'checkboxes',
                '#title'   => t('Would you like to create an account with EEN?'),
                '#options' => $crchoice,
                '#required'       => true,
                '#default_value' => 'no',
            ],

            'terms'    => [
                '#type'    => 'checkbox',
                '#title'   => t('I have read and accept the <a href="/terms-and-conditions" target="_blank">terms and conditions</a>'),
                '#required'       => true
            ],


            'company_registered'    => [
                '#type'    => 'radios',
                '#title'   => t('Is your company registered with Companies House?'),
                '#options' => $crchoice,
                '#required'       => true
            ],


            'company_name'   => [
                '#type'          => 'textfield',
                '#title'         => t('Company Name'),
                '#label_display' => 'before',
                '#prefix'        => '<span class="company-name-wrapper">',
                '#suffix'        => '</span>',
                '#required'       => true,
                '#attributes'    => [
                    'class'       => [
                        'form-control ch_search',
                    ],
                    'id'          => [
                        'ch_search',
                    ],
                    'auto-complete' => false
                ],
            ],

            'search'         => [
                '#type'                     => 'html_tag',
                '#title'                    => t('Search Companies House'),
                '#label_display'            => 'before',
                '#executes_submit_callback' => false,
                '#value'                    => 'Get company information',
                '#tag'                      => 'button',
                '#attributes'               => [
                    'class' => [
                        'form-control',
                    ],
                    'id'    => [
                        'ch-search-trigger',
                    ],
                ],
            ],


            'company_number' => [
                '#type'          => 'textfield',
                '#title'         => t('Company number'),
                '#label_display' => 'before',
                //'#description'         => $this->t("You can use the 'Search Companies House' button to lookup your registered number and registered address automatically"),
                //'#description_display' => 'after',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'website'        => [
                '#type'          => 'textfield',
                '#title'         => t('Company Website'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],
            'company_phone'  => [
                '#type'          => 'textfield',
                '#title'         => t('Company switchboard number'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'postcode_registered'   => [
                '#type'           => 'textfield',
                '#title'          => t('Registered address: Postcode'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-postcode',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The postcode is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control disabled-input',
                    ],
                    'autocomplete' => [
                        'shipping postal-code',
                    ],
                    'readonly' => true,
                ],
            ],
            'addressone_registered' => [
                '#type'           => 'textfield',
                '#title'          => t('Registered address: Address line 1'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-addressone',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The address is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control disabled-input',
                    ],
                    'autocomplete' => [
                        'shipping address-line1',
                    ],
                    'readonly' => true,
                ],
            ],
            'addresstwo_registered' => [
                '#type'          => 'textfield',
                '#title'         => t('Registered address: Address line 2'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class'        => [
                        'form-control disabled-input',
                    ],
                    'autocomplete' => [
                        'shipping address-line2',
                    ],
                    'readonly' => true,
                ],

            ],
            'city_registered'       => [
                '#type'           => 'textfield',
                '#title'          => t('Registered address: Town / City'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-city',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The city is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control disabled-input',
                    ],
                    'autocomplete' => [
                        'shipping locality',
                    ],
                    'readonly' => true,
                ],
            ],


            'alternative_address'    => [
                '#type'    => 'checkbox',
                '#title'   => t('Same as registered business address'),
                '#default_value' => 1,
            ],

            'postcode'   => [
                '#type'           => 'textfield',
                '#title'          => t('Enter your postcode'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-postcode',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The postcode is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control ',
                    ],
                    'autocomplete' => [
                        'shipping postal-code',
                    ],
                    'placeholder' => [
                        'Postcode',
                    ],
                ],
            ],
            'addressone' => [
                '#type'           => 'textfield',
                '#title'          => t('Address line 1'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-addressone',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The address is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'shipping address-line1',
                    ],
                ],
            ],
            'addresstwo' => [
                '#type'          => 'textfield',
                '#title'         => t('Address line 2'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'shipping address-line2',
                    ],
                ],
            ],
            'city'       => [
                '#type'           => 'textfield',
                '#title'          => t('Town / City'),
                '#label_display'  => 'before',
                '#required'       => false,
                '#required_error' => [
                    'key'          => 'edit-city',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The city is required to complete your application.'),
                ],
                '#attributes'     => [
                    'class'        => [
                        'form-control',
                    ],
                    'autocomplete' => [
                        'shipping locality',
                    ],
                ],
            ],
            'create_account'    => [
                '#type'    => 'radios',
                '#title'   => t('Would you like to create and account with EEN?'),
                '#options' => $accountchoice,
                '#required'       => true,
            ],
            'password'  => [
                '#type'            => 'password',
                '#title'           => t('Password'),
                '#label_display'   => 'before',
                '#required'        => false,
                '#required_error' => [
                    'key'          => 'edit-password',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The password is required to complete your application.'),
                ],
                '#attributes'     => [
                    'size'        => [
                        null,
                    ]
                ],

            ],

            'actions' => [
                '#type'  => 'actions',
                'submit' => [
                    '#type'        => 'submit',
                    '#value'       => $this->t('Save and continue'),
                    '#button_type' => 'primary',
                ],
            ],
            '#method' => Request::METHOD_POST,
        ];
        $form_state->setCached(false);

        return $form;
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        if (!parent::checkRegexField($form_state, self::EMAIL_REGEX, 'contact_email')) {
            $form_state->setErrorByName(
                'contact_email',
                [
                    'key'          => 'edit-contact-email',
                    'text'         => t('This is required to complete your application.'),
                    'general_text' => t('The email is required to complete your application.'),
                ]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $form_state->setRedirect(
            'sign-up.review',
            [
                'id'   => $this->session->get('id'),
                'type' => $this->session->get('type'),
            ]
        );

        $this->session->set('step1', true);
        $this->session->set('firstname', $form_state->getValue('firstname'));
        $this->session->set('lastname', $form_state->getValue('lastname'));
        $this->session->set('contact_email', $form_state->getValue('contact_email'));
        $this->session->set('contact_phone', $form_state->getValue('contact_phone'));
        $this->session->set('newsletter', $this->purgeValues($form_state->getValue('newsletter')));
        $this->session->set('radiobutton', $this->purgeValues($form_state->getValue('newsletter')));

        $this->session->set('step2', true);
        $this->session->set('company_registered', $form_state->getValue('company_registered'));
        $this->session->set('company_name', $form_state->getValue('company_name'));
        $this->session->set('company_number', $form_state->getValue('company_number'));
        $this->session->set('no_company_number', $form_state->getValue('no_company_number'));
        $this->session->set('website', $form_state->getValue('website'));
        $this->session->set('company_phone', $form_state->getValue('company_phone'));

        $this->session->set('step3', true);

        $this->session->set('alternative_address', $form_state->getValue('alternative_address'));

        $this->session->set('postcode', $form_state->getValue('postcode'));
        $this->session->set('addressone', $form_state->getValue('addressone'));
        $this->session->set('addresstwo', $form_state->getValue('addresstwo'));
        $this->session->set('city', $form_state->getValue('city'));

        $this->session->set('postcode_registered', $form_state->getValue('postcode_registered'));
        $this->session->set('addressone_registered', $form_state->getValue('addressone_registered'));
        $this->session->set('addresstwo_registered', $form_state->getValue('addresstwo_registered'));
        $this->session->set('city_registered', $form_state->getValue('city_registered'));
    }

    private function purgeValues($values)
    {
        return array_filter($values, function($value) {
            return !empty($value);
        });
    }
}
