<?php
namespace Drupal\opportunity_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\opportunity_search\Form\OpportunityForm;
use Drupal\opportunity_search\Service\ElasticSearchService;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OpportunityController extends ControllerBase
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
            $container->get('elastic_search_service')
        );
    }

    public function form()
    {
        $form = \Drupal::formBuilder()->getForm(OpportunityForm::class);

        return [
            '#theme'     => 'opportunity_form',
            '#form'      => $form,
            '#results'   => isset($form['results']) ? $form['results']['results'] : null,
            '#total'     => isset($form['results']) ? $form['results']['total'] : null,
            '#page'      => 1,
            '#pageTotal' => isset($form['results']) ? ceil($form['results']['total'] / 10) : null,
        ];
    }

    public function details($id)
    {
        return [
            '#theme'       => 'opportunity_details',
            '#opportunity' => $id,
            '#attributes'  => [],
        ];
    }
}