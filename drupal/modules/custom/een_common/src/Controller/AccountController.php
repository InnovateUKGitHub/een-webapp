<?php
namespace Drupal\een_common\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\een_common\Form\PasswordLinkForm;
use Drupal\een_common\Form\SignUp\SignUpStep1Form;
use Drupal\een_common\Form\SignUp\SignUpStep2Form;
use Drupal\een_common\Form\SignUp\SignUpStep3Form;
use Drupal\een_common\Form\SignUp\SignUpStepsForm;
use Drupal\een_common\Form\SignInForm;
use Drupal\message_notify\Exception\MessageNotifyException;
use Drupal\opportunities\Form\ExpressionOfInterest\EmailVerificationForm;
use Drupal\een_common\Service\ContactService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AccountController extends ControllerBase
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

    public function index(Request $request)
    {
        if(!$this->session->get('isLoggedIn')){
            return $this->redirect(
                'login',
                array()
            );
        }

        //get user
        $user = $this->service->getContactV2($this->session->get('email'));
        $alerts = $this->service->getAlerts($user['Id']);



        if (\Drupal::request()->query->get('update') == 1) {

            $form = $this->getSession(null, null);
            unset($form['password']);

            $this->service->processUser($form);
            return $this->redirect(
                'my-account',
                array()
            );
        }

        return [
            '#alerts' => $alerts['records'],
            '#theme' => 'my_account',
            '#userdetails'  => $user
        ];
    }

    public function edit(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(SignUpStepsForm::class);

        if(!$this->session->get('isLoggedIn')){
            return $this->redirect(
                'login',
                array()
            );
        }


        $form['#action'] = Url::fromRoute('my-account-edit', [])->toString();


        if ($request->isMethod(Request::METHOD_GET)) {


            $contact = $this->service->getContactV2($this->session->get('email'));
            $contactSession = $this->service->setContactSession($contact);
            foreach($contactSession as $key => $value){
                $this->session->set($key, $value);
            }

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

            //remove these form elements for editing.
            unset($form['create_account']);
            unset($form['password']);
        }

        return [
            '#theme' => 'my_account_edit',
            '#form'  => $form,
        ];
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

}
