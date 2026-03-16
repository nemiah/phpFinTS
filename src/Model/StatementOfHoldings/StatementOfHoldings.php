<?php

namespace Fhp\Model\StatementOfHoldings;

class StatementOfHoldings
{
    /**
     * @var Holding[]
     */
    protected $holdings = [];

    /**
     * Get statements
     *
     * @return Holding[]
     */
    public function getHoldings(): array
    {
        return $this->holdings;
    }

    public function addHolding(Holding $holding)
    {
        $this->holdings[] = $holding;
    }
}
