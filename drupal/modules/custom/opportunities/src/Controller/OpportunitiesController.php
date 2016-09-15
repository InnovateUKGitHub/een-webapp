<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunities\Form\OpportunitiesForm;
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
     * @param Request $request
     *
     * @return array
     */
    public function index(Request $request)
    {
        $data = $this->getOpportunities($request);

        return [
            '#theme'            => 'opportunities_search',
            '#form'             => $data['form'],
            '#search'           => $data['search'],
            '#opportunity_type' => $data['types'],
            '#results'          => $data['results'],
            '#results2'         => $data['results2'],
            '#results3'         => $data['results3'],
            '#total'            => $data['total'],
            '#total2'           => $data['total2'],
            '#total3'           => $data['total3'],
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

        $results = $this->service->search($form, $search, $types, $page, $resultPerPage);
        $results = $this->reformatResults($results);
        $results2 = $this->service->search($form, $search, $types, $page, $resultPerPage, 2);
        $results2 = $this->reformatResults($results2);
        $results3 = $this->service->search($form, $search, $types, $page, $resultPerPage, 3);
        $results3 = $this->reformatResults($results3);

        return [
            'form'          => $form,
            'results'       => $results['results'],
            'results2'      => $results2['results'],
            'results3'      => $results3['results'],
            'total'         => $results['total'],
            'total2'        => $results2['total'],
            'total3'        => $results3['total'],
            'pageTotal'     => (int)ceil($results['total'] / $resultPerPage),
            'page'          => $page,
            'resultPerPage' => $resultPerPage,
            'search'        => $search,
            'types'         => $types,
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
            $response['results'][] = [
                'id'           => $result['_id'],
                'title'        => $result['_source']['title'],
                'type'         => $result['_source']['type'],
                'summary'      => $result['_source']['summary'],
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
     * @return JsonResponse
     */
    public function ajax(Request $request)
    {

        $data = $this->getOpportunities($request);

        return new JsonResponse(
            [
                'route'            => 'opportunities.search',
                'search'           => $data['search'],
                'opportunity_type' => $data['types'],
                'page'             => $data['page'],
                'resultPerPage'    => $data['resultPerPage'],
                'pageTotal'        => $data['pageTotal'],
                'total'            => $data['total'],
                'results'          => $data['results'],
            ]
        );
    }
}
