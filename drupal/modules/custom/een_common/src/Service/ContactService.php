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
        return $this->createLeadV2($email);
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
        $eoi = new \stdClass();
        $eoi->Nature_of_interest__c = $data['interest'];
        $eoi->Local_Client__c = $data['account'];
        $eoi->Profile__c = $this->getProfile($data['profile']);
        $eoi->Short_Description_Organisation__c = $data['description'];
        $eoi->Opportunity_Interests__c = $data['interest'];
        $eoi->Opportunity_any_other_info__c = $data['more'];
        $eoi->Source_of_EOI__c = 'EEN ENIW website';

        $params = (array)$eoi;
        $name = 'Eoi__c';
        $salesforce = \Drupal::service('salesforce.client');
        $eoiResponse = [];

        try {
            $response = $salesforce->objectCreate($name, $params);
            $eoiResponse['id'] = $response->__toString();
        }
        catch (\Exception $e) {
            $eoiResponse['error'] = $e->getMessage();
            \Drupal::logger('salesforce')->warning('%title %data',
                array(
                    '%title' => 'Account creation error',
                    '%data' => $e->getMessage()
                )
            );
        }

        return $eoiResponse;
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

        //$email_template_key = \Drupal::config('opportunities.settings')->get('verify_email_template_key');
        $email_template_key = "955bb101-bf66-48c5-a9db-52c051890c22";

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




    public function getAccount($accountId)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT ShippingStreet, ShippingPostalCode, ShippingCity,
Id, Name, Phone, Website, BillingStreet, BillingCity, BillingPostalCode, Company_Registration_Number__c, Address_Update_From_Client__c
FROM Account
WHERE Id = \'' . $accountId . '\'
';

        $result = $salesforce->apiCall('query?q=' . urlencode($query));

        try {

            if(isset($result['records'][0])){
                return $result['records'][0];
            }
        }
        catch (SalesforceException $e) {

        }
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

    public function getContactV2($email)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT c.Id, c.Email, c.Email1__c, c.Contact_Status__c, c.FirstName, c.LastName, c.Phone, c.MobilePhone,
