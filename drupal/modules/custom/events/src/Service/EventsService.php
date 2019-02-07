<?php

namespace Drupal\events\Service;

use Drupal\Core\Url;
use Drupal\events\Form\EventSearchForm;
use Drupal\service_connection\Service\HttpService;
use Symfony\Component\HttpFoundation\Request;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Elasticsearch\ClientBuilder;
use Aws\ElasticsearchService\ElasticsearchPhpHandler;


class EventsService
{
    const PAGE_NUMBER = 'page';
    const RESULT_PER_PAGE = 'resultPerPage';
    const DEFAULT_RESULT_PER_PAGE = 20;
    const SEARCH = 'search';
    const DATE_TYPE = 'date_type';
    const DATE_FROM = 'date_from';
    const DATE_TO = 'date_to';
    const COUNTRY = 'country';

    /**
     * @var HttpService
     */
    private $service;
    private $cluster = null;

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

        $create_elasticsearch_indices_from_drupal = FALSE;
        //
        // This setting dictates whether all Event and Partnering opportunity nodes in Drupal will be indexed in ElasticSearch.
        // If so, these indices will be used to drive the Event and Partnering Opportunities searches within Drupal.
        // This means that content edited within Drupal will be reflected in the searches.
        // In order for this to work, the Vagrant VM must be running ElasticSearch 5.1 on Ubuntu 16.04, if you
        // have the older Ubuntu 14.04 VM with ElasticSearch 2.3.4 then set this to 'false'
        //
        if (Settings::get('create_elasticsearch_indices_from_drupal')) {
            $create_elasticsearch_indices_from_drupal = Settings::get('create_elasticsearch_indices_from_drupal');
        }

