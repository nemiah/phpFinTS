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

    /**
     * Gets statement for given date.
     *
     * @param string|\DateTime $date
     * @return Statement|null
     */
    public function getStatementForDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        foreach ($this->statements as $stmt) {
            if ($stmt->getDate() == $date) {
                return $stmt;
            }
        }

        return null;
    }

    /**
     * Checks if a statement with given date exists.
     *
     * @param string|\DateTime $date
     * @return bool
     */
    public function hasStatementForDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return null !== $this->getStatementForDate($date);
    }
}