c.Email_Newsletter__c, c.MailingStreet, c.MailingPostalCode, c.MailingCity, c.New_TCs__c, c.Blogs_New_data__c, c.Consultations_New_data__c, c.National_New_data__c, c.East_New_data__c,
c.London_New_data__c, c.Midlands_New_data__c, c.North_New_data__c, c.NI_New_data__c, c.South_East_New_data__c, c.South_West_New_data__c, c.Wales_New_data__c, c.Region__c,
a.ShippingStreet, a.ShippingPostalCode, a.ShippingCity,
a.Id, a.Name, a.Phone, a.Website, a.BillingStreet, a.BillingCity, a.BillingPostalCode, a.Company_Registration_Number__c, c.ENIW_Region__c, c.Website_user_password__c, c.CreatedDate,
c.Subject_Interest_Aeronautics__c, c.Subject_Interest_Agrofood__c, c.Subject_Interest_Automotive__c, c.Subject_Interest_BiochemTech__c, c.Subject_Interest_Creative__c, c.Subject_Interest_Environment__c,
c.Subject_Interest_Healthcare__c, Subject_Interest_ICT__c, c.Subject_Interest_IntelligentEnergy__c, c.Subject_Interest_Maritime__c, c.Subject_Interest_Materials__c, c.Subject_Interest_NanoMicro__c, c.Subject_Interest_Retail__c,
c.Subject_Interest_Construction__c, c.Subject_Interest_Textile__c, c.Subject_Interest_Tourism__c, c.Update_Type_Commercial__c, c.Update_Type_Research__c, c.Update_Type_Technology__c
FROM Contact c, c.Account a
WHERE Email1__c = \'' . $email . '\'
';
        $result = $salesforce->apiCall('query?q=' . urlencode($query));

        try {

            if(isset($result['records'][0]) && $result['records'][0]['Email1__c'] == $email){
                return $result['records'][0];
            }
        }
        catch (SalesforceException $e) {

        }
    }

    public function setContactSession($contact = null)
    {
        $array['type'] = $contact['Contact_Status__c'];

        if (isset($contact['Phone'])) {
            $array['phone'] = $contact['Phone'];
        }

        $array['step1'] = true;
        $array['firstname'] = $contact['FirstName'];
        $array['lastname'] = $contact['LastName'];
        $array['email'] = $contact['Email1__c'];
        $array['contact_email'] = $contact['Email'];
        $array['contact_phone'] = $contact['MobilePhone'];

        $array['step2'] = true;
        $array['company_name'] = $contact['Account']['Name'];

        if (isset($contact['Account']['Id'])) {
            $array['sfaccount'] = base64_encode($contact['Account']['Id']);
        }

        if (isset($contact['Account']['Company_Registration_Number__c'])) {
            $array['company_number'] = $contact['Account']['Company_Registration_Number__c'];
        }
        if (isset($contact['Account']['Website'])) {
            $array['website'] = $contact['Account']['Website'];
        }
        if (isset($contact['Account']['Phone'])) {
            $array['company_phone'] = $contact['Account']['Phone'];
        }

        $array['step3'] = true;
        if (isset($contact['MailingPostalCode'])) {
            $array['postcode'] = $contact['MailingPostalCode'];
        }
        if (isset($contact['MailingStreet'])) {
            $array['addressone'] = $contact['MailingStreet'];
        }
        if (isset($contact['MailingCity'])) {
            $array['city'] = $contact['MailingCity'];
        }
        if (isset($contact['Account']['BillingPostalCode'])) {
            $array['postcode_registered'] = $contact['Account']['BillingPostalCode'];
            $array['company_registered'] = 'yes';
        }
        if (isset($contact['Account']['BillingStreet'])) {
            $array['addressone_registered'] = $contact['Account']['BillingStreet'];
        }
        if (isset($contact['Account']['BillingCity'])) {
            $array['city_registered'] = $contact['Account']['BillingCity'];
        }

        if (isset($contact['Account']['BillingPostalCode'])) {
            $array['alternative_address'] = true;
        }

        $array['create_account'] = true;
        $array['terms'] = true;


        $query = \Drupal::entityQuery('node')
            ->condition('type', 'client_options');
        $nids = $query->execute();
        $node = node_load(end($nids));

        $subjects = [];
        foreach ($node->get('field_subjects_of_interest')->getValue() as $interest) {
            $value = explode('|', $interest['value']);
            if($contact[$value[1]]){
                $subjects[$value[1]] = $value[1];
            }

        }

        $updatesWanted = [];
        foreach ($node->get('field_types_of_updates')->getValue() as $updates) {
            $value = explode('|', $updates['value']);
            if($contact[$value[1]]) {
                $updatesWanted[$value[1]] = $value[1];
            }
        }

        $types = [];
        if($node->get('field_newsletter_otions')->getValue()){
            foreach ($node->get('field_newsletter_otions')->getValue() as $type) {
                $value = explode('|', $type['value']);
                if($contact[$value[1]]) {
                    $types[$value[1]] = $value[1];
                }
            }
        }

        $array['newsletter'] =  $types;
        $array['update_type'] =  $updatesWanted;
        $array['subjects'] =  $subjects;

        return $array;
    }


    public function getAlerts($userId = NULL)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT Id, Contact__c, Search_Term__c, Business_Offer__c, Business_Request__c, R_and_D_Request__c, Technology_Offer__c, Technology_Request__c, Country__c
