<?php
namespace Drupal\update_preferences\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Element\Html;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\een_common\Service\ContactService;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Drupal\update_preferences\Form\UpdatePreferencesForm;
use Drupal\update_preferences\Form\UpdatePreferencesNewUserForm;


class UpdatePreferencesController extends ControllerBase
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
     * @param array  $form
     * @param array  $fields
     * @param string $name
     */
    private function addCheckboxAttributes(&$form, $fields, $name, $singleCheckbox = false)
    {
        if (empty($fields) === false) {
            foreach ($fields as $field) {
                if($singleCheckbox){
                    $form[$name]['#attributes']['value'] = $field;
                    $form[$name]['#attributes']['checked'] = 'checked';
                } else {
                    $form[$name][$field]['#attributes']['checked'] = 'checked';
                }
            }
        }
    }


    public function index(Request $request)
    {



        if($request->isMethod('POST') && $request->get('userType')) {
            //This is a new signup - should not exist on salesforce
            drupal_set_message('Please check your inbox for a verification code to proceed', 'status');

            $form = \Drupal::formBuilder()->getForm(UpdatePreferencesNewUserForm::class);
            $email = strtolower($request->get('email'));

            if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $form['email']['#value'] = $email;
                $token = mt_rand(100000, 999999);
                $this->session->set('temptoken', $token);

                $this->oppService->verifyEmail($email, $token);
            }

        } else {

            $form = \Drupal::formBuilder()->getForm(UpdatePreferencesForm::class);

            $email = strtolower($request->get('email'));
            $id = $request->get('t');
            $contact = $this->service->getContactV2($email);

            $verified = false;
            if($contact){
                if(sha1($email.$contact['Id']) == $id){
                    $verified = true;
                }
            }
            if($verified && $contact){

                $query = \Drupal::entityQuery('node')
                    ->condition('type', 'client_options');
                $nids = $query->execute();
                $node = node_load(end($nids));

                $subjects = [];
                foreach ($node->get('field_subjects_of_interest')->getValue() as $interest) {
                    $value = explode('|', $interest['value']);
                    if($contact[$value[1]]){
                        $subjects[$value[1]] = $value[1];
                    }

                }

                $updatesWanted = [];
                foreach ($node->get('field_types_of_updates')->getValue() as $updates) {
                    $value = explode('|', $updates['value']);
                    if($contact[$value[1]]) {
                        $updatesWanted[$value[1]] = $value[1];
                    }
                }

                $types = [];
                if($node->get('field_newsletter_otions')->getValue()){
                    foreach ($node->get('field_newsletter_otions')->getValue() as $type) {
                        $value = explode('|', $type['value']);
                        if($contact[$value[1]]) {
                            $types[$value[1]] = $value[1];
                        }
                    }
                }

                $form['contact_id']['#value'] = $contact['Id'];
                $form['email']['#value'] = $email;

                $this->addCheckboxAttributes($form, $types, 'newsletter', false);
                $this->addCheckboxAttributes($form, $updatesWanted, 'update_type', false);
                $this->addCheckboxAttributes($form, $subjects, 'subjects', false);


                //get user
                $userId = $contact['Id'];

                if(!$userId) {
                    $alerts = null;
                } else {
                    $alerts = $this->service->getAlerts($userId);
                    $alerts = $alerts['records'];
                }



            } else {
                $form = NULL;
                drupal_set_message('Sorry, we are unable to load your preferences. Please try again later.', 'error');
            }
        }

        return [
            '#theme' => 'update_prefs_form',
            '#form'  => $form,
            '#description'  => '',
            '#alerts' => $alerts
        ];

    }


}
