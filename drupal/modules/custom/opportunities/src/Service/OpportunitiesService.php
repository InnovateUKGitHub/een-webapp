<?php

namespace Drupal\opportunities\Service;

use Drupal\Core\Url;
use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    /**
     * @param string $search
     * @param array  $types
     * @param array  $countries
     *
     * @return array
     */
    public function count($search, $types, $countries)
    {
        $params = [
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
            'count'            => true,
        ];

        $this->service
            ->setUrl('opportunities')
            ->setMethod(Request::METHOD_POST)
            ->setBody($params);

        return $this->service->sendRequest();
    }

    /**
     * @param array  $form
     * @param string $search
     * @param array  $types
     * @param array  $countries
     * @param int    $page
     * @param int    $resultPerPage
     * @param int    $searchType
     *
     * @return array|null
     */
    public function search(&$form, $search, $types, $countries, $page, $resultPerPage, $searchType = 1)
    {
        $form['search']['#value'] = $search;
        $this->addCheckboxAttributes($form, $types, 'opportunity_type');
        $this->addCheckboxAttributes($form, $countries, 'country');

        $params = [
            'type'             => $searchType,
            'from'             => ($page - 1) * $resultPerPage,
            'size'             => $resultPerPage,
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
            'source'           => ['type', 'title', 'summary', 'date', 'country', 'country_code'],
        ];

        if (empty($search)) {
            $params['sort'] = [
                'date' => ['order' => 'desc'],
            ];
        }

        $this->service
            ->setUrl('opportunities')
            ->setMethod(Request::METHOD_POST)
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
     * @param string $profileId
     *
     * @return array|null
     */
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

    /**
     * @return array
     */
    public function getCountryList()
    {
        $this->service
            ->setUrl('countries')
            ->setMethod(Request::METHOD_GET);

        return $this->service->sendRequest();
    }

    public function verifyEmail($email, $profileId)
    {
        $params = [
            'email' => $email,
            'url'   => $_SERVER['SERVER_NAME'] . Url::fromRoute(
                'opportunities.details',
                [
                    'profileId' => $profileId,
                    'token'     => bin2hex(random_bytes(50)),
                    'email'     => $email,
                ]
            )->toString(),
        ];

        $this->service
            ->setUrl('email-verification')
            ->setMethod(Request::METHOD_POST)
            ->setBody($params);

        try {
            $this->service->sendRequest();
        } catch (NotFoundHttpException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }
}