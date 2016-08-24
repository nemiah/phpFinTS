<?php

namespace Fhp\Model\StatementOfAccount;

/**
 * Class StatementOfAccount
 * @package Fhp\Model\StatementOfAccount
 */
class StatementOfAccount
{
    /**
     * @var Statement[]
     */
    protected $statements = array();

    /**
     * Get statements
     *
     * @return Statement[]
     */
    public function getStatements()
    {
        return $this->statements;
    }

    /**
     * Set statements
     *
     * @param array $statements
     *
     * @return $this
     */
    public function setStatements(array $statements = null)
    {
        $this->statements = null == $statements ? array() : $statements;

        return $this;
    }

    /**
     * @param Statement $statement
     */
    public function addStatement(Statement $statement)
    {
        $this->statements[] = $statement;
    }
}
