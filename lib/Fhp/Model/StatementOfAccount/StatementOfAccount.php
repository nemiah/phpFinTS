<?php

namespace Fhp\Model\StatementOfAccount;

class StatementOfAccount
{
    /**
     * @var Statement[]
     */
    protected $statements = [];

    /**
     * Get statements
     *
     * @return Statement[]
     */
    public function getStatements(): array
    {
        return $this->statements;
    }

    public function addStatement(Statement $statement)
    {
        $this->statements[] = $statement;
    }

    /**
     * Gets statement for given date.
     *
     * @param string|\DateTime $date
     */
    public function getStatementForDate($date): ?Statement
    {
        if (is_string($date)) {
            try {
                $date = new \DateTime($date);
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid date: $date", 0, $e);
            }
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
     */
    public function hasStatementForDate($date): bool
    {
        return null !== $this->getStatementForDate($date);
    }
}
