<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\opportunities\Form\CompaniesHouseForm;
use Drupal\opportunities\Form\MultiOpportunitiesForm;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\opportunities\Form\OpportunitiesExploreForm;
use Drupal\opportunities\Service\OpportunitiesService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesController extends ControllerBase
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';
    const COUNTRY = 'country';

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

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, self::DEFAULT_RESULT_PER_PAGE);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);
        $country = $request->query->get(self::COUNTRY);

        $results = $this->service->search($form, $search, $types, $country, $page, $resultPerPage);
        $results = $this->reformatResults($results);
        $results2 = $this->service->search($form, $search, $types, $country, $page, $resultPerPage, 2);
        $results2 = $this->reformatResults($results2);
        $results3 = $this->service->search($form, $search, $types, $country, $page, $resultPerPage, 3);
        $results3 = $this->reformatResults($results3);

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
     * @param array $results
     *
     * @return array
     */
    private function reformatResults($results)
    {
        $response = [
            'total'   => $results['total'],
            'results' => [],
        ];

        if (isset($results['results']) === false) {
            return $response;
        }

        foreach ($results['results'] as $result) {

            $title = isset($result['highlight']['title']) ? implode('', $result['highlight']['title']) : $result['_source']['title'];
            $summary = isset($result['highlight']['summary']) ? implode('', $result['highlight']['summary']) : $result['_source']['summary'];

            $response['results'][] = [
                'id'           => $result['_id'],
                'title'        => str_replace('</span> <span>', ' ', $title),
                'summary'      => str_replace('</span> <span>', ' ', $summary),
                'type'         => $result['_source']['type'],
                'date'         => $result['_source']['date'],
                'country_code' => $result['_source']['country_code'],
                'country'      => $result['_source']['country'],
            ];
        }

        return $response;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $data = $this->getOpportunities($request);

        if (isset($data['redirect']) && $data['redirect'] === true) {
            return $this->redirect(
                'opportunities.details',
                ['profileId' => $data['id']]
            );
        }

        return [
            '#attached'         => [
                'library' => [
                    'een/opportunity-list',
                ],
            ],
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
     * @return array
     */
    private function getOpportunities(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, self::DEFAULT_RESULT_PER_PAGE);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);
        $countries = $request->query->get(self::COUNTRY);

        $results = $this->service->search($form, $search, $types, $countries, $page, $resultPerPage, 3);

        // Test if single match
        if (isset($results['_id'])) {
            return [
                'redirect' => true,
                'id'       => $results['_id'],
            ];
        }
        $results = $this->reformatResults($results);

        return [
            'form'             => $form,
            'results'          => $results['results'],
            'total'            => $results['total'],
            'pageTotal'        => (int)ceil($results['total'] / $resultPerPage),
            'page'             => $page,
            'resultPerPage'    => $resultPerPage,
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
        ];
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function ajax(Request $request)
    {
        $data = $this->getOpportunities($request);

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
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);
        $countries = $request->query->get(self::COUNTRY);

        $count = $this->service->count($search, $types, $countries);

        return new JsonResponse($count);
    }

    public function temp()
    {
        $form = \Drupal::formBuilder()->getForm(CompaniesHouseForm::class);

        return [
            '#form'  => $form,
            '#theme' => 'opportunities_search_temp',
            '#route' => 'opportunities.search.temp',
        ];
    }

    public function tempajax()
    {
        $key = '7orha_oflH8yLjXTboak_oUDkvhnuOhpQWJhwirD';

        $query = ['q' => $_GET['q']];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, 'https://api.companieshouse.gov.uk/search/companies?' . http_build_query($query));
        curl_setopt($ch, CURLOPT_USERNAME, $key);
        $result = curl_exec($ch);

        return new JsonResponse(
            [
                'results' => $result,
            ]
        );
    }

    public function exploreOpportunities(Request $request)
    {
        
        $data = $this->getOpportunities($request);

        if (isset($data['redirect']) && $data['redirect'] === true) {
            return $this->redirect(
                'opportunities.details',
                ['profileId' => $data['id']]
            );
}
        
        $form = \Drupal::formBuilder()->getForm(OpportunitiesExploreForm::class);

        return [
            '#attached'         => [
                'library' => [
                    'een/opportunity-list',
                ],
            ],
            '#theme'            => 'explore_opportunities',
            '#form'             => $form,
            '#route'            => 'opportunities.explore',
        ];
    }
    
    
    
}
