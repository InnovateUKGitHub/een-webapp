<?php
namespace Drupal\een_common\Form\SignUp;

use Drupal\Core\Form\FormStateInterface;
use Drupal\opportunities\Form\AbstractForm;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\een_common\Service\ContactService;

class SignUpStepsForm extends AbstractForm
{
    /**
     * @var ContactService
     */
    private $service;

    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * SignUpStep1Form constructor.
     *
     * @param PrivateTempStore $session
     */
    public function __construct(
        PrivateTempStore $session,
        ContactService $service
    )
    {
        $this->session = $session;
        $this->service = $service;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SignUpStep1Form
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('contact.service')
        );
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


        $query = \Drupal::entityQuery('node')
            ->condition('type', 'client_options');
        $nids = $query->execute();
        $node = node_load(end($nids));

        $subjects = [];
        foreach ($node->get('field_subjects_of_interest')->getValue() as $interest) {
            $value = explode('|', $interest['value']);
            $subjects[$value[1]] = $value[0];
        }

        $updatesWanted = [];
        foreach ($node->get('field_types_of_updates')->getValue() as $updates) {
            $value = explode('|', $updates['value']);
            $updatesWanted[$value[1]] = $value[0];
        }


        if($node->get('field_newsletter_otions')->getValue()){
            $types = [];
            foreach ($node->get('field_newsletter_otions')->getValue() as $type) {
                $value = explode('|', $type['value']);
                $types[$value[1]] = $value[0];
            }
        }


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
                    'placeholder' => 'So we can get in touch'
                ],
            ],
            'newsletter'    => [
                '#type'    => 'checkboxes',
                '#title'   => t('<span tabindex="0">Sign up for the latest:</span>'),
                '#options' => $types,
            ],

            'subjects'    => [
                '#type'    => 'checkboxes',
                '#required'=> false,
                '#title'   => t('<span tabindex="0">Subjects of interest:</span>'),
                '#options' => $subjects,
            ],

            'update_type'    => [
                '#type'    => 'checkboxes',
                '#title'   => t('<span tabindex="0">What types of updates do you want from us?:</span>'),
                '#options' => $updatesWanted,
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

            'sfaccount'  => [
                '#type'          => 'hidden',
                '#title'         => t('Linked Account'),
                '#label_display' => 'before',
                '#attributes'    => [
                    'class' => [
                        'form-control',
                    ],
                ],
            ],

            'postcode_registered'   => [
                '#type'           => 'hidden',
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
                        'off',
                    ],
                    'readonly' => true,
                ],
            ],
            'addressone_registered' => [
                '#type'           => 'hidden',
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
                '#type'          => 'hidden',
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
                '#type'           => 'hidden',
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
                '#default_value' => 1
            ],

            'postcode'   => [
                '#type'           => 'textfield',
                '#title'          => t('Your postcode'),
                '#label_display'  => 'before',
                '#required'       => true,
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
                        'nope',
                    ],
                    'placeholder' => [
                        'So an advisor local to you can get in touch',
                    ],
                ],
            ],
            'addressone' => [
                '#type'           => 'textfield',
                '#title'          => t('Address line 1'),
                '#label_display'  => 'before',
                '#required'       => true,
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
                '#required'       => true,
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
                '#title'   => t('Create a password for next time?'),
                '#options' => $accountchoice,
                '#required'       => false,
                '#default_value' => 'no',
            ],

            'terms'    => [
                '#type'    => 'checkbox',
                '#title'   => t('I have read and accept the <a href="/privacy-notice" target="_blank">privacy notice policy</a>'),
                '#required'       => true
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
                    '#value'       => $this->t('Submit'),
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
        $route = \Drupal::routeMatch()->getRouteName();
        if($route == 'my-account-edit'){
            drupal_set_message('Your details have been updated');
            $form_state->setRedirect(
                'my-account',
                ['update' => true]
            );
        } else {
            $form_state->setRedirect(
                'sign-up.complete',
                [
                    'id'   => $this->session->get('id'),
                    'type' => $this->session->get('type'),
                ]
            );
        }



        ////reset

        if(!$form_state->getValue('sfaccount')){
            $this->session->set('sfaccount', NULL);
        }
        $this->session->set('requestednewaddress', NULL);
        $this->session->set('postcode', NULL);
        $this->session->set('addressone', NULL);
        $this->session->set('addresstwo', NULL);
        $this->session->set('city', NULL);

        /// end of reset


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

        //if linking up to a pre-existing account
        if($form_state->getValue('sfaccount')){
            $this->session->set('sfaccount', $form_state->getValue('sfaccount'));
        }


        if($form_state->getValue('sfaccount')){

            $account = $this->service->getAccount(base64_decode($form_state->getValue('sfaccount')));

            $this->session->set('postcode_registered', $account['BillingPostalCode']);
            $this->session->set('addressone_registered', $account['BillingStreet']);
            $this->session->set('addresstwo_registered', $account['BillingStreet']);
            $this->session->set('city_registered', $account['BillingCity']);

            $requestedNewAddress = $form_state->getValue('company_name'). ', '
                . $form_state->getValue('addressone'). ', '
                . $form_state->getValue('addresstwo'). ', '
                . $form_state->getValue('city'). ', '
                . $form_state->getValue('postcode');

            $this->session->set('requestednewaddress', $requestedNewAddress);

            $this->session->set('postcode', $form_state->getValue('postcode'));
            $this->session->set('addressone', $form_state->getValue('addressone'));
            $this->session->set('addresstwo', $form_state->getValue('addresstwo'));
            $this->session->set('city', $form_state->getValue('city'));

        } else {

            $this->session->set('postcode_registered', $form_state->getValue('postcode'));
            $this->session->set('addressone_registered', $form_state->getValue('addressone'));
            $this->session->set('addresstwo_registered', $form_state->getValue('addresstwo'));
            $this->session->set('city_registered', $form_state->getValue('city'));

            $this->session->set('postcode', $form_state->getValue('postcode'));
            $this->session->set('addressone', $form_state->getValue('addressone'));
            $this->session->set('addresstwo', $form_state->getValue('addresstwo'));
            $this->session->set('city', $form_state->getValue('city'));
        }

        $this->session->set('update_type', $form_state->getValue('update_type'));
        $this->session->set('subjects', $form_state->getValue('subjects'));

        $this->session->set('terms', $form_state->getValue('terms'));
        $this->session->set('create_account', $form_state->getValue('create_account'));
        if($form_state->getValue('password')) {
            $this->session->set('password', $this->service->hashPassword($form_state->getValue('password')));
        }

        try {
            $postcode = json_decode(file_get_contents('https://api.postcodes.io/postcodes/'.$form_state->getValue('postcode')), true);

            if($postcode['status'] == 200){

                if($postcode['result']['region'] == 'Yorkshire and The Humber' || $postcode['result']['region'] == 'North West' || $postcode['result']['region'] == 'North East'){
                    $postcode['result']['region'] = 'North';
                }
                if(!$postcode['result']['region']) {
                    $postcode['result']['region'] = $postcode['result']['country'];
                }

                $this->session->set('region', $postcode['result']['region']);
            }
        } catch (Exception $e) {

        }
    }

    private function purgeValues($values)
    {
        return array_filter($values, function($value) {
            return !empty($value);
        });
    }
}
