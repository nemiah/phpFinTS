<?php

namespace Fhp\Model\StatementOfAccount;

class StatementOfAccount
{
    /**
     * @var array
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
    public function setStatements(array $statements)
    {
        $this->statements = $statements;

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
