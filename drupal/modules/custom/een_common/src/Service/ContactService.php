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


    public function updateContact($data)
    {
        return $this->service->execute(Request::METHOD_POST, 'contact/update', $data);
    }


    public function deleteContact($form, $id){

        return $this->service->execute(Request::METHOD_POST, 'contact/remove?id='.$id, $form);
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

    public function getMatchingContacts($keywords) {
        $results = $this->service->execute(Request::METHOD_POST, 'contact/getmatching/' . $keywords);
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



    /**
     * @param string $email
     * @param string $token
     * @param string $profileId
     */
    public function passwordReset($email, $token)
    {
        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = \Drupal::config('opportunities.settings')->get('verify_email_template_key');
        // This could be a hardcoded value

        try {
            // Call the new 'Gov Notify' service to send verification email
            $response = $notifyClient->sendEmail( $email, $email_template_key, [
                'eensubject' => 'Password Reset',
                'eencode' => $token
            ]);

        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }

    public function hashPassword($password)
    {
        return crypt($password, \Drupal::config('een_salesforce.settings')->get('hash_key'));
    }


    public function getContactId($email)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT Id, Email, Email1__c
FROM Contact
WHERE Email1__c = \'' . $email . '\'
';

        $result = $salesforce->apiCall('query?q=' . urlencode($query));

        try {

            if(isset($result['records'][0]) && $result['records'][0]['Email1__c'] == $email){
                return $result['records'][0]['Id'];
            }
        }
        catch (SalesforceException $e) {

        }
    }


    public function getContactFromId($id)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT Id, Email, Email1__c
FROM Contact
WHERE Id = \'' . $id . '\'
';

        $result = $salesforce->apiCall('query?q=' . urlencode($query));

        try {

            if(isset($result['records'][0]) && $result['records'][0]['Id'] == $id){
                return $result['records'][0];
            }
        }
        catch (SalesforceException $e) {

        }
    }


    public function getAlerts($userId = NULL)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT Id, Contact__c, Search_Term__c, Business_Offer__c, Business_Request__c, R_and_D_Request__c, Technology_Offer__c, Technology_Request__c
FROM POD_Alert__c';

        if($userId){
            $query .= " WHERE Contact__c = '".$userId."' AND Unsubscribe__c = false";
        } else {
            $query .= ' WHERE (Contact__c = \'0032400001L145v\' OR Contact__c = \'0032400001JTTLt\') AND Unsubscribe__c = false';
        }

        try {
            $result = $salesforce->apiCall('query?q=' . urlencode($query));
            return $result;
        }
        catch (SalesforceException $e) {

        }
    }



    public function createLeadV2($email)
    {
        $lead = new \stdClass();
        $lead->Email1__c = $email;
        $lead->LastName = 'Lead';
        $lead->Contact_Status__c = 'Lead';

        $params = (array)$lead;
        $name = 'Contact';
        $salesforce = \Drupal::service('salesforce.client');

        try {
            $salesforce->objectCreate($name, $params);
        }
        catch (Exception $e) {

        }
    }

}