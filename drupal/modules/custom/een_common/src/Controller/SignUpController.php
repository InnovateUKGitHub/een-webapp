<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\een_common\Form\NewPasswordForm;
use Drupal\een_common\Form\PasswordLinkForm;
use Drupal\een_common\Form\SignUp\SignUpStep1Form;
use Drupal\een_common\Form\SignUp\SignUpStep2Form;
use Drupal\een_common\Form\SignUp\SignUpStep3Form;
use Drupal\een_common\Form\SignUp\SignUpStepsForm;
use Drupal\een_common\Form\SignInForm;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\opportunities\Form\ExpressionOfInterest\EmailVerificationForm;
use Drupal\een_common\Form\VerifyCodeForm;
use Drupal\een_common\Service\ContactService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class SignUpController extends ControllerBase
{
    const VALUE = 'value';

    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var ContactService
     */
    private $service;
    
    /**
     *
     * @var OpportunitiesService
     */
    private $oppService;

    /**
     * SignUpController constructor.
     *
     * @param OpportunitiesService    $oppService
     * @param ContactService          $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        OpportunitiesService $oppService,
        ContactService $service,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
        $this->oppService = $oppService;
        $this->service = $service;
        $this->session = $session;
        $this->sessionManager = $sessionManager;

        // TODO check if the user is connected when the login is implemented
        if (!isset($_SESSION['session_started'])) {
            $_SESSION['session_started'] = true;
            $this->sessionManager->start();
        }
    }

    /**
     * @param ContainerInterface $container
     *
     * @return SignUpController
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('opportunities.service'),
            $container->get('contact.service'),
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
    }

    /**
     * @throws UnauthorizedHttpException
     */
    private function isLoggedIn()
    {
        if (!$this->session->get('isLoggedIn')) {
            throw new UnauthorizedHttpException('');
        }
    }

    /**
     * @param string $step
     * @param string $redirect
     * @param string $id
     * @param null   $token
     *
     * @return null|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function isStepValid($step, $redirect, $id, $token = null)
    {
        if (!$this->session->get($step)) {
            if ($redirect === 'opportunities.details') {
                $params = ['profileId' => $id];
            } else if ($redirect === 'events.details') {
                $params = ['eventId' => $id];
            } else {
                $params = ['id' => $id];
            }
            if ($token !== null) {
                $params['token'] = $token;
            }

            return $this->redirect(
                $redirect,
                $params
            );
        }

        return null;
    }

    /**
     * @param array  $form
     * @param array  $fields
     * @param string $name
     */
    private function addCheckboxAttributes(&$form, $fields, $name, $singleCheckbox = false)
    {
        if (empty($fields) === false) {
            foreach ($fields as $field) {
                if($singleCheckbox && $this->session->get($name)){
                    $form[$name]['#attributes']['value'] = $field;
                    $form[$name]['#attributes']['checked'] = $field;
                } else {
                    $form[$name][$field]['#attributes']['checked'] = 'checked';
                }
            }
        }
    }

    /**
     * @param string $id
     * @param string $title
     *
     * @return array
     */
    private function getSession($id, $title)
    {
        return [
            'id'               => $id,
            'title'            => $title,
            'reference_number' => $this->session->get('reference_number'),

            'dietary'     => $this->session->get('dietary'),
            'description' => $this->session->get('description'),
            'interest'    => $this->session->get('interest'),
            'more'        => $this->session->get('more'),
            'phone'       => $this->session->get('phone'),
            'email'       => $this->session->get('email'),

            'firstname'     => $this->session->get('firstname'),
            'lastname'      => $this->session->get('lastname'),
            'contact_email' => $this->session->get('contact_email'),
            'contact_phone' => $this->session->get('contact_phone'),
            'newsletter'    => $this->session->get('newsletter'),

            'company_name'   => $this->session->get('company_name'),
            'company_number' => $this->session->get('company_number'),
            'no_company_number'  => $this->session->get('no_company_number'),
            'website'        => $this->session->get('website'),
            'company_phone'  => $this->session->get('company_phone'),


            'alternative_address'   => $this->session->get('alternative_address'),
            'company_registered'   => $this->session->get('company_registered'),

            'postcode'   => $this->session->get('postcode'),
            'addressone' => $this->session->get('addressone'),
            'addresstwo' => $this->session->get('addresstwo'),
            'city'       => $this->session->get('city'),
            'region'     => $this->session->get('region'),

            'postcode_registered'   => $this->session->get('postcode_registered'),
            'addressone_registered' => $this->session->get('addressone_registered'),
            'addresstwo_registered' => $this->session->get('addresstwo_registered'),
            'city_registered'       => $this->session->get('city_registered'),
            'region_registered'     => $this->session->get('region_registered'),

            'terms'              => $this->session->get('terms'),
            'create_account'     => $this->session->get('create_account'),
            'password'           => $this->session->get('password'),
        ];
    }



    public function steps($type, $id, Request $request)
    {

        if (($redirect = $this->isStepValid('email-verification', $type . '.details', $type, $this->session->get('token'))) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStepsForm::class);
        $form['#action'] = Url::fromRoute('sign-up.steps', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['firstname']['#value'] = $this->session->get('firstname');
            $form['lastname']['#value'] = $this->session->get('lastname');

            $form['contact_email']['#value'] = $this->session->get('contact_email');
            if (empty($form['contact_email']['#value'])) {
                $form['contact_email']['#value'] = $this->session->get('email');
            }
            $form['contact_phone']['#value'] = $this->session->get('contact_phone');
            if (empty($form['contact_phone']['#value'])) {
                $form['contact_phone']['#value'] = $this->session->get('phone');
            }

            $form['company_registered']['#value'] = $this->session->get('company_registered');
            $form['company_name']['#value'] = $this->session->get('company_name');
            $form['company_number']['#value'] = $this->session->get('company_number');
            $form['no_company_number']['#return_value'] = $this->session->get('no_company_number');
            $form['website']['#value'] = $this->session->get('website');
            $form['company_phone']['#value'] = $this->session->get('company_phone');

            $form['alternative_address']['#value'] = $this->session->get('alternative_address');
            $form['postcode']['#value'] = $this->session->get('postcode');
            $form['addressone']['#value'] = $this->session->get('addressone');
            $form['addresstwo']['#value'] = $this->session->get('addresstwo');
            $form['city']['#value'] = $this->session->get('city');

            $form['postcode_registered']['#value'] = $this->session->get('postcode_registered');
            $form['addressone_registered']['#value'] = $this->session->get('addressone_registered');
            $form['addresstwo_registered']['#value'] = $this->session->get('addresstwo_registered');
            $form['city_registered']['#value'] = $this->session->get('city_registered');

            $this->addCheckboxAttributes($form, array($this->session->get('company_registered')), 'company_registered');
            $this->addCheckboxAttributes($form, $this->session->get('newsletter'), 'newsletter');
            $this->addCheckboxAttributes($form, $this->session->get('radiobutton'), 'radiobutton');
            $this->addCheckboxAttributes($form, array($this->session->get('alternative_address')), 'alternative_address', true);
            $this->addCheckboxAttributes($form, array($this->session->get('terms')), 'terms', true);
            $this->addCheckboxAttributes($form, array($this->session->get('create_account')), 'create_account');
        }

        return [
            '#theme'        => 'sign_up_steps',
            '#form'         => $form,
            '#type'         => $type,
        ];
    }



    /**
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step1($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('email-verification', $type . '.details', $type, $this->session->get('token'))) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep1Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step1', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['firstname']['#value'] = $this->session->get('firstname');
            $form['lastname']['#value'] = $this->session->get('lastname');

            $form['contact_email']['#value'] = $this->session->get('contact_email');
            if (empty($form['contact_email']['#value'])) {
                $form['contact_email']['#value'] = $this->session->get('email');
            }
            $form['contact_phone']['#value'] = $this->session->get('contact_phone');
            if (empty($form['contact_phone']['#value'])) {
                $form['contact_phone']['#value'] = $this->session->get('phone');
            }

            $this->addCheckboxAttributes($form, $this->session->get('newsletter'), 'newsletter');
            $this->addCheckboxAttributes($form, $this->session->get('radiobutton'), 'radiobutton');
        }

        return [
            '#theme' => 'sign_up_step1',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step2($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step1', $type . '.step1', $id)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep2Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step2', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['company_name']['#value'] = $this->session->get('company_name');
            $form['company_number']['#value'] = $this->session->get('company_number');
            $form['no_company_number']['#return_value'] = $this->session->get('no_company_number');
            $form['website']['#value'] = $this->session->get('website');
            $form['company_phone']['#value'] = $this->session->get('company_phone');
        }

        return [
            '#theme' => 'sign_up_step2',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string  $type
     * @param string  $id
     * @param Request $request
     *
     * @return array
     */
    public function step3($type, $id, Request $request)
    {
        $this->isLoggedIn();
        if (($redirect = $this->isStepValid('step2', $type . '.step2', $id)) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStep3Form::class);
        $form['#action'] = Url::fromRoute('sign-up.step3', ['id' => $id, 'type' => $type])->toString();

        if ($request->isMethod(Request::METHOD_GET)) {
            $form['postcode']['#value'] = $this->session->get('postcode');
            $form['addressone']['#value'] = $this->session->get('addressone');
            $form['addresstwo']['#value'] = $this->session->get('addresstwo');
            $form['city']['#value'] = $this->session->get('city');
        }

        return [
            '#theme' => 'sign_up_step3',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return array
     */
    public function review($type, $id)
    {

        if (($redirect = $this->isStepValid('step3', $type . '.step3', $id)) !== null) {
          return $redirect;
        }

        if($type == 'events'){
            $fids =  \Drupal::entityQuery('node')
                ->condition('type', 'event')
                ->condition('nid', $id)
                ->execute();
        } else {
            $fids =  \Drupal::entityQuery('node')
                ->condition('type', 'partnering_opportunity')
                ->condition('field_opportunity_id', $id)
                ->execute();
        }

        $nid = array_shift(array_values($fids));

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($nid)->toArray();

        $form = $this->getSession($id, $results['title'][0]['value']);

        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['id']['#value'] = $id;

        $loggedIn = false;
        if ($this->session->get('isLoggedIn')) {
            $loggedIn = true;
        }

        return [
            '#theme'        => $type == 'opportunities' ? 'sign_up_review' : 'sign_up_review_event',
            '#form'         => $form,
            '#formemail'    => $formEmail,
            '#type'         => $type,
            '#loggedIn'     => $loggedIn
        ];
    }
    
    /**
     * @param string $type
     * @param string $id
     *
     * @return array
     */
    public function complete($type, $id)
    {
        if (($redirect = $this->isStepValid('step3', $type . '.step3', $id)) !== null) {
            return $redirect;
        }

        if($type == 'events'){
            $fids =  \Drupal::entityQuery('node')
                ->condition('type', 'event')
                ->condition('nid', $id)
                ->execute();
        } else {
            $fids =  \Drupal::entityQuery('node')
                ->condition('type', 'partnering_opportunity')
                ->condition('field_opportunity_id', $id)
                ->execute();
        }

        $nid = array_shift(array_values($fids));

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($nid)->toArray();

        $form = $this->getSession($id, $results['title'][0]['value']);



        $user = $this->service->convertLead($form);

        //add in contact information
        $form['org_contact_organisation'] = $results['field_contact_organisation'][0]['value'];
        $form['org_contact_consortium'] = $results['field_contact_consortium'][0]['value'];
        $form['org_contact_fullname'] = $results['field_contact_fullname'][0]['value'];
        $form['org_contact_phone'] = $results['field_contact_phone'][0]['value'];
        $form['org_contact_email'] = $results['field_contact_email'][0]['value'];
        
        if ($type === 'events') {
            $data = [
                'contact' => $user['Id'],
                'event'   => $results['field_salesforce_id'][0]['value'],
                'dietary' => $form['dietary'],
            ];
            $this->service->registerToEvent($data);
            $this->oppService->sendEventRegistrationNotificationEmail($form, $results);
        }

        if ($type === 'opportunities') {

            $sentToHoldingAccount = false;

            if(!isset($user['Account']['Id'])) {

                //send to holding account
                $sentToHoldingAccount = true;
                $user['Account']['Id'] = '0012400001OKNpv';

                $message = "Missing account details, or account could not be created";

                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => $message,
                        '%data' => json_encode($data).json_encode($form)
                    )
                );
            }


            $data = [
                'account' => $user['Account']['Id'],
                'profile' => $form['id'],
                'description' => $form['description'],
                'interest' => $form['interest'],
                'more' => $form['more'],
            ];


            try {

                $result = $this->service->submitEoi($data);

                if (isset($result['error'])) {
                    $message = "Eoi could not be created, Unknown error from SalesForce";

                    \Drupal::logger('salesforce')->warning('%title %data',
                        array(
                            '%title' => $message,
                            '%data' => json_encode($data).json_encode($form)
                        )
                    );
                    $data['message'] = $message;

                    $this->oppService->sendApiFailureEmail('contact@enterprise-europe.co.uk', $data, $form);

                    if(time() - strtotime($user['CreatedDate']) < 300) {
                        $contactId = $user['Id'];
                        $this->service->deleteContact($form, $contactId);
                    }
                }

                if($sentToHoldingAccount){
                    $data['message'] = 'EOI has been saved in the holding Account and needs assigning to the correct Account';
                    $this->oppService->sendApiFailureEmail('contact@enterprise-europe.co.uk', $data, $form);
                }

            } catch (Exception $e) {

            }

            // Send email to EEN team to inform of Expression of Interest
            $token =  mt_rand(100000, 999999);
            $this->oppService->sendNotificationEmail('contact@enterprise-europe.co.uk', $token, $id, $form);
        }

        return [
            '#theme' => $type == 'opportunities' ? 'sign_up_complete' : 'sign_up_complete_event',
            '#form'  => $form,
            '#type'  => $type,
        ];
    }

    /**
     * @param string  $field
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($field, Request $request)
    {
        $value = $request->get(self::VALUE);

        $this->session->set($field, $value);

        return new JsonResponse(
            [
                'success' => true,
                'params'  => $value,
            ]
        );
    }



    public function login(Request $request)
    {
        $this->session->set('isLoggedIn', false);

        $formLogin = \Drupal::formBuilder()->getForm(SignInForm::class);

        if(\Drupal::request()->isXmlHttpRequest() && !$request->get('popover')) {
            $password = $this->service->hashPassword($request->get('password'));
            $email = $request->get('email');
            $token = $request->get('token');
            $user = $this->service->createLead($email);

           if($token){
                if($token != $this->session->get('token')) {
                    return new JsonResponse(
                        ['status' => 'failure', 'message' => 'Your verification code is invalid.']
                    );
                } else {
                    $this->session->set('isLoggedIn', true);
                    $this->setSession($user);

                    return new JsonResponse(
                        [
                            'success' => true
                        ]
                    );
                }
            }

            if($user['Website_user_password__c'] == $password) {
                $this->session->set('isLoggedIn', true);
                $this->setSession($user);

                return new JsonResponse(
                    [
                        'success' => true
                    ]
                );
            }


            return new JsonResponse(
                [
                    'success' => false,
                    'status' => 'failure',
                    'message' => 'Your email / password combination does not match. Please try again or reset your password.'
                ]
            );
        }

        return [
            '#theme' => 'login',
            '#form'  => $formLogin,
        ];

    }


    public function logout()
    {
        setcookie('loggedIn', false);

        //@todo clear sessions
        $this->session->set('email', '');

        drupal_set_message('You have been logged out');

        return $this->redirect(
            'login',
            array()
        );
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function companies(Request $request)
    {
        return new JsonResponse($this->service->getCompaniesList($request->get('q')));
    }


    private function setSession($contact)
    {

        $this->session->set('type', $contact['Contact_Status__c']);

        if (isset($contact['Phone'])) {
            $this->session->set('phone', $contact['Phone']);
        }

        $this->session->set('step1', true);
        $this->session->set('firstname', $contact['FirstName']);
        $this->session->set('lastname', $contact['LastName']);
        $this->session->set('email', $contact['Email1__c']);
        $this->session->set('contact_email', $contact['Email']);
        $this->session->set('contact_phone', $contact['MobilePhone']);

        $this->session->set('step2', true);
        $this->session->set('company_name', $contact['Account']['Name']);
        if (isset($contact['Account']['Company_Registration_Number__c'])) {
            $this->session->set('company_number', $contact['Account']['Company_Registration_Number__c']);
        }
        if (isset($contact['Account']['Website'])) {
            $this->session->set('website', $contact['Account']['Website']);
        }
        if (isset($contact['Account']['Phone'])) {
            $this->session->set('company_phone', $contact['Account']['Phone']);
        }

        $this->session->set('step3', true);
        if (isset($contact['MailingPostalCode'])) {
            $this->session->set('postcode', $contact['MailingPostalCode']);
        }
        if (isset($contact['MailingStreet'])) {
            $this->session->set('addressone', $contact['MailingStreet']);
        }
        if (isset($contact['MailingCity'])) {
            $this->session->set('city', $contact['MailingCity']);
        }
        if (isset($contact['Account']['BillingPostalCode'])) {
            $this->session->set('postcode_registered', $contact['Account']['BillingPostalCode']);
            $this->session->set('company_registered', 'yes');
        }
        if (isset($contact['Account']['BillingStreet'])) {
            $this->session->set('addressone_registered', $contact['Account']['BillingStreet']);
        }
        if (isset($contact['Account']['BillingCity'])) {
            $this->session->set('city_registered', $contact['Account']['BillingCity']);
        }

        if (isset($contact['Account']['BillingPostalCode'])) {
            $this->session->set('alternative_address', true);
        }

        $activeNewsletters = array();
        if($contact['Blogs_New_data__c'] == 1){            $activeNewsletters['Blogs_New_data__c'] = 'Blogs_New_data__c'; }
        if($contact['Consultations_New_data__c'] == 1){    $activeNewsletters['Consultations_New_data__c'] = 'Consultations_New_data__c'; }
        if($contact['National_New_data__c'] == 1){         $activeNewsletters['National_New_data__c'] = 'National_New_data__c'; }
        if($contact['East_New_data__c'] == 1){             $activeNewsletters['East_New_data__c'] = 'East_New_data__c'; }
        if($contact['London_New_data__c'] == 1){           $activeNewsletters['London_New_data__c'] = 'London_New_data__c'; }
        if($contact['Midlands_New_data__c'] == 1){         $activeNewsletters['Midlands_New_data__c'] = 'Midlands_New_data__c'; }
        if($contact['North_New_data__c'] == 1){            $activeNewsletters['North_New_data__c'] = 'North_New_data__c'; }
        if($contact['NI_New_data__c'] == 1){               $activeNewsletters['NI_New_data__c'] = 'NI_New_data__c'; }
        if($contact['South_East_New_data__c'] == 1){       $activeNewsletters['South_East_New_data__c'] = 'South_East_New_data__c'; }
        if($contact['South_West_New_data__c'] == 1){       $activeNewsletters['South_West_New_data__c'] = 'South_West_New_data__c'; }
        if($contact['Wales_New_data__c'] == 1){            $activeNewsletters['Wales_New_data__c'] = 'Wales_New_data__c'; }

        $this->session->set('newsletter', $activeNewsletters);
        $this->session->set('create_account', 1);
        $this->session->set('terms', 1);
    }

    /*
     * Reset the password..
     */
    public function reset(Request $request)
    {

        $form = \Drupal::formBuilder()->getForm(PasswordLinkForm::class);

        $token = mt_rand(100000, 999999);
        $email = $request->get('email');



        if($email){
            $user = $this->service->createLead($email);
            if($user['Contact_Status__c'] == 'Client' || $user['Contact_Status__c'] == 'Lead'){

                $this->session->set('password-reset', $this->service->hashPassword($token));
                $this->session->set('password-reset-email', $email);
                $this->session->set('password-reset-verified', null);

                $this->service->passwordReset(
                    $email,
                    $token
                );
                drupal_set_message('Please check your email for your password reset code');
            } else {

                drupal_set_message('We could not find your account');

                return $this->redirect(
                    'reset-password',
                    array()
                );
            }

            return $this->redirect(
                'reset-password-enter',
                array()
            );
        }

        return [
            '#theme' => 'reset_password',
            '#form'  => $form,
        ];
    }

    /*
     * Reset the password..
     */
    public function verifyPassword(Request $request)
    {
        if(!$this->session->get('password-reset')){
            return $this->redirect(
                'reset-password',
                array()
            );
        };


        $form = \Drupal::formBuilder()->getForm(VerifyCodeForm::class);
        return [
            '#theme' => 'reset_password_verify',
            '#form'  => $form,
        ];
    }

    public function newPassword(Request $request)
    {
        $passwordform = \Drupal::formBuilder()->getForm(NewPasswordForm::class);

        if(!$this->session->get('password-reset-verified')){
            return $this->redirect(
                'reset-password',
                array()
            );
        };

        return [
            '#theme' => 'reset_password_new',
            '#passwordform' => $passwordform
        ];
    }


}
