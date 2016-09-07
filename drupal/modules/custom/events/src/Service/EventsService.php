<?php

namespace Drupal\events\Service;

use Drupal\elastic_search\Service\ElasticSearchService;

class EventsService
{
    /**
     * @var ElasticSearchService
     */
    private $service;

    /**
     * EventsService constructor.
     *
     * @param ElasticSearchService $service
     */
    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    public function search($search, $page, $resultPerPage)
    {
        $params = [
            'from'             => ($page - 1) * $resultPerPage,
            'size'             => $resultPerPage,
            'search'           => $search,
            'source'           => ['title', 'type', 'description', 'start_date', 'end_date', 'closing_date', 'deadline'],
        ];
        if (empty($search)) {
            $params['sort'] = [
                'date' => ['order' => 'desc'],
            ];
        }

        $this->service
            ->setUrl('events')
            ->setBody($params);

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
}