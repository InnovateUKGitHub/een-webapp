<?php

namespace Drupal\een_common\Service;

use \Phpforce\SoapClient;
use Drupal\een_common\Service\ContactService;

class SalesforceService
{

    public function getDuplicates($searchTerm)
    {
        $builder = new \Phpforce\SoapClient\ClientBuilder(
            \Drupal::config('een_salesforce.settings')->get('sfwsdlpath'),
            \Drupal::config('een_salesforce.settings')->get('sfsoapuser'),
            \Drupal::config('een_salesforce.settings')->get('sfsoappassword'),
            \Drupal::config('een_salesforce.settings')->get('sfsoaptoken')
        );

        $client = $builder->build();
        $duplicates = $client->duplicates($searchTerm);

        $results = [];

        if(count($duplicates['matches']) > 0){
            $i=0;
            foreach($duplicates['matches'][0]->matchRecords as $match){

                $result  = $client->query("SELECT Id, Name, Company_Registration_Number__c, BillingStreet, BillingPostalCode, BillingCity from Account where ID = '".$match->record->Id."'")->getQueryResult();
                $record = $result->getRecord(0);
                $results[$i]['title'] = $record->Name;
                $results[$i]['company_number'] = $record->Company_Registration_Number__c;
                $results[$i]['address']['postal_code'] = $record->BillingPostalCode;
                $results[$i]['address']['locality'] = $record->BillingCity;
                $results[$i]['address']['address_line_1'] = $record->BillingStreet;
                $results[$i]['id'] = base64_encode($record->Id);
                
                $results[$i]['type'] = 'sfduplicate';

                $i++;
            }
        } else {
            return false;
        }

        return $results;
    }

}