FROM POD_Alert__c';

        if($userId){
            $query .= " WHERE Contact__c = '".$userId."' AND Unsubscribe__c = false";
        } else {
            $query .= ' WHERE Unsubscribe__c = false';
        }

        try {
            $result = $salesforce->apiCall('query?q=' . urlencode($query));
            return $result;
        }
        catch (\Exception $e) {

        }
    }



    public function createLeadV2($email)
    {
        $email = strtolower($email);
        $contact = $this->getContactV2($email);


        if($contact){
            return $contact;
        }

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
        catch (\Exception $e) {
            $message = $e->getMessage();
            \Drupal::logger('salesforce')->warning('%title %data',
                array(
                    '%title' => 'Contact update error',
                    '%data' => $message
                )
            );
        }

        $contact = $this->getContactV2($email);
        if($contact && $contact['Contact_Status__c'] == 'Lead' && $contact['LastName'] == "Lead"){
            $contact['LastName'] = NULL;
        }

        return $contact;
    }


    public function processUser($form)
    {
        $accountId = NULL;
        if($form['sfaccount']){
            $accountId = base64_decode($form['sfaccount']);
        }

        if(!$accountId){
            //create new account
            $accountId = $this->createAccount($form, $accountId);
        } else {
            $this->updateAccount($form, $accountId);
        }

        //Update user - will always have account id
        $contact = $this->updateOrCreateContactV2($form, $accountId);
        return $contact;
    }


    public function updatePassword($data)
    {
        $contact = new \stdClass();
        $contact->Website_user_password__c = $data['password'];

        $name = 'Contact';
        $salesforce = \Drupal::service('salesforce.client');

        try {
            $salesforce->objectUpdate($name, $data['id'], (array)$contact);
        }
        catch (\Exception $e) {
            $message = $e->getMessage();
            \Drupal::logger('salesforce')->warning('%title %data',
                array(
                    '%title' => 'Contact update error',
                    '%data' => $message
                )
            );
        }
    }


    private function updateOrCreateContactV2($data, $accountId)
    {
        $contactId = $this->getContactId($data['email']);

        $contact = new \stdClass();
        $contact->FirstName = $data['firstname'];
        $contact->LastName = $data['lastname'];
        $contact->Phone = $data['phone'];
        $contact->MobilePhone = $data['contact_phone'];
        $contact->Email1__c = $data['contact_email'];

        if (!empty($data['password'])) {
            $contact->Website_user_password__c = $data['password'];
        }

        $contact->MailingStreet = $data['addressone_registered'] . ' ' . $data['addresstwo_registered'];
        $contact->MailingPostalCode = $data['postcode_registered'];
        $contact->MailingCity = $data['city_registered'];

        if (!empty($data['region'])) {
            $allowedRegions = array('North', 'Northern Ireland', 'Wales', 'South East', 'South West', 'London', 'East', 'Midlands');
            if(in_array($data['region'], $allowedRegions)){
                $contact->ENIW_Region__c = $data['region'];
            }
        }

        $contact->AccountId = $accountId;
        $contact->Contact_Status__c = 'Client';
        $contact->New_TCs__c = 1;


        foreach($data['newsletter'] as $key => $value){
            $contact->$key = ($value == '0') ? false : true;
        }
        foreach($data['update_type'] as $key => $value){
            $contact->$key = ($value == '0') ? false : true;
        }
        foreach($data['subjects'] as $key => $value) {
            $contact->$key = ($value == '0') ? false : true;
        }

        $name = 'Contact';
        $salesforce = \Drupal::service('salesforce.client');

        if ($contactId === null) {
            $contact->Email1__c = $data['email'];

            try {
                $salesforce->objectCreate($name, (array)$contact);
            }
            catch (\Exception $e) {

                $message = $e->getMessage();
                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => 'Contact creation error',
                        '%data' => $message
                    )
                );
            }
        } else {
            try {
                $salesforce->objectUpdate($name, $contactId, (array)$contact);
            }
            catch (\Exception $e) {
                $message = $e->getMessage();
                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => 'Contact update error',
                        '%data' => $message
                    )
                );
            }
        }

        $endContact = $this->getContactV2($data['email']);
        return $endContact;
    }




    private function createAccount($data, $accountId)
    {

        $salesforce = \Drupal::service('salesforce.client');

        $account = new \stdClass();
        $account->Name = $data['company_name'];
        $account->Phone = $data['company_phone'];
        $account->Website = $data['website'];
        $account->Company_Registration_Number__c = $data['company_number'];

        $account->BillingStreet = $data['addressone_registered'] . ' ' . $data['addresstwo_registered'];
        $account->BillingPostalCode = $data['postcode_registered'];
        $account->BillingCity = $data['city_registered'];

        if($accountId === null && $account->Name != 'EEN'){
            $accountId = $this->searchMatchingAccountNames($data);
        }
        $name = 'Account';
        if ($accountId === null) {

            try {
                $response = $salesforce->objectCreate($name, (array)$account);
                $accountId =  $response->__toString();
            }
            catch (\Exception $e) {

                $message = $e->getMessage();

                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => 'Account creation error',
                        '%data' => $message
                    )
                );
                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => 'Account creation error',
                        '%data' => json_encode($account)
                    )
                );
            }
        }

        /*else {
            try {
                $salesforce->objectUpdate($name, $accountId, (array)$account);
            }
            catch (\Exception $e) {
                $message = $e->getMessage();
                \Drupal::logger('salesforce')->warning('%title %data',
                    array(
                        '%title' => 'Account update error',
                        '%data' => $message
                    )
                );
            }
        }*/


        if($accountId){
            return $accountId;
        } else {

            $id = $this->searchMatchingAccountNames(array('company_name' => 'Holding account from website'));
            if($id){
                return $id;
            }

            return '0012400001OKNpv';
        }
    }


    private function updateAccount($data, $accountId)
    {
        $name = 'Account';
        $salesforce = \Drupal::service('salesforce.client');

        $account = new \stdClass();
        $account->Address_Update_From_Client__c = $data['requestednewaddress'];
        $account->Website = $data['website'];
        $account->Phone = $data['company_phone'];

        try {
            $salesforce->objectUpdate($name, $accountId, (array)$account);
        }
        catch (\Exception $e) {
            $message = $e->getMessage();

            \Drupal::logger('salesforce')->warning('%title %data',
                array(
                    '%title' => 'Account update error',
                    '%data' => $message
                )
            );
        }
    }


    public function searchMatchingAccountNames($data)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $accountId = NULL;

        $query = '
