<?php

namespace Search\V1\Rpc\Opportunity;

/**
 * Class OpportunityControllerFactory
 *
 * @package Search\V1\Rpc\Opportunity
 */
class OpportunityControllerFactory
{
    /**
     * @param $controllers
     *
     * @return OpportunityController
     */
    public function __invoke($controllers)
    {
        return new OpportunityController();
    }
}
