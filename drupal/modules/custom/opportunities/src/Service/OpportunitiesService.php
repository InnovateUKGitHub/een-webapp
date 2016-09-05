<?php

namespace Drupal\opportunities\Service;

use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\HttpFoundation\Request;

class OpportunitiesService
{
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

    public function search(&$form, $search, $types, $page, $resultPerPage)
    {
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
                'source'           => ['type', 'title', 'summary', 'date', 'country', 'country_code'],
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

        return $results;
    }

    public function get($profileId)
    {
        $this->service
            ->setUrl('opportunities/' . urlencode($profileId))
            ->setMethod(Request::METHOD_GET);

        $results = $this->service->sendRequest();

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }
}