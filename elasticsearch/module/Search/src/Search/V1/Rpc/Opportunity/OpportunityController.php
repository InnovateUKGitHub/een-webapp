<?php

namespace Search\V1\Rpc\Opportunity;

use Search\V1\ElasticSearch\Service\ElasticSearchService;
use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class OpportunityController
 *
 * @package Search\V1\Rpc\Opportunity
 */
class OpportunityController extends AbstractActionController
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
    public function opportunityAction()
    {
        $params = $this->getParams();

        return new ViewModel($this->service->searchOpportunity($params));
    }
}
