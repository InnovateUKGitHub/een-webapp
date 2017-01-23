<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\opportunities\Form\MultiOpportunitiesForm;
use Drupal\opportunities\Form\OpportunitiesExploreForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesController extends ControllerBase
{
    /**
     * @var OpportunitiesService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
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
     * @return OpportunitiesController
     */
    public static function create(ContainerInterface $container)
    {
        return new self($container->get('opportunities.service'));
    }

    /**
     * This is a temporary action to test the different searches
     *
     * @param Request $request
     *
     * @return array
     */
    public function test(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(MultiOpportunitiesForm::class);

        $page = $request->query->get(OpportunitiesService::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(OpportunitiesService::RESULT_PER_PAGE, OpportunitiesService::DEFAULT_RESULT_PER_PAGE);
        $search = $request->query->get(OpportunitiesService::SEARCH);
        $types = $request->query->get(OpportunitiesService::OPPORTUNITY_TYPE);
        $country = $request->query->get(OpportunitiesService::COUNTRY);

        $results = $this->service->search($form, $search, $types, $country, $page, $resultPerPage);
        $results2 = $this->service->search($form, $search, $types, $country, $page, $resultPerPage, 2);
        $results3 = $this->service->search($form, $search, $types, $country, $page, $resultPerPage, 3);

        return [
            '#theme'            => 'opportunities_search_test',
            '#form'             => $form,
            '#search'           => $search,
            '#opportunity_type' => $types,
            '#results'          => $results['results'],
            '#results2'         => $results2['results'],
            '#results3'         => $results3['results'],
            '#total'            => $results['total'],
            '#total2'           => $results2['total'],
            '#total3'           => $results3['total'],
            '#pageTotal'        => (int)ceil($results['total'] / $resultPerPage),
            '#page'             => $page,
            '#resultPerPage'    => $resultPerPage,
            '#route'            => 'opportunities.search.test',
        ];
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $data = $this->service->getOpportunities($request);

        if (isset($data['redirect']) && $data['redirect'] === true) {
            return $this->redirect(
                'opportunities.details',
                ['profileId' => $data['id']]
            );
        }

        return [
            '#theme'            => 'opportunities_search',
            '#form'             => $data['form'],
            '#search'           => $data['search'],
            '#opportunity_type' => $data['opportunity_type'],
            '#country'          => $data['country'],
            '#results'          => $data['results'],
            '#total'            => $data['total'],
            '#pageTotal'        => $data['pageTotal'],
            '#page'             => $data['page'],
            '#resultPerPage'    => $data['resultPerPage'],
            '#route'            => 'opportunities.search',
        ];
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajax(Request $request)
    {
        $data = $this->service->getOpportunities($request);

        if (isset($data['redirect']) && $data['redirect'] === true) {
            return new JsonResponse(
                [
                    'redirect' => true,
                    'url'      => Url::fromRoute(
                        'opportunities.details',
                        ['profileId' => $data['id']]
                    ),
                ]
            );
        }

        return new JsonResponse(
            [
                'route'            => 'opportunities.search',
                'search'           => $data['search'],
                'opportunity_type' => $data['opportunity_type'],
                'country'          => $data['country'],
                'page'             => $data['page'],
                'resultPerPage'    => $data['resultPerPage'],
                'pageTotal'        => $data['pageTotal'],
                'total'            => $data['total'],
                'results'          => $data['results'],
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function count(Request $request)
    {
        $search = $request->query->get(OpportunitiesService::SEARCH);
        $types = $request->query->get(OpportunitiesService::OPPORTUNITY_TYPE);
        $countries = $request->query->get(OpportunitiesService::COUNTRY);

        if ($countries[0] == 'anywhere') {
            $countries = null;
        }
        if ($countries[0] == 'europe') {
            $countries = $this->service->getEuropeCountryCode();
        }
        $count = $this->service->count($search, $types, $countries);

        return new JsonResponse($count);
    }

    /**
     * @return array
     */
    public function exploreOpportunities(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesExploreForm::class);

        $data = $this->service->getOpportunities($request, $resultsPerPage = 5);


        return [
            '#form'  => $form,
            '#results'          => $data['results'],
            '#theme' => 'explore_opportunities',
            '#route' => 'opportunities.explore',
        ];
    }

}