        if ($create_elasticsearch_indices_from_drupal) {
            // We are using the new ElasticSearch 5.1 indexes that have been created from Drupal

            $start_pos = ($page - 1) * $resultPerPage;
            $params = [
                'index' => ['elasticsearch_index_een_een_event'],
                'body' => [
                    'sort' => [
                        [ 'field_event_date' => ['order' => 'asc']]
                    ]
                ],
                'size' => $resultPerPage,
                'from' => $start_pos
            ];

            // Add dates
            if (isset($dateType) && is_array($dateType) && (reset($dateType) == 'pick')) {
                $dt_from = NULL;
                $dt_to = NULL;
                if (!empty($dateFrom)) {
                    // Replace '/' in date with '-' so that strtotime knows it is uk format (not US)
                    $uk_date_from = str_replace('/','-',$dateFrom);
                    $dt_from = date("Y-m-d\TH:i:s", strtotime($uk_date_from));
                }
                if (!empty($dateTo)) {
                    // Replace '/' in date with '-' so that strtotime knows it is uk format (not US)
                    $uk_date_to = str_replace('/','-',$dateTo);
                    $dt_to = date("Y-m-d\TH:i:s", strtotime($uk_date_to));
                }
                if ($dt_from || $dt_to) {
                    if (!is_array($params['body']['query']['bool'])) {
                        $params['body']['query']['bool'] = array();
                    }
                    $params['body']['query']['bool']['must'] = array();
                    if ($dt_to && $dt_from) {
                        $params['body']['query']['bool']['must']['bool'] = array();
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('field_event_date' => array('gte' => $dt_from, 'lte' => $dt_to)));
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('end_value' => array('gte' => $dt_from, 'lte' => $dt_to)));
                    } else if ($dt_from) {
                        $params['body']['query']['bool']['must']['bool'] = array();
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('field_event_date' => array('gte' => $dt_from)));
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('end_value' => array('gte' => $dt_from)));
                    } else if ($dt_to) {
                        $params['body']['query']['bool']['must']['bool'] = array();
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('field_event_date' => array('lte' => $dt_to)));
                        $params['body']['query']['bool']['must']['bool']['should'][] = array('range' => array('end_value' => array('lte' => $dt_to)));
                    }
                }
            }

            // Add countries/regions
            if (isset($countries) && is_array($countries) && (count($countries) > 0)) {
                $cntry = reset($countries);
                $cntry_list = array();
                if ($cntry == 'uk') {
                    $cntry_list = $this->getUkCountries();
                } elseif ($cntry == 'europe') {
                    $cntry_list = $this->getEuropeCountries();
                }
                if (count($cntry_list) > 0) {
                    if (!is_array($params['body']['query']['bool'])) {
                        $params['body']['query']['bool'] = array();
                    }
                    $params['body']['query']['bool']['should'] = array();
                    foreach($cntry_list as $country) {
                        $params['body']['query']['bool']['should'][] = array('term' => array('field_event_country_code' => strtoupper($country)));
                    }
                    if (!isset($params['body']['query']['bool']['minimum_should_match'])) {
                        $params['body']['query']['bool']['minimum_should_match'] = 1;
                    }
                }
            }

            if($search) {
                $params['body']['query']['bool']['must'][] = [
                    'query_string' => [
                        'query' => strtolower($search),
                        'phrase_slop' => 50,
                        'allow_leading_wildcard' => true,
                        'analyze_wildcard' => true,
                        'default_operator' => 'AND',
                        'fuzzy_prefix_length' => 3
                    ]
                ];
            }

            if($search) {
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
                                                "title^2",
                                                "body^1",
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

            $client =$this->getElasticSearchClient($hosts);
            
            $es_results = $client->search($params);






            // Process the results
            $es_results_rows = array();
            $es_results_formatted = array();
            if (isset($es_results['hits']) && (isset($es_results['hits']['hits']))) {
                foreach ($es_results['hits']['hits'] as $thisresult) {
                    if (isset($thisresult['_source'])) {
                        $thisres = $thisresult['_source'];
                        // Deduce nid
                        $nid = '';
                        $thisid = $thisres['id'];
                        $start = strpos($thisid, 'node');
                        if ($start) {
                            $portion = substr($thisid, 12);
                            $nid = substr($portion, 0, strpos($portion, ':'));
                        }

                        $title = (isset($thisresult['highlight']['title'])
                            ? implode('', $thisresult['highlight']['title'])
                            : (isset($thisresult['highlight']['title'])
                                ? implode('', $thisresult['highlight']['title'])
                                : $thisresult['_source']['title'][0]));

                        $summary = (isset($thisresult['highlight']['summary'])
                            ? implode('', $thisresult['highlight']['summary'])
                            : (isset($thisresult['highlight']['body'])
                                ? implode('', $thisresult['highlight']['body'])
                                : $thisresult['_source']['body'][0]));

                        $es_results_rows[] = array(
                            'nid' => $nid,
                            'title' => $title,
                            'description' => $summary,
                            'start_date' => $thisres['field_event_date'][0],
                            'end_date' => $thisres['end_value'][0],
                            'city' => $thisres['field_event_city'][0],
                            'country' => $thisres['field_event_country'][0],
                            'country_code'  => $thisres['field_event_country_code'][0],
                            'type' => $thisres['field_event_import_source'][0],
                            'url' => $thisres['field_event_url'][0],
                            'field_featured_event' => $thisres['field_featured_event'][0]
                        );
                    }
                }
                $es_results_formatted['total'] = $es_results['hits']['total'];
                $es_results_formatted['results'] = $es_results_rows;
            }
            return $es_results_formatted;
        } else {
            // We are using the old ElasticSearch 2.3.4 indexes that have been built from external data
            $params = [
                'from' => ($page - 1) * $resultPerPage,
                'size' => $resultPerPage,
                'search' => $search,
                'date_type' => $dateType,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'country' => $countries,
                'source' => [
                    'title', 'summary', 'description', 'start_date', 'end_date',
                    'city', 'country', 'country_code', 'url', 'type',
                ],
            ];
            $params['sort'] = [
                'start_date' => ['order' => 'asc'],
                'end_date' => ['order' => 'asc'],
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

    }

    private function getEuropeCountries()
    {
        return [
            'ad', 'al', 'at', 'ba', 'be', 'bg', 'by', 'ch', 'cy', 'cz',
            'de', 'dk', 'ee', 'es', 'fi', 'fo', 'fr', 'gb', 'gi', 'gr',
            'hr', 'hu', 'ie', 'im', 'is', 'it', 'li', 'lt', 'lu', 'lv',
            'mc', 'md', 'me', 'mk', 'mt', 'nl', 'no', 'pl', 'pt', 'ro',
            'rs', 'ru', 'se', 'si', 'sk', 'sm', 'ua', 'uk', 'va', 'uk',
        ];
    }

    private function getUkCountries()
    {
        return [
            'gb', 'uk', 'ie', 'im'
        ];
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
}