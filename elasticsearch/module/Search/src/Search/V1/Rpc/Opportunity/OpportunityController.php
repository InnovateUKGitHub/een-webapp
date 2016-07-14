<?php

namespace Search\V1\Rpc\Opportunity;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class OpportunityController
 *
 * @package Search\V1\Rpc\Opportunity
 */
class OpportunityController extends AbstractActionController
{
    /**
     * @return ViewModel
     */
    public function opportunityAction()
    {
        return new ViewModel([
            'controller' => 'opportunity',
            'success'    => true
        ]);
    }
}
