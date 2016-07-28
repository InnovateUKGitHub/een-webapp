<?php
namespace Drupal\opportunities\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunities\Form\OpportunitiesForm;
use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zend\Http\Request;

class OpportunitiesController extends ControllerBase
{
    /**
     * @var ElasticSearchService
     */
    protected $service;

    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    public static function create(ContainerInterface $container)
    {
        return new static(
            \Drupal::service('elastic_search.connection')
        );
    }

    public function form()
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

    public function details($profileId)
    {
        $this->service
            ->setUrl('opportunities/details/' . urlencode($profileId))
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
}
