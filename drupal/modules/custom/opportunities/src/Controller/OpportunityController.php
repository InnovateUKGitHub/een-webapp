<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\opportunities\Form\EmailVerificationForm;
use Drupal\opportunities\Form\ExpressionOfInterestForm;
use Drupal\opportunities\Service\OpportunitiesService;
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
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunityController constructor.
     *
     * @param OpportunitiesService $service
     */
    public function __construct(OpportunitiesService $service)
    {
        $this->service = $service;
    }

    /**
     * @param ContainerInterface $container
     *
     * @return OpportunityController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('opportunities.service'));
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
        $email = $request->query->get(self::EMAIL);

        $results = $this->service->get($profileId);

        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);
        $formEmail = \Drupal::formBuilder()->getForm(EmailVerificationForm::class);
        $formEmail['profile-id']['#value'] = $profileId;

        if ($token === null) {
            $this->disableForm($form);
        } else {
            $form['email']['#value'] = $email;
            $form['email']['#attributes']['disabled'] = 'disabled';
        }

        return [
            '#theme'            => 'opportunities_details',
            '#form_email'       => $formEmail,
            '#form'             => $form,
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $opportunityType,
            '#country'          => $country,
            '#token'            => $token,
            '#email'            => $email,
            '#mail'             => [
                'subject' => $results['_source']['title'],
                'body'    => "Hello,

Here's a partnering opportunity I thought might be of interest:
" . $request->getSchemeAndHttpHost() . Url::fromRoute('opportunities.details', ['profileId' => $profileId])->toString() . "

It's on Enterprise Europe Network's website, the world's largest business support network, led by the European Commission.
",
            ],
        ];
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
        $form['actions']['submit']['#attributes']['disabled'] = 'disabled';
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

        $this->service->verifyEmail(
            $email,
            $profileId
        );

        return ['success' => true];
    }
}
