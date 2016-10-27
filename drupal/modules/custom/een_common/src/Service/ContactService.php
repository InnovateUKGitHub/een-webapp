<?php

namespace Drupal\een_common\Service;

use Drupal\service_connection\Service\HttpService;
use Symfony\Component\HttpFoundation\Request;

class ContactService
{
    /**
     * @var HttpService
     */
    private $service;

    /**
     * ContactService constructor.
     *
     * @param HttpService $service
     */
    public function __construct(HttpService $service)
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
        return $this->service->execute(Request::METHOD_POST, 'lead', ['email' => $email]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function convertLead($data)
    {
        return $this->service->execute(Request::METHOD_POST, 'contact', $data);
    }

    /**
     * @param string $type
     * @param string $id
     *
     * @return array|null
     */
    public function get($type, $id)
    {
        $results = $this->service->execute(Request::METHOD_GET, urlencode($type) . '/' . urlencode($id));

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
        $this->service
            ->setServer('https://api.companieshouse.gov.uk/')
            ->setBasicAuth('7orha_oflH8yLjXTboak_oUDkvhnuOhpQWJhwirD');

        return $this->service->execute(Request::METHOD_GET, 'search/companies', ['q' => $search]);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function registerToEvent($data)
    {
        return $this->service->execute(Request::METHOD_POST, 'contact/event', $data);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    public function submitEoi($data)
    {
        $this->service
            ->setUrl('eoi')
            ->setMethod(Request::METHOD_POST)
            ->setBody($data);

        return $this->service->execute(Request::METHOD_POST, 'eoi', $data);
    }
}