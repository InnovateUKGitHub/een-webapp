<?php

namespace Search\V1\Rpc\Event;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class EventController
 *
 * @package Search\V1\Rpc\Event
 */
class EventController extends AbstractActionController
{
    /** @var ElasticSearchService */
    private $service;

    public function __construct(ElasticSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        $inputFilter = $this->getEvent()->getParam('ZF\ContentValidation\InputFilter');

        return $inputFilter->getValues();
    }

    /**
     * @return ViewModel
     */
    public function eventAction()
    {
        $params = $this->getParams();

        return new ViewModel($this->service->searchOpportunity($params));
    }
}
