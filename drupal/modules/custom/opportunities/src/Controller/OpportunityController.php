<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\Url;
use Drupal\opportunities\Form\ExpressionOfInterest\EmailVerificationForm;
use Drupal\opportunities\Form\ExpressionOfInterest\ExpressionOfInterestForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Drupal\user\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class OpportunityController extends ControllerBase
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';
    const COUNTRY = 'country';
    const EMAIL = 'email';

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
        $search = $request->query->get(self::SEARCH);
        $opportunityType = $request->query->get(self::OPPORTUNITY_TYPE);
        $country = $request->query->get(self::COUNTRY);

        $results = $this->service->get($profileId);


        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);
        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['id']['#value'] = $profileId;

        $this->checkSession($form, $token, $profileId);

        return [
            '#theme'            => 'opportunities_details',
            '#form_email'       => $formEmail,
            '#form'             => $form,
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $opportunityType,
            '#country'          => $country,
            '#token'            => $token != null && $token === $this->session->get('token'),
            '#email'            => $this->session->get('email'),
            '#mail'             => [
                'subject' => $results['_source']['title'],
                'body'    => $this->getMailToBody($request, $profileId),
            ],
        ];
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
        $this->session->delete('website');
        $this->session->delete('company_phone');

        $this->session->delete('step3');
        $this->session->delete('postcode');
        $this->session->delete('addressone');
        $this->session->delete('addresstwo');
        $this->session->delete('city');
    }

    /**
     * @param array $contact
     */
    private function setSession($contact)
    {
        if (isset($contact['Phone'])) {
            $this->session->set('phone', $contact['Phone']);
        }

        $this->session->set('step1', true);
        $this->session->set('firstname', $contact['FirstName']);
        $this->session->set('lastname', $contact['LastName']);
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
        $email = $request->query->get(self::EMAIL);
        $token = bin2hex(random_bytes(50));
        $this->session->set('email', $email);
        $this->session->set('token', $token);

        $this->service->verifyEmail(
            $email,
            $token,
            $profileId
        );

        return ['success' => true];
    }
}
