<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
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
     * @param Request $request
     *
     * @return array
     */
    public function index($profileId, Request $request)
    {
        $search = $request->query->get(self::SEARCH);
        $opportunityType = $request->query->get(self::OPPORTUNITY_TYPE);
        $country = $request->query->get(self::COUNTRY);

        $results = $this->service->get($profileId);

        $form = \Drupal::formBuilder()->getForm(ExpressionOfInterestForm::class);
        $form['#action'] = Url::fromRoute(
            'opportunities.details',
            ['profileId' => $profileId],
            ['query' => ['search' => $search, 'opportunity_type' => $opportunityType]]
        )->toString();

        return [
            '#attached'         => [
                'library' => [
                    'core/drupal.ajax',
                    'core/drupal.dialog',
                    'core/drupal.dialog.ajax',
                    'een/opportunity',
                ],
            ],
            '#theme'            => 'opportunities_details',
            '#form'             => $form,
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $opportunityType,
            '#country'          => $country,
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
}
