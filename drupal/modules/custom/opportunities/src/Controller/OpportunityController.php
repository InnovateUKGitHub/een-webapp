<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\opportunities\Form\ExpressionOfInterest\EmailVerificationForm;
use Drupal\opportunities\Form\ExpressionOfInterest\ExpressionOfInterestForm;
use Drupal\een_common\Form\SignInForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunityController extends ControllerBase
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';
    const COUNTRY = 'country';
    const EMAIL = 'email';
    const TOKEN = 'token';

    /**
     * @var PrivateTempStore
     */
    private $session;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunityController constructor.
     *
     * @param OpportunitiesService    $service
     * @param PrivateTempStore        $session
     * @param SessionManagerInterface $sessionManager
     */
    public function __construct(
        OpportunitiesService $service,
        PrivateTempStore $session,
        SessionManagerInterface $sessionManager
    )
    {
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
     * @return OpportunityController
     */
    public static function create(ContainerInterface $container)
    {
        return new self(
            $container->get('opportunities.service'),
            $container->get('user.private_tempstore')->get('SESSION_ANONYMOUS'),
            $container->get('session_manager')
        );
    }

    /**
     * @param string  $profileId
     * @param string  $token
     * @param Request $request
     *
     * @return array
     */
    public function index($profileId, $token, Request $request)
    {

        $fids =  \Drupal::entityQuery('node')
            ->condition('type', 'partnering_opportunity')
            ->condition('field_opportunity_id', $profileId)
            ->execute();

        $id = array_shift(array_values($fids));

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($id)->toArray();

        $search = $request->query->get(self::SEARCH);
        $opportunityType = $request->query->get(self::OPPORTUNITY_TYPE);

        $country_list = \Drupal\Core\Locale\CountryManager::getStandardList();
        $country = array_search($results['field_country_of_origin'][0]['value'], $country_list);

        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);
        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['id']['#value'] = $profileId;

        $formLogin = \Drupal::formBuilder()->getForm(SignInForm::class);

        $this->session->set('id', $profileId);
        $this->checkSession($form, $token, $profileId);

        return [
            '#theme'            => 'opportunities_details',
            '#title'            => '',
            '#form_email'       => $formEmail,
            '#form_login'       => $formLogin,
            '#form'             => $form,
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $opportunityType,
            '#country'          => $country,
            '#token'            => $token != null && $token === $this->session->get('token'),
            '#email'            => $this->session->get('email'),
            '#mail'             => [
                'subject' => $results['title'][0]['value'],
                'body'    => $this->getMailToBody($request, $profileId),
            ],
        ];
    }




    public function getTitle($profileId, $token, Request $request){


        $fids =  \Drupal::entityQuery('node')
            ->condition('type', 'partnering_opportunity')
            ->condition('field_opportunity_id', $profileId)
            ->execute();

        $id = array_shift(array_values($fids));

        $entity_manager = \Drupal::entityManager();
        $results =  $entity_manager->getStorage('node')->load($id)->toArray();

        return $results['title'][0]['value'];
    }



    /**
     * @param array  $form
     * @param string $token
     * @param string $profileId
     */
    private function checkSession(&$form, $token, $profileId)
    {
        $emailSession = $this->session->get('email');
        $tokenSession = $this->session->get('token');

        if ($token != null && $token === $tokenSession) {

            $this->clearSession();

            $form['email']['#value'] = $emailSession;
            $form['email']['#attributes']['disabled'] = 'disabled';

            $form['#action'] = Url::fromRoute(
                'opportunities.details',
                [
                    'profileId' => $profileId,
                    'token'     => $token,
                ]
            )->toString();

            $contact = $this->service->createLead($emailSession);

            $this->session->set('isLoggedIn', true);
            $this->session->set('type', $contact['Contact_Status__c']);

            if ($contact['Contact_Status__c'] !== 'Lead') {
                $this->setSession($contact);
            }
        } else {
            $this->disableForm($form);
            $this->session->set('isLoggedIn', false);
        }
    }

    /**
     * Delete all the information stored in session
     */
    private function clearSession()
    {
        $this->session->delete('email-verification');
        $this->session->delete('description');
        $this->session->delete('interest');
        $this->session->delete('more');
        $this->session->delete('phone');

        $this->session->delete('step1');
        $this->session->delete('firstname');
        $this->session->delete('lastname');
        $this->session->delete('contact_email');
        $this->session->delete('contact_phone');
        $this->session->delete('newsletter');

        $this->session->delete('step2');
        $this->session->delete('company_name');
        $this->session->delete('company_number');
        $this->session->delete('no_company_number');
        $this->session->delete('website');
        $this->session->delete('company_phone');

        $this->session->delete('step3');
        $this->session->delete('postcode');
        $this->session->delete('addressone');
        $this->session->delete('addresstwo');
        $this->session->delete('city');
    }

    /**
     * @param array $form
     */
    private function disableForm(&$form)
    {
        $form['description']['#attributes']['disabled'] = 'disabled';
        $form['interest']['#attributes']['disabled'] = 'disabled';
        $form['more']['#attributes']['disabled'] = 'disabled';
        $form['email']['#attributes']['disabled'] = 'disabled';
        $form['phone']['#attributes']['disabled'] = 'disabled';
        $form['actions']['submit']['#attributes']['disabled'] = 'disabled';
    }

    /**
     * @param Request $request
     * @param string  $profileId
     *
     * @return string
     */
    private function getMailToBody(Request $request, $profileId)
    {
        return "Hello,

Here's a partnering opportunity I thought might be of interest:
" . $request->getSchemeAndHttpHost() . Url::fromRoute('opportunities.details', ['profileId' => $profileId])->toString() . "

It's on Enterprise Europe Network's website, the world's largest business support network, led by the European Commission.
";
    }

    /**
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function ajax($profileId, Request $request)
    {

        $this->session->set('id', $profileId);
        $email = $request->query->get(self::EMAIL);


        $type = 'opportunity';
        $fids =  \Drupal::entityQuery('node')
            ->condition('type', 'partnering_opportunity')
            ->condition('field_opportunity_id', $profileId)
            ->execute();

        $id = array_shift(array_values($fids));

        if(!$id){
            $fids =  \Drupal::entityQuery('node')
                ->condition('type', 'event')
                ->condition('nid', $profileId)
                ->execute();

            $id = array_shift(array_values($fids));
            if($id){
                $type = 'event';
            }
        }

        //Create lead on verify email.
        $this->service->createLead($email);

        $token =  mt_rand(100000, 999999);
        $this->session->set('email', $email);
        $this->session->set('token', $token);


        $this->service->verifyEmail(
            $email,
            $token,
            $type
        );

        return new JsonResponse(
            ['status' => 'success']
        );
    }

}
