<?php

namespace Drupal\events\Service;

use Drupal\Core\Url;
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

    /**
     * @param int $page
     * @param int $resultPerPage
     *
     * @return array|null
     */
    public function search($page, $resultPerPage)
    {
        $params = [
            'from'   => ($page - 1) * $resultPerPage,
            'size'   => $resultPerPage,
            'source' => [
                'title', 'summary', 'description', 'start_date', 'end_date',
                'country', 'country_code', 'url', 'type',
            ],
        ];
        if (empty($search)) {
            $params['sort'] = [
                'start_date' => ['order' => 'asc'],
                'end_date'   => ['order' => 'asc'],
            ];
        }

        $results = $this->service->execute(Request::METHOD_POST, 'events', $params);

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

    /**
     * @param string $eventId
     *
     * @return array|null
     */
    public function get($eventId)
    {
        $results = $this->service->execute(Request::METHOD_GET, 'events/' . urlencode($eventId));

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $eventId
     */
    public function verifyEmail($email, $token, $eventId)
    {
        $params = [
            'template' => 'email-verification-event',
            'email'    => $email,
            'url'      => \Drupal::request()->getSchemeAndHttpHost() .
                Url::fromRoute(
                    'events.details',
                    [
                        'token'   => $token,
                        'eventId' => $eventId,
                    ]
                )->toString(),
        ];

        try {
            $this->service->execute(Request::METHOD_POST, 'email-verification', $params);
        } catch (\Exception $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }

    /**
     * @param string $email
     *
     * @return array
     */
    public function createLead($email)
    {
        return $this->service->execute(Request::METHOD_POST, 'lead', ['email' => $email]);
    }
}