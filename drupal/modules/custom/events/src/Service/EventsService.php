<?php

namespace Drupal\events\Service;

use Drupal\Core\Url;
use Drupal\events\Form\EventSearchForm;
use Drupal\service_connection\Service\HttpService;
use Symfony\Component\HttpFoundation\Request;

class EventsService
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 10;
    const SEARCH = 'search';
    const DATE_TYPE = 'date_type';
    const DATE_FROM = 'date_from';
    const DATE_TO = 'date_to';
    const COUNTRY = 'country';

    /**
     * @var HttpService
     */
    private $service;

    /**
     * EventsService constructor.
     *
     * @param HttpService $service
     */
    public function __construct(HttpService $service)
    {
        $this->service = $service;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getEvents(Request $request, $resultsPerPage = self::DEFAULT_RESULT_PER_PAGE)
    {
        $form = \Drupal::formBuilder()->getForm(EventSearchForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, $resultsPerPage);
        $search = $request->query->get(self::SEARCH);
        $dateType = $request->query->get(self::DATE_TYPE);
        $dateFrom = $request->query->get(self::DATE_FROM);
        $dateTo = $request->query->get(self::DATE_TO);
        $countries = $request->query->get(self::COUNTRY);

        if ($dateType === null) {
            $dateType = ['any'];
        }
        if ($countries === null) {
            $countries = ['anywhere'];
        }
        $results = $this->search($form, $search, $dateType, $dateFrom, $dateTo, $countries, $page, $resultPerPage);

        return [
            'form'          => $form,
            'results'       => $results['results'],
            'total'         => $results['total'],
            'pageTotal'     => (int)ceil($results['total'] / $resultPerPage),
            'page'          => $page,
            'resultPerPage' => $resultPerPage,
            'search'        => $search,
            'date_type'     => $dateType,
            'date_from'     => $dateFrom,
            'date_to'       => $dateTo,
            'country'       => $countries,
        ];
    }

    public function search(&$form, $search, $dateType, $dateFrom, $dateTo, $countries, $page, $resultPerPage)
    {
        $form['search']['#value'] = $search;
        $form['date_from']['#value'] = $dateFrom;
        $form['date_type']['#value'] = $dateTo;
        $this->addCheckboxAttributes($form, $dateType, 'date_type');
        $this->addCheckboxAttributes($form, $countries, 'country');

        $params = [
            'from'      => ($page - 1) * $resultPerPage,
            'size'      => $resultPerPage,
            'search'    => $search,
            'date_type' => $dateType,
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
            'country'   => $countries,
            'source'    => [
                'title', 'summary', 'description', 'start_date', 'end_date',
                'city', 'country', 'country_code', 'url', 'type',
            ],
        ];
        $params['sort'] = [
            'start_date' => ['order' => 'asc'],
            'end_date'   => ['order' => 'asc'],
        ];

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

        return $this->reformatResults($results);
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
            $summary = (isset($result['highlight']['summary']) ? implode('', $result['highlight']['summary'])
                : (isset($result['highlight']['description']) ? implode('', $result['highlight']['description'])
                    : (isset($result['_source']['summary']) ? $result['_source']['summary'] : $result['_source']['description'])));

            $response['results'][] = [
                'id'           => $result['_id'],
                'title'        => $title,
                'description'  => $summary,
                'type'         => $result['_source']['type'],
                'start_date'   => $result['_source']['start_date'],
                'end_date'     => $result['_source']['end_date'],
                'country_code' => $result['_source']['country_code'],
                'country'      => $result['_source']['country'],
                'city'         => $result['_source']['city'],
                'url'          => $result['_source']['url'],
            ];
        }

        return $response;
    }

    /**
     * @param array  $form
     * @param array  $fields
     * @param string $name
     */
    private function addCheckboxAttributes(&$form, $fields, $name)
    {
        if (empty($fields) === false) {
            foreach ($fields as $field) {
                $form[$name][$field]['#attributes']['checked'] = 'checked';
            }
        }
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