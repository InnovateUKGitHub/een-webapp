<?php

namespace EEN\V1\Rpc\Opportunity;

use Zend\Mvc\Controller\AbstractActionController;
use ZF\ContentNegotiation\ViewModel;

/**
 * Class OpportunityController
 *
 * @package EEN\V1\Rpc\Opportunity
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
