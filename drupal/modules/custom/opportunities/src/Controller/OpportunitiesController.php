<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\elastic_search\Service\ElasticSearchService;
use Drupal\opportunities\Form\OpportunitiesForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesController extends ControllerBase
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';

    /**
     * @var ElasticSearchService
     */
    private $service;

    /**
     * OpportunitiesController constructor.
     *
     * @param ElasticSearchService $service
     */
    public function __construct(ElasticSearchService $service)
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
        return new self(
            \Drupal::service('elastic_search.connection')
        );
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function search(Request $request)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        if ($request->getMethod() === Request::METHOD_POST) {
            $request->query->set(self::SEARCH, $form[self::SEARCH]['#value']);
            $request->query->set(self::OPPORTUNITY_TYPE, $form[self::OPPORTUNITY_TYPE]['#value']);
        }

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, 10);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);

        if ($search) {

            $form['search']['#value'] = $search;
            if ($types) {
                $types = array_filter($types, function($type) {
                    if ($type !== '0') {
                        return $type;
                    }

                    return false;
                });

                foreach ($types as $type) {
                    $form['opportunity_type'][$type]['#attributes']['checked'] = 'checked';
                }
            }

            $this->service
                ->setUrl('opportunities')
                ->setBody([
                    'from'             => ($page - 1) * $resultPerPage,
                    'size'             => $resultPerPage,
                    'search'           => $search,
                    'opportunity_type' => $types,
                    'sort'             => [
                        ['date' => 'desc'],
                    ],
                    'source'           => ['type', 'title', 'summary'],
                ]);

            $results = $this->service->sendRequest();

            if (array_key_exists('error', $results)) {
                if (is_array($results['error'])) {
                    foreach ($results['error'] as $key => $error) {
                        drupal_set_message($key . ' => ' . array_pop($error), 'error');
                    }
                } else {
                    drupal_set_message($results['error'], 'error');
                }
                $results = null;
            }
        }

        return [
            '#theme'            => 'opportunities_search',
            '#form'             => $form,
            '#search'           => $search,
            '#opportunity_type' => $types,
            '#results'          => isset($results) ? $results['results'] : null,
            '#total'            => isset($results) ? $results['total'] : null,
            '#page'             => $page,
            '#resultPerPage'    => $resultPerPage,
            '#route'            => 'opportunities.search',
        ];
    }

    /**
     * @param string  $profileId
     * @param Request $request
     *
     * @return array
     */
    public function details($profileId, Request $request)
    {
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);

        $this->service
            ->setUrl('opportunities/' . urlencode($profileId))
            ->setMethod(Request::METHOD_GET);
        $results = $this->service->sendRequest();
        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
        }

        return [
            '#theme'            => 'opportunities_details',
            '#opportunity'      => $results,
            '#search'           => $search,
            '#opportunity_type' => $types,
        ];
    }

    /**
     * @param $profileId
     *
     * @return JsonResponse
     */
    public function ajax($profileId)
    {
        $this->service
            ->setUrl('opportunities/' . urlencode($profileId))
            ->setMethod(Request::METHOD_GET);
        $results = $this->service->sendRequest();

        return new JsonResponse($results);
    }
}
