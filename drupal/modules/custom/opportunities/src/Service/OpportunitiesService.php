<?php

namespace Drupal\opportunities\Service;

use Drupal\Core\Url;
use Drupal\service_connection\Service\HttpService;
use Drupal\opportunities\Form\OpportunitiesForm;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Component\Utility\Html;
use Elasticsearch\ClientBuilder;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;

class OpportunitiesService
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;
    const SEARCH = 'search';
    const OPPORTUNITY_TYPE = 'opportunity_type';
    const COUNTRY = 'country';

    /**
     * @var HttpService
     */
    private $service;
    private $create_elasticsearch_indices_from_drupal;
    private $cluster = null;

    /**
     * OpportunitiesController constructor.
     *
     * @param HttpService $service
     */
    public function __construct(HttpService $service)
    {
        $this->service = $service;
        $this->create_elasticsearch_indices_from_drupal = FALSE;
        //
        // This setting dictates whether all Event and Partnering opportunity nodes in Drupal will be indexed in ElasticSearch.
        // If so, these indices will be used to drive the Event and Partnering Opportunities searches within Drupal.
        // This means that content edited within Drupal will be reflected in the searches.
        // In order for this to work, the Vagrant VM must be running ElasticSearch 5.1 on Ubuntu 16.04, if you
        // have the older Ubuntu 14.04 VM with ElasticSearch 2.3.4 then set this to 'false'
        //
        if (Settings::get('create_elasticsearch_indices_from_drupal')) {
            $this->create_elasticsearch_indices_from_drupal = Settings::get('create_elasticsearch_indices_from_drupal');
        }
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

        return $this->service->execute(Request::METHOD_POST, 'opportunities', $params);
    }



    public function getCountryList()
    {
        $country_list = \Drupal\Core\Locale\CountryManager::getStandardList();
        $countries = $this->getAggregations(null);

        $countriesWithNames = [];
        foreach($countries['countries']['buckets'] as $country) {
            $key = array_search($country['key'], $country_list);
            if($key) {
                $countriesWithNames[$key] = $country['key'];
            }
        }

        asort($countriesWithNames);
        return $countriesWithNames;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function getOpportunities(Request $request, $resultsPerPage = self::DEFAULT_RESULT_PER_PAGE)
    {
        $form = \Drupal::formBuilder()->getForm(OpportunitiesForm::class);

        $page = $request->query->get(self::PAGE_NUMBER, 1);
        $resultPerPage = $request->query->get(self::RESULT_PER_PAGE, $resultsPerPage);
        $search = $request->query->get(self::SEARCH);
        $types = $request->query->get(self::OPPORTUNITY_TYPE);
        $countries = $request->query->get(self::COUNTRY);
        $exactMatch = $request->query->get('exactMatch');



        if ($countries) {
            if (in_array('europe', $countries) || in_array('anywhere', $countries)) {

                $europeanCountriesWithResults = [];
                if (in_array('europe', $countries) && !in_array('anywhere', $countries) && current($countries) == 'europe') {

                    $countries = array_keys($this->getCountryList());
                    $europeFullList = $this->getEuropeCountryCode();

                    foreach ($europeFullList as $country) {
                        if (in_array($country, $countries)) {
                            $europeanCountriesWithResults[] = $country;
                        }
                    }
                    $countries = $europeFullList;

                    array_push($countries, 'europe');
                }

                if (in_array('anywhere', $countries) && current($countries) == 'anywhere') {
                    $countries = array_keys($this->getCountryList());
                    array_push($countries, 'anywhere');
                    array_push($countries, 'europe');
                }
            }
        }

        $search = str_replace('"', '', $search);
        $results = $this->search($form, $search, $types, $countries, $page, $resultPerPage, $exactMatch);


        // Test if single match
        if (isset($results['_id'])) {
            return [
                'redirect' => true,
                'id'       => $results['_id'],
            ];
        }

        return [
            'form'             => $form,
            'results'          => $results['results'],
            'total'            => $results['total'],
            'aggregations'     => $results['aggregations'],
            'pageTotal'        => (int)ceil($results['total'] / $resultPerPage),
            'page'             => $page,
            'resultPerPage'    => $resultPerPage,
            'search'           => $search,
            'opportunity_type' => $types,
            'country'          => $countries,
        ];
    }


    public function getOpportunitiesForAlerts(Request $request, $search, $types, $countries,  $page = 1, $resultPerPage = 50)
    {
        $date = date('Y-m-d H:i:s');
        $results = $this->search($form, $search, $types, $countries, $page, $resultPerPage, 3,  date('Y-m-d', (strtotime ('-1 day', strtotime($date)))));
        return $results;
    }


    /**
     * @return array
     */
    public function getEuropeCountryCode()
    {
        return [
            'AD', 'AL', 'AT', 'BA', 'BE', 'BG', 'BY', 'CH', 'CY', 'CZ',
            'DE', 'DK', 'EE', 'ES', 'FI', 'FO', 'FR', 'GB', 'GI', 'GR',
            'HR', 'HU', 'IE', 'IM', 'IS', 'IT', 'LI', 'LT', 'LU', 'LV',
            'MC', 'MD', 'ME', 'MK', 'MT', 'NL', 'NO', 'PL', 'PT', 'RO',
            'RS', 'RU', 'SE', 'SI', 'SK', 'SM', 'UA', 'UK', 'VA',
        ];
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


    public function getAggregations($search, $countries = array()){

        if(!$search){
            $search = '';
        }

        // Get country list from the Drupal core 'locale' module
        $country_list = \Drupal\Core\Locale\CountryManager::getStandardList();

        $params = [
            'index' => ['elasticsearch_index_een_een_opportunity'],
            'body' => [
                'sort' => [
                    [ 'created' => ['order' => 'desc']]
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['range' =>
                                ['changed' => ['gte' => 1]]
                            ]
                        ],
                        'must' => [
                            ['bool' =>
                                ['should' => [
                                        ['term' =>
                                            ['field_eoi_show' => 'Yes']
                                        ],
                                        ['term' =>
                                            ['field_eoi_show' => 'True']
                                        ],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'size' => 0
        ];

        if($search) {
            $params['body']['query']['bool']['must'][] = [
                'query_string' => [
                    'query' => $search,
                    'fields' => [
                        'field_opportunity_id^3',
                        'title^2',
                        'body^1',
                    ],
                    'phrase_slop' => 50,
                    'allow_leading_wildcard' => true,
                    'analyze_wildcard' => true,
                    'default_operator' => 'AND',
                    'fuzzy_prefix_length' => 3
                ]
            ];
        }

        $filterCountries = true;
        $countriesWithNames = [];
        if (!is_array($countries) || in_array('anywhere', $countries)) {
            $filterCountries = false;
        }
        if($filterCountries) {
            foreach($countries as $country) {
                if(isset($country_list[$country])) {
                    $countriesWithNames[] = $country_list[$country]->getUntranslatedString();
                }
            }

            if (count($countriesWithNames) > 0) {
                $operator = 'OR';
                $params['body']['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'field_country_of_origin'
                        ],
                        'query' => implode('* ' . $operator . ' ', $countriesWithNames) . '*',
                    ]
                ];
            }
        }

        $params['body']['aggs'] = array(
            "countries" => array(
                "terms" => array(
                    'field' => 'field_country_of_origin',
                    'size' => 999
                )
            ),
            "types" => array(
                "terms" => array(
                    'field' => 'field_opportunity_type'
                )
            )
        );

        $elasticSearchUrl = \Drupal::config('elasticsearch_connector.cluster.een_cluster')->get('url');
        // Send the search to the ElasticSearch API
        $hosts = [
            $elasticSearchUrl
        ];

        $client = $this->getElasticSearchClient($hosts);

        $es_results = $client->search($params);

        return $es_results['aggregations'];
    }

    public function search(&$form, $search, $types, $countries, $page, $resultPerPage, $exactMatch = false, $timeStampFrom = 1)
    {

        $form['search']['#value'] = $search;
        $this->addCheckboxAttributes($form, $types, 'opportunity_type');
        $this->addCheckboxAttributes($form, $countries, 'country');

        // We are using the new ElasticSearch 5.1 indexes that have been created from Drupal,
        // and also using the Elasticsearch API directly aftr mixed results using Search API

        // Get country list from the Drupal core 'locale' module
        $country_list = \Drupal\Core\Locale\CountryManager::getStandardList();

        // Set up a dummy query to return all results to start with
        $start_pos = ($page - 1) * $resultPerPage;

        $sortby = 'created';
        if($search) {
            $sortby = '_score';
        }

        $dateFrom = 'created';
        if($timeStampFrom != 1) {
            $dateFrom = 'field_updated_date';
        }


      $date = date('Y-m-d H:i:s');

        $params = [
            'index' => ['elasticsearch_index_een_een_opportunity'],
            'body' => [
                'sort' => [
                    [ $sortby => ['order' => 'desc']]
                ],
                'query' => [
                    'bool' => [
                        'filter' => [
                            ['range' =>
                                [
                                   $dateFrom => ['gte' => $timeStampFrom]
                                ]
                            ],
                            ['range' =>
                                [
                                  'field_deadline_date' => ['gte' => date('Y-m-d')],
                                ]
                            ],
                        ],
                        'must' => [
                            ['bool' =>
                                [
                                    'should' => [
                                        ['term' =>
                                            ['field_eoi_show' => 'Yes']
                                        ],
                                        ['term' =>
                                            ['field_eoi_show' => 'True']
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ]
                ]
            ],
            'size' => $resultPerPage,
            'from' => $start_pos
        ];


        if($search && $exactMatch == 1) {
           $params['body']['query']['bool']['must'][]['bool']['must'] = [
                   'match' => [
                       'title' => [
                           "query" => $search,
                           "operator" => "and",
                           "zero_terms_query" => "all"
                       ]
                   ]

           ];
       } elseif($search){

            $params['body']['query']['bool']['must'][] = [
                'query_string' => [
                    'query' => $search,
                    'fields' => [
                        'field_opportunity_id^3',
                        'title^2',
                        'body^1',
                    ],
                    'phrase_slop' => 50,
                    'allow_leading_wildcard' => true,
                    'analyze_wildcard' => true,
                    'default_operator' => 'AND',
                    'fuzzy_prefix_length' => 3
                ]
            ];
        }



        $filterCountries = true;
        $countriesWithNames = [];
        if (!is_array($countries) || in_array('anywhere', $countries)) {
            $filterCountries = false;
        }
        if($filterCountries) {
            foreach($countries as $country) {
                if(isset($country_list[$country])) {
                    $countriesWithNames[] = $country_list[$country]->getUntranslatedString();
                }
            }

            if (count($countriesWithNames) > 0) {
                $operator = 'OR';
                $params['body']['query']['bool']['must'][] = [
                    'query_string' => [
                        'fields' => [
                            'field_country_of_origin'
                        ],
                        'query' => implode('* ' . $operator . ' ', $countriesWithNames) . '*',
                    ]
                ];
            }
        }


        //Filter on type
        if (count($types) > 0) {
            $operator = 'OR';
            $params['body']['query']['bool']['must'][] = [
                'query_string' => [
                    'fields' => [
                        'field_opportunity_type'
                    ],
                    'query'  => implode('* ' . $operator . ' ', $types) . '*',
                ]
            ];
        }

        if($timeStampFrom == 1) {
            $params['body']['highlight'] = [
                "pre_tags" => ["<span class='highlight'>"],
                "post_tags" => ["</span>"],
                "order" => "score",
                "fields" => [
                    "title" => [
                        "fragment_size" => 0,
                        "number_of_fragments" => 0,
                        "highlight_query" => [
                            "bool" => [
                                "must" => [
                                    "query_string" => [
                                        "fields" => [
                                            "title",
                                            "body",
                                        ],
                                        "query" => $search,
                                        "phrase_slop" => 50,
                                        "allow_leading_wildcard" => true,
                                        "analyze_wildcard" => true,
                                        "default_operator" => "AND",
                                        "fuzzy_prefix_length" => 3
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "body" => [
                        "fragment_size" => 240,
                        "number_of_fragments" => 2,
                        "highlight_query" => [
                            "bool" => [
                                "must" => [
                                    "query_string" => [
                                        "fields" => [
                                            "title",
                                            "body",
                                        ],
                                        "query" => $search,
                                        "phrase_slop" => 50,
                                        "allow_leading_wildcard" => true,
                                        "analyze_wildcard" => true,
                                        "default_operator" => "AND",
                                        "fuzzy_prefix_length" => 3
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $elasticSearchUrl = \Drupal::config('elasticsearch_connector.cluster.een_cluster')->get('url');
        // Send the search to the ElasticSearch API
        $hosts = [
            $elasticSearchUrl
        ];

        $client = $this->getElasticSearchClient($hosts);

        $es_results = $client->search($params);

        // Process the results
        $es_results_rows = array();
        $es_results_formatted = array();
        if (isset($es_results['hits']) && (isset($es_results['hits']['hits']))) {
            foreach ($es_results['hits']['hits'] as $thisresult) {
                if (isset($thisresult['_source'])) {
                    $thisres = $thisresult['_source'];
                    $country_code = array_search($thisres['field_country_of_origin'][0], $country_list);


                    $summary = (isset($thisresult['highlight']['summary'])
                        ? implode('', $thisresult['highlight']['summary'])
                        : (isset($thisresult['highlight']['body'])
                            ? implode('', $thisresult['highlight']['body'])
                            : $thisresult['_source']['body'][0]));

                    $title = (isset($thisresult['highlight']['title'])
                        ? implode('', $thisresult['highlight']['title'])
                        : (isset($thisresult['highlight']['title'])
                            ? implode('', $thisresult['highlight']['title'])
                            : $thisresult['_source']['title'][0]));

                    $es_results_rows[] = array(
                        'id' => $thisres['field_opportunity_id'][0],
                        'title' => $title,
                        'summary' => $summary,
                        'date' => \Drupal::service('date.formatter')->format($thisres['created'][0], 'custom', 'Y-m-d\Th:m:s'),
                        'country' => $thisres['field_country_of_origin'][0],
                        'changed' => $thisres['changed'][0],
                        'nid' => $thisres['nid'][0],
                        'country_code' => $country_code,
                        'field_submitted_date' => $thisres['field_submitted_date'][0]
                    );
                }
            }
            $es_results_formatted['total'] = $es_results['hits']['total'];
            $es_results_formatted['results'] = $es_results_rows;

            $aggregations = $this->getAggregations($search, $countriesWithNames);
            unset($aggregations['countries']);

            $es_results_formatted['aggregations'] = $aggregations;

        }

        return $es_results_formatted;
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
     * @param array $results
     *
     * @return array
     */
    private function reformatResults($results)
    {
        $response = [
            'total'        => $results['total'],
            'results'      => [],
            'aggregations' => $results['aggregations'],
        ];

        if (isset($results['results']) === false) {
            return $response;
        }

        foreach ($results['results'] as $result) {

            $title = isset($result['highlight']['title']) ? implode('', $result['highlight']['title']) : $result['_source']['title'];
            $summary = (isset($result['highlight']['summary'])
                ? implode('', $result['highlight']['summary'])
                : (isset($result['highlight']['description'])
                    ? implode('', $result['highlight']['description'])
                    : $result['_source']['summary']));

            $response['results'][] = [
                'id'           => $result['_id'],
                'title'        => str_replace('</span> <span>', ' ', $title),
                'summary'      => str_replace('</span> <span>', ' ', $summary),
                'type'         => $result['_source']['type'],
                'date'         => $result['_source']['date'],
                'country_code' => $result['_source']['country_code'],
                'country'      => $result['_source']['country'],
            ];
        }

        return $response;
    }

    /**
     * @param string $profileId
     *
     * @return array|null
     */
    public function get($profileId)
    {
        $results = $this->service->execute(Request::METHOD_GET, 'opportunities/' . urlencode($profileId));

        if (array_key_exists('error', $results)) {
            drupal_set_message($results['error'], 'error');
            $results = null;
        }

        return $results;
    }
    /**
     * @param string $email
     * @param string $token
     * @param string $profileId
     */
    public function verifyEmail($email, $token, $type = 'opportunity')
    {
        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = \Drupal::config('opportunities.settings')->get('verify_email_template_key');
        $sms_template_key = \Drupal::config('opportunities.settings')->get('verify_sms_template_key');

        if($type == 'event'){
            $email_template_key = \Drupal::config('opportunities.settings')->get('verify_email_events_template_key');
        }

        try {
            // Call the new 'Gov Notify' service to send verification email
            $response = $notifyClient->sendEmail( $email, $email_template_key, [
                'eensubject' => 'Please confirm you\'re human',
                'eencode' => $token
            ]);

            // Can also call 'Gov Notify' service to send sms messages
            // (code here for demonstration only at the moment, but is tested and working)
            //$response = $notifyClient->sendSms( '+44xxxxxxxxx', $sms_template_key, [
            //    'eencode' => $token
            //]);

        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }

    /**
     * @param string $email
     * @param string $token
     * @param string $profileId
     */
    public function sendNotificationEmail($email, $token, $profileId, $data)
    {
        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = \Drupal::config('opportunities.settings')->get('eoi_notify_client_email_template_key');

        // Build parameters
        $parameters = array();
        $parameters['title'] = isset($data['title']) ? $data['title'] : '';
        $parameters['reference_number'] = isset($data['reference_number']) ? $data['reference_number'] : '';
        $parameters['profile'] = isset($data['id']) ? $data['id'] : '';
        $parameters['description'] = isset($data['description']) ? $data['description'] : '';
        $parameters['interest'] = isset($data['interest']) ? $data['interest'] : '';
        $parameters['more'] = isset($data['more']) ? $data['more'] : '';
        $parameters['first_name'] = isset($data['firstname']) ? $data['firstname'] : '';
        $parameters['last_name'] = isset($data['lastname']) ? $data['lastname'] : '';
        $parameters['phone'] = isset($data['phone']) ? $data['phone'] : '';
        $parameters['email'] = isset($data['email']) ? $data['email'] : '';
        $parameters['company_name'] = isset($data['company_name']) ? $data['company_name'] : '';
        $parameters['website'] = isset($data['website']) ? $data['website'] : '';
        $parameters['postcode'] = isset($data['postcode']) ? $data['postcode'] : $data['postcode_registered'];

        try {
            // Call the new 'Gov Notify' service to send notification email
            $response = $notifyClient->sendEmail( $data['email'], $email_template_key, $parameters);
        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }

        // Get the service email template
        $email_template_key = \Drupal::config('opportunities.settings')->get('eoi_notify_service_email_template_key');
        $service_email = \Drupal::config('opportunities.settings')->get('service_email');

        // Add in the extra parameters
        $parameters['contact_organisation'] = isset($data['org_contact_organisation']) ? $data['org_contact_organisation'] : 'No data';
        $parameters['contact_consortium'] = isset($data['org_contact_consortium']) ? $data['org_contact_consortium'] : 'No data';
        $parameters['contact_fullname'] = isset($data['org_contact_fullname']) ? $data['org_contact_fullname'] : 'No data';
        $parameters['contact_phone'] = isset($data['org_contact_phone']) ? $data['org_contact_phone'] : 'No data';
        $parameters['contact_email'] = isset($data['org_contact_email']) ? $data['org_contact_email'] : 'No data';

        try {
            $response = $notifyClient->sendEmail($service_email, $email_template_key, $parameters);
        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }



    /**
     * @param string $email
     * @param string $token
     * @param string $profileId
     */
    public function sendEventRegistrationNotificationEmail($data, $eventDetails)
    {
        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = \Drupal::config('opportunities.settings')->get('event_registration_notify_template_key');

        $parameters = [];
        $parameters['event_title'] = isset($eventDetails['title'][0]['value']) ? $eventDetails['title'][0]['value'] : '';
        $parameters['delegateName'] = $data['delegateName'];
        $parameters['delegateEmail'] = $data['delegateEmail'];
        $parameters['companyName'] = $data['companyName'];
        $parameters['eventDate'] = $data['eventDate'];

        try {
            $response = $notifyClient->sendEmail( $data['email'], $email_template_key, $parameters);
        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }

        $service_email = \Drupal::config('opportunities.settings')->get('service_email');
        try {
            $response = $notifyClient->sendEmail($service_email, $email_template_key, $parameters);
        } catch (NotifyException $e) {
            drupal_set_message('There was a problem while sending the email, please try later.', 'error');
        }
    }



    public function sendApiFailureEmail($email, $data, $form)
    {
        // Call the new 'Gov Notify' service instead of the old call to een-service which called 'Gov Delivery'
        $api_key = \Drupal::config('opportunities.settings')->get('api_key');
        $notifyClient = new \Alphagov\Notifications\Client([
            'apiKey' => $api_key,
            'httpClient' => new \Http\Adapter\Guzzle6\Client
        ]);

        $email_template_key = "475927be-6d72-4fa5-ae2e-58b370dc7909";

        $data['account'] = isset($data['account']) ? $data['account'] : 'No data';
        $data['message'] = isset($data['message']) ? $data['message'] : 'No data';
        $data['profile'] = isset($data['profile']) ? $data['profile'] : 'No data';
        $data['description'] = isset($data['description']) ? $data['description'] : 'No data';
        $data['interest'] = isset($data['interest']) ? $data['interest'] : 'No data';
        $data['more'] = isset($data['more']) ? $data['more'] : 'No data';


        $data['contact_firstname'] = isset($form['firstname']) ? $form['firstname'] : 'No data';
        $data['contact_lastname'] = isset($form['lastname']) ? $form['lastname'] : 'No data';
        $data['contact_email'] = isset($form['email']) ? $form['email'] : 'No data';
        $data['contact_phone'] = isset($form['contact_phone']) ? $form['contact_phone'] : 'No data';

        $data['company_name'] = isset($form['company_name']) ? $form['company_name'] : 'No data';
        $data['company_website'] = isset($form['website']) ? $form['website'] : 'No data';
        $data['company_phone'] = isset($form['company_phone']) ? $form['firstname'] : 'No data';

        $data['addressone'] = isset($form['addressone']) ? $form['addressone'] : 'No data';
        $data['addresstwo'] = isset($form['addresstwo']) ? $form['addresstwo'] : 'No data';
        $data['city'] = isset($form['city']) ? $form['city'] : 'No data';
        $data['postcode'] = isset($form['postcode']) ? $form['postcode'] : 'No data';

        $data['addressone_registered'] = isset($form['addressone_registered']) ? $form['addressone_registered'] : 'No data';
        $data['addresstwo_registered'] = isset($form['addresstwo_registered']) ? $form['addresstwo_registered'] : 'No data';
        $data['city_registered'] = isset($form['city_registered']) ? $form['city_registered'] : 'No data';
        $data['postcode_registered'] = isset($form['postcode_registered']) ? $form['postcode_registered'] : 'No data';

        $data['requestednewaddress'] = isset($form['requestednewaddress']) ? $form['requestednewaddress'] : 'No data';

        try {
            // Call the new 'Gov Notify' service to send notification email
            $response = $notifyClient->sendEmail($email, $email_template_key, $data);
        } catch (NotifyException $e) {
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

    /**
     * @param array $data
     *
     * @return array
     */
    public function convertLead($data)
    {
        return $this->service->execute(Request::METHOD_POST, 'contact', $data);
    }

    public function autosuggest($search) {

        $esr = array();
        $esr['search'] = $search;
        $esr['size'] = 0;

        $results = $this->service->execute(Request::METHOD_POST, 'opportunities', $esr);


        if(isset($results['aggregations']['autocomplete']['buckets'])){
            if(count($results['aggregations']['autocomplete']['buckets'])){
                return $results['aggregations']['autocomplete']['buckets'];
            }
        }

        return null;
    }



    /* send pod alert data to salesforce */

    public function addAlert($data, $update = false)
    {
        if(in_array('anywhere', $data->country)){

            $this->submitAlert($data, $update, 'Anywhere');

        } elseif(in_array('europe', $data->country)){

            $this->submitAlert($data, $update, 'Europe');

        } else {
            //..... Pod alert for each specific country.

            if($data->country) {
                foreach ($data->country as $country) {
                    $this->submitAlert($data, $update, $country);
                }
            } else {
                $this->submitAlert($data, $update, null);
            }
        }
    }


    public function submitAlert($data, $update = false, $country = null)
    {
        if(!$update){
            $params = array();
            $params['Contact__c']               = $data->id;
            $params['Search_Term__c']           = $data->search;
            $params['Business_Offer__c']        = (in_array('BO', $data->opportunity_type)) ? 1 : 0;
            $params['Technology_Offer__c']      = (in_array('TO', $data->opportunity_type)) ? 1 : 0;
            $params['Technology_Request__c']    = (in_array('TR', $data->opportunity_type)) ? 1 : 0;
            $params['Business_Request__c']      = (in_array('BR', $data->opportunity_type)) ? 1 : 0;
            $params['R_and_D_Request__c']       = (in_array('RDR', $data->opportunity_type)) ? 1 : 0;
            if($country) {
                $params['Country__c'] = $country;
            }

        } else {
            $params = $data;
        }

        $name = 'POD_Alert__c';
        $salesforce = \Drupal::service('salesforce.client');
        if($update){
            try {

                $id = $params['Id'];
                unset($params['Id']);

                $params['Business_Offer__c']        = ($params['Business_Offer__c'] == '1') ? true : false;
                $params['Business_Request__c']      = ($params['Business_Request__c'] == '1') ? true : false;
                $params['Technology_Request__c']    = ($params['Technology_Request__c'] == '1') ? true : false;
                $params['Technology_Offer__c']      = ($params['Technology_Offer__c'] == '1') ? true : false;
                $params['R_and_D_Request__c']       = ($params['R_and_D_Request__c'] == '1') ? true : false;

                $salesforce->objectUpdate($name, $id, $params);
            }
            catch (SalesforceException $e) {

            }
        } else {
            try {
                $salesforce->objectCreate($name, $params);
            }
            catch (SalesforceException $e) {

            }
        }
        //temp
        return true;
    }


    public function updateAlert($data)
    {
        $this->addAlert($data, true);
    }


    public function unsubscribeAlert($id)
    {
        try {

            $name = 'POD_Alert__c';
            $salesforce = \Drupal::service('salesforce.client');
            $params['Unsubscribe__c'] = true;
            $salesforce->objectUpdate($name, $id, $params);

        }
        catch (SalesforceException $e) {

        }
    }


    private function getElasticSearchClient($hosts) {
        if (!$this->cluster) {
            $clusterId = \Drupal\elasticsearch_connector\Entity\Cluster::getDefaultCluster();
            $this->cluster = \Drupal\elasticsearch_connector\Entity\Cluster::load($clusterId);
        }

        if (empty($this->cluster->options['elasticsearch_aws_connector_aws_region'])) {
            $client = ClientBuilder::create()->setHosts($hosts)->build();
        } else {
            $handler = new ElasticsearchPhpHandler($this->cluster->options['elasticsearch_aws_connector_aws_region']);
            $client = ClientBuilder::create()
                      ->setHandler($handler)
                      ->setHosts($hosts)->build();
        }

        return $client;
    }

    public function createEmailAlertContent($opportunities)
    {
        $content = '';
        foreach($opportunities as $emailContent){
            $content .= '
#'.$emailContent['title'].'
'.$emailContent['id'].' | '.strtoupper($emailContent['country']).'
https://www.enterprise-europe.co.uk/opportunities/'.$emailContent['id'].'?utm_source=podalert
'.substr($emailContent['summary'], 0 , 240).'...
            ';
        }
        return $content;
    }


}
