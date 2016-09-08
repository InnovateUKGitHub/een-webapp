<?php

namespace Drupal\events\Service;

use Drupal\elastic_search\Service\ElasticSearchService;
use Zend\Http\Request;

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

    public function search($page, $resultPerPage)
    {
        $params = [
            'from'   => ($page - 1) * $resultPerPage,
            'size'   => $resultPerPage,
            'source' => ['title', 'description', 'start_date', 'end_date', 'country', 'country_code'],
        ];
        if (empty($search)) {
            $params['sort'] = [
                'start_date' => ['order' => 'desc'],
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

    public function get($eventId)
    {
        $this->service
            ->setUrl('events/' . urlencode($eventId))
            ->setMethod(Request::METHOD_GET);

        $results = $this->service->sendRequest();

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }
}