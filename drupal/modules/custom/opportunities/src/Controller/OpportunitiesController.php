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
     * @return array
     */
    public function search()
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        return [
            '#theme'     => 'opportunities_form',
            '#form'      => $form,
            '#results'   => isset($form['results']) ? $form['results']['results'] : null,
            '#total'     => isset($form['results']) ? $form['results']['total'] : null,
            '#page'      => 1,
            '#pageTotal' => isset($form['results']) ? ceil($form['results']['total'] / 10) : null,
        ];
    }

    public function results(Request $request)
    {
        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, 10);

        $this->service
            ->setUrl('opportunities')
            ->setBody([
                'from'   => ($page - 1) * $resultPerPage,
                'size'   => $resultPerPage,
                'sort'   => [
                    ['date' => 'desc'],
                ],
                'source' => ['title', 'summary'],
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

        return [
            '#theme'         => 'opportunities_results',
            '#results'       => $results['results'],
            '#total'         => $results['total'],
            '#page'          => $page,
            '#resultPerPage' => $resultPerPage,
        ];
    }

    /**
     * @param $profileId
     *
     * @return array
     */
    public function details($profileId)
    {
        $this->service
            ->setUrl('opportunities/' . urlencode($profileId))
            ->setMethod(Request::METHOD_GET);
        $results = $this->service->sendRequest();
        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
        }

        return [
            '#theme'       => 'opportunities_details',
            '#opportunity' => $results,
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
