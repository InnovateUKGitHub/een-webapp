<?php

namespace Drupal\een_common\Service;

use Drupal\elastic_search\Service\ElasticSearchService;
use Symfony\Component\HttpFoundation\Request;

class ContactService
{
    /**
     * @var ElasticSearchService
     */
    private $service;

    /**
     * ContactService constructor.
     *
     * @param ElasticSearchService $service
     */
    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * @param string $email
     *
     * @return array
     */
    public function createLead($email)
    {
        $this->service
            ->setUrl('lead')
            ->setMethod(Request::METHOD_POST)
            ->setBody(['email' => $email]);

        return $this->service->sendRequest();
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convertLead($data)
    {
        $this->service
            ->setUrl('contact')
            ->setMethod(Request::METHOD_POST)
            ->setBody($data);

        return $this->service->sendRequest();
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return array|null
     */
    public function get($type, $id)
    {
        $this->service
            ->setUrl(urlencode($type) . '/' . urlencode($id))
            ->setMethod(Request::METHOD_GET);

        $results = $this->service->sendRequest();

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }

    /**
     * @param string $search
     *
     * @return array
     */
    public function getCompaniesList($search)
    {
        $this->service->setServer('https://api.companieshouse.gov.uk/');
        $this->service
            ->setUrl('search/companies')
            ->setMethod(Request::METHOD_GET)
            ->setQueryParams(['q' => $search])
            ->setBasicAuth('7orha_oflH8yLjXTboak_oUDkvhnuOhpQWJhwirD');

        return $this->service->sendRequest();
    }
}