SELECT Id, Name, Company_Registration_Number__c
FROM Account
WHERE Name LIKE \''.$data['company_name'].'\'
';
        try {
            $response = $salesforce->apiCall('query?q=' . urlencode($query));

            if($response && $response['totalSize'] > 0){

                if($response['totalSize'] == 1){
                    $accountId = $response['records'][0]['Id'];
                } else {
                    foreach ($response['records'] as $record) {
                        if($record['Name'] == $data['company_name']){
                            $accountId = $record['Id'];
                        }
                    }
                }
            }
            return $accountId;
        }
        catch (\Exception $e) {

        }
        return null;
    }


    /**
     * @param string $profileId
     *
     * @return string
     */
    private function getProfile($profileId)
    {
        $salesforce = \Drupal::service('salesforce.client');
        $query = '
SELECT Id
FROM Profile__c
WHERE Name = \'' . $profileId . '\'
';

        try {
            $result = $salesforce->apiCall('query?q=' . urlencode($query));

            if ($result['totalSize'] == 0) {
                return $this->createProfile($profileId);
            }
            return $result['records'][0]['Id'];
        }
        catch (\Exception $e) {

        }
    }

    /**
     * @param string $profileId
     *
     * @return array
     */
    private function createProfile($profileId)
    {

        $name = 'Profile__c';
        $salesforce = \Drupal::service('salesforce.client');
        $profile = new \stdClass();
        $profile->Name = $profileId;
        $profile->Profile_Type__c = 'BO';
        $profileId = NULL;

        try {
            $response = $salesforce->objectCreate($name, (array)$profile);
            $profileId = $response->__toString();
        }
        catch (\Exception $e) {

            $message = $e->getMessage();
            \Drupal::logger('salesforce')->warning('%title %data',
                array(
                    '%title' => 'Profile creation error',
                    '%data' => $message
                )
            );
        }

        return $profileId;
    }



    public function updateContactPreferences($contactId, $form_state)
    {

        $types = $form_state->getValue('newsletter');
        $subjects = $form_state->getValue('subjects');
        $updates = $form_state->getValue('update_type');


        $values = [];
        foreach($types as $key => $value){
            $values[$key] = ($value == '0') ? false : true;
        }
        foreach($subjects as $key => $value){
            $values[$key] = ($value == '0') ? false : true;
        }
        foreach($updates as $key => $value){
            $values[$key] = ($value == '0') ? false : true;
        }
        $contact = $values;

        $contact['New_TCs__c'] = ($form_state->getValue('terms') == '0') ? false : true;

        $name = 'Contact';
        $salesforce = \Drupal::service('salesforce.client');

        try {
            $salesforce->objectUpdate($name, $contactId, (array)$contact);
        }
        catch (\Exception $e) {
            $this->_logError($e->getMessage(), 'salesforce', 'Could not update client');
        }
    }


    private function _logError($message, $type, $title)
    {
        \Drupal::logger($type)->warning('%title %data',
            array(
                '%title' => $title,
                '%data' => $message
            )
        );
    }


    public function verifyUnsubscribeEmail($email, $contactId)
    {
        $token =  mt_rand(100000, 999999);
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);
        $email_template_key = '532096f8-ac0c-445b-b1a5-78d7bf9abeb4';
        $parameters = [];
        $parameters['eencode'] = $token;
        $parameters['link'] = $this->getPreferencesLink($email, $contactId);

        try {
            $response = $notifyClient->sendEmail($email, $email_template_key, $parameters);
        } catch (NotifyException $e) {

        }

        return [
            'token' => $token,
            'link'  => $this->getPreferencesLink($email, $contactId)
        ];
    }

    public function getPreferencesLink($email, $contactId)
    {
        return \Drupal::request()->getSchemeAndHttpHost().'/manage-your-preferences?email='.$email.'&t='.sha1($email.$contactId);
    }
}