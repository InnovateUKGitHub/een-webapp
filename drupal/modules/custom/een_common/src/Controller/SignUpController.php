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
use Drupal\een_common\Form\UnsubscribeForm;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\opportunities\Form\ExpressionOfInterest\EmailVerificationForm;
use Drupal\een_common\Form\VerifyCodeForm;
use Drupal\een_common\Service\ContactService;
use Drupal\een_common\Service\SalesforceService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use \Drupal\eoilog\EoiLogStorage;


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
     *
     * @var OpportunitiesService
     */
    private $sfService;

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
        SalesforceService $sfService,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
        $this->oppService = $oppService;
        $this->service = $service;
        $this->sfService = $sfService;
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
            $container->get('salesforce.service'),
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
            'update_type'   => $this->session->get('update_type'),
            'subjects'      => $this->session->get('subjects'),

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
            'sfaccount'          => $this->session->get('sfaccount'),
            'requestednewaddress' => $this->session->get('requestednewaddress')
        ];
    }



    public function steps($type, $id, Request $request)
    {

        if (($redirect = $this->isStepValid('email-verification', $type . '.details', $type, $this->session->get('token'))) !== null) {
            return $redirect;
        }

        $form = \Drupal::formBuilder()->getForm(SignUpStepsForm::class);
        $form['#action'] = Url::fromRoute('sign-up.steps', ['id' => $id, 'type' => $type])->toString();

        $user = $this->service->getContactV2($this->session->get('contact_email'));



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

            $form['sfaccount']['#value'] = $this->session->get('sfaccount');

            $this->addCheckboxAttributes($form, array($this->session->get('company_registered')), 'company_registered');
            $this->addCheckboxAttributes($form, $this->session->get('newsletter'), 'newsletter');
            $this->addCheckboxAttributes($form, $this->session->get('subjects'), 'subjects');
            $this->addCheckboxAttributes($form, $this->session->get('update_type'), 'update_type');
            $this->addCheckboxAttributes($form, $this->session->get('radiobutton'), 'radiobutton');
            $this->addCheckboxAttributes($form, array($this->session->get('alternative_address')), 'alternative_address', true);
            $this->addCheckboxAttributes($form, array($this->session->get('terms')), 'terms', true);
            $this->addCheckboxAttributes($form, array($this->session->get('create_account')), 'create_account');

            $accountOptions = false;
            if($user['Website_user_password__c'] != ''){
                $accountOptions = true;
            }
        }

        return [
            '#theme'        => 'sign_up_steps',
            '#form'         => $form,
            '#type'         => $type,
            '#accountoptions' => $accountOptions
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

        $values = array_values($fids);
        $nid = array_shift($values);

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($nid)->toArray();

        $form = $this->getSession($id, $results['title'][0]['value']);

        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['id']['#value'] = $id;
        $formEmail['emailverification']['#value'] = $form['email'];

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
        $values = array_values($fids);
        $nid = array_shift($values);

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($nid)->toArray();

        $form = $this->getSession($id, $results['title'][0]['value']);

        $user = $this->service->processUser($form);

        //add in contact information
        $form['org_contact_organisation'] = $results['field_contact_organisation'][0]['value'];
        $form['org_contact_consortium'] = $results['field_contact_consortium'][0]['value'];
        $form['org_contact_fullname'] = $results['field_contact_fullname'][0]['value'];
        $form['org_contact_phone'] = $results['field_contact_phone'][0]['value'];
        $form['org_contact_email'] = $results['field_contact_email'][0]['value'];

        

        if ($type === 'events') {

            if(isset($user['Account']['Name'])){
                $form['companyName'] = $user['Account']['Name'];
            } else {
                $form['companyName'] = $form['company_name'];
            }

            // delegate name
            if(isset($user['Account']['Name'])){
                $form['delegateName'] = $user['FirstName'].' '.$user['LastName'];
            } else {
                $form['delegateName'] = $form['first_name']. ' '. $form['last_name'];
            }

            // delegate email address
            if(isset($user['Email1__c'])){
                $form['delegateEmail'] = $user['Email1__c'];
            } else {
                $form['delegateEmail'] = $form['email'];
            }


            // event date
            $eventDate = date('d/m/Y', strtotime($results['field_event_date'][0]['value']));
            if($results['field_event_date'][0]['end_value'] && ($results['field_event_date'][0]['value'] != $results['field_event_date'][0]['end_value'])){
                $eventDate .= ' - '. date('d/m/Y', strtotime($results['field_event_date'][0]['end_value']));
            }
            $form['eventDate'] = $eventDate;

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

            if($user['Account']['Id'] == '0012400001OKNpv') {

                //send to holding account
                $sentToHoldingAccount = true;
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


    public function valid(){
        return new JsonResponse(
            [
                'loggedIn' => $this->session->get('isLoggedIn')
            ]
        );
    }



    public function login(Request $request)
    {

        $this->session->set('isLoggedIn', false);

        $formLogin = \Drupal::formBuilder()->getForm(SignInForm::class);

        if(\Drupal::request()->isXmlHttpRequest() && !$request->get('popover')) {
            $password = $this->service->hashPassword($request->get('password'));
            $email = strtolower($request->get('email'));
            $token = $request->get('token');
            $user = $this->service->createLead($email);

           if($token){
                if($token != $this->session->get('token')) {
                    return new JsonResponse(
                        ['status' => 'failure', 'message' => 'Your verification code is invalid.']
                    );
                } else {

                    EoiLogStorage::update(array(
                        'token' => $token,
                        'code_verified' => 'Yes '.date('Y-m-d H:i:s')
                    ));

                    $this->session->set('isLoggedIn', true);
                    $this->setSession($user);

                    $response = [
                        'success' => true
                    ];

                    if($this->session->get('link')){
                        $response['link'] = $this->session->get('link');
                    }

                    return new JsonResponse(
                        $response
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

        $duplicates = $this->sfService->getDuplicates($request->get('q'));

        $response = [];


        if($duplicates){
            foreach($duplicates as $item){
                $response['items'][] =  $item;
            }

            $response['total_results'] = count($duplicates);
        }


        return new JsonResponse($response);
    }


    private function setSession($contact)
    {
        $contactSession = $this->service->setContactSession($contact);

        foreach($contactSession as $key => $value){
            $this->session->set($key, $value);
        }
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




    public function unsubscribe()
    {

        $form = \Drupal::formBuilder()->getForm(UnsubscribeForm::class);

        return [
            '#theme' => 'unsubscribe',
            '#form' => $form
        ];
    }


}